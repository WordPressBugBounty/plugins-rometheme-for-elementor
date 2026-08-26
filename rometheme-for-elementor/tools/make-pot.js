#!/usr/bin/env node
/**
 * Generate a .pot catalogue from PHP gettext calls.
 *
 * Stand-in for `wp i18n make-pot`, which needs WP-CLI. Scans PHP sources,
 * extracts translatable strings for a given text domain and writes a POT file.
 *
 *   node tools/make-pot.js [pluginDir] [outFile] [textDomain]
 *
 * Defaults to this plugin. Strings whose domain does not match are reported as
 * warnings rather than silently dropped -- a wrong domain never reaches a
 * translator, so it needs to be loud.
 */

'use strict';

const fs = require('fs');
const path = require('path');

/** Gettext functions mapped to the position of their text-domain argument. */
const FUNCTIONS = {
    __: { domain: 1, forms: 1 },
    _e: { domain: 1, forms: 1 },
    esc_html__: { domain: 1, forms: 1 },
    esc_html_e: { domain: 1, forms: 1 },
    esc_attr__: { domain: 1, forms: 1 },
    esc_attr_e: { domain: 1, forms: 1 },
    _x: { domain: 2, forms: 1, context: 1 },
    esc_html_x: { domain: 2, forms: 1, context: 1 },
    esc_attr_x: { domain: 2, forms: 1, context: 1 },
    _n: { domain: 3, forms: 2 },
    _nx: { domain: 4, forms: 2, context: 3 },
};

const SKIP_DIRS = new Set([
    'vendor', 'node_modules', 'build', '.git', 'languages', 'tools', 'fontawesome',
]);

/**
 * Read a PHP string literal starting at `start` (which must be a quote char).
 * Returns the decoded value and the index just past the closing quote, or null
 * when the argument is not a plain literal (a variable, concatenation, etc.).
 */
function readPhpString(src, start) {
    const quote = src[start];
    if (quote !== "'" && quote !== '"') return null;

    let out = '';
    let i = start + 1;

    while (i < src.length) {
        const ch = src[i];

        if (ch === '\\') {
            const next = src[i + 1];
            if (quote === "'") {
                // Single quotes only escape \' and \\; everything else is literal.
                if (next === "'" || next === '\\') {
                    out += next;
                    i += 2;
                } else {
                    out += ch;
                    i += 1;
                }
            } else {
                const simple = { n: '\n', t: '\t', r: '\r', v: '\v', f: '\f', e: '\x1b', '\\': '\\', '"': '"', $: '$' };
                if (next in simple) {
                    out += simple[next];
                    i += 2;
                } else {
                    out += ch;
                    i += 1;
                }
            }
            continue;
        }

        if (ch === quote) {
            return { value: out, end: i + 1 };
        }

        out += ch;
        i += 1;
    }

    return null;
}

/** Skip whitespace and PHP comments. */
function skipTrivia(src, i) {
    while (i < src.length) {
        const ch = src[i];
        if (ch === ' ' || ch === '\t' || ch === '\n' || ch === '\r') {
            i += 1;
        } else if (ch === '/' && src[i + 1] === '/') {
            while (i < src.length && src[i] !== '\n') i += 1;
        } else if (ch === '#') {
            while (i < src.length && src[i] !== '\n') i += 1;
        } else if (ch === '/' && src[i + 1] === '*') {
            i += 2;
            while (i < src.length && !(src[i] === '*' && src[i + 1] === '/')) i += 1;
            i += 2;
        } else {
            break;
        }
    }
    return i;
}

/**
 * Parse the argument list of a call whose '(' sits at `open`.
 * Literal arguments yield their string value; anything else yields null, which
 * is enough to tell "not a literal" from "empty string".
 */
function readArgs(src, open) {
    const args = [];
    let i = open + 1;
    let depth = 0;
    let current = null;
    let currentIsLiteral = false;
    let sawContent = false;

    while (i < src.length) {
        i = skipTrivia(src, i);
        const ch = src[i];

        if (ch === undefined) break;

        if (depth === 0 && (ch === "'" || ch === '"') && !sawContent) {
            const str = readPhpString(src, i);
            if (!str) return null;
            current = str.value;
            currentIsLiteral = true;
            sawContent = true;
            i = str.end;
            continue;
        }

        if (ch === "'" || ch === '"') {
            const str = readPhpString(src, i);
            if (!str) return null;
            currentIsLiteral = false;
            sawContent = true;
            i = str.end;
            continue;
        }

        if (ch === '(' || ch === '[') {
            depth += 1;
            sawContent = true;
            i += 1;
            continue;
        }

        if (ch === ']') {
            depth -= 1;
            i += 1;
            continue;
        }

        if (ch === ')') {
            if (depth === 0) {
                if (sawContent) args.push(currentIsLiteral ? current : null);
                return { args, end: i + 1 };
            }
            depth -= 1;
            i += 1;
            continue;
        }

        if (ch === ',' && depth === 0) {
            args.push(currentIsLiteral ? current : null);
            current = null;
            currentIsLiteral = false;
            sawContent = false;
            i += 1;
            continue;
        }

        currentIsLiteral = false;
        sawContent = true;
        i += 1;
    }

    return null;
}

/**
 * Blank out PHP comments, preserving length and newlines so byte offsets and
 * line numbers stay valid. Without this, commented-out gettext calls land in
 * the catalogue as strings that no longer exist in the UI.
 *
 * Tracks PHP open/close tags because `//` inside inline HTML (a URL, say) is
 * not a comment, and tracks string literals because `//` inside one is not
 * either.
 */
function stripComments(src) {
    const out = src.split('');
    let i = 0;
    let inPhp = false;

    const blank = (from, to) => {
        for (let k = from; k < to && k < out.length; k += 1) {
            if (out[k] !== '\n') out[k] = ' ';
        }
    };

    while (i < src.length) {
        if (!inPhp) {
            const open = src.indexOf('<?', i);
            if (open === -1) break;
            i = src.startsWith('<?php', open) ? open + 5 : open + 2;
            inPhp = true;
            continue;
        }

        const ch = src[i];

        if (ch === '?' && src[i + 1] === '>') {
            inPhp = false;
            i += 2;
            continue;
        }

        if (ch === "'" || ch === '"') {
            const str = readPhpString(src, i);
            i = str ? str.end : i + 1;
            continue;
        }

        if (ch === '<' && src[i + 1] === '<' && src[i + 2] === '<') {
            // Heredoc/nowdoc: skip to the terminating label.
            const label = /^<<<\s*(['"]?)([A-Za-z_]\w*)\1\r?\n/.exec(src.slice(i));
            if (label) {
                const end = new RegExp(`^[ \\t]*${label[2]}\\b`, 'm').exec(src.slice(i + label[0].length));
                i = end ? i + label[0].length + end.index + end[0].length : src.length;
                continue;
            }
        }

        if (ch === '/' && src[i + 1] === '/') {
            let end = i;
            while (end < src.length && src[end] !== '\n') {
                if (src[end] === '?' && src[end + 1] === '>') break;
                end += 1;
            }
            blank(i, end);
            i = end;
            continue;
        }

        if (ch === '#') {
            // `#[Attribute]` is not a comment.
            if (src[i + 1] === '[') {
                i += 2;
                continue;
            }
            let end = i;
            while (end < src.length && src[end] !== '\n') {
                if (src[end] === '?' && src[end + 1] === '>') break;
                end += 1;
            }
            blank(i, end);
            i = end;
            continue;
        }

        if (ch === '/' && src[i + 1] === '*') {
            let end = src.indexOf('*/', i + 2);
            end = end === -1 ? src.length : end + 2;
            blank(i, end);
            i = end;
            continue;
        }

        i += 1;
    }

    return out.join('');
}

function collectPhpFiles(dir, root, out) {
    for (const entry of fs.readdirSync(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        if (entry.isDirectory()) {
            if (SKIP_DIRS.has(entry.name)) continue;
            collectPhpFiles(full, root, out);
        } else if (entry.isFile() && entry.name.endsWith('.php')) {
            out.push(full);
        }
    }
    return out;
}

function lineOf(lineStarts, index) {
    let lo = 0;
    let hi = lineStarts.length - 1;
    while (lo < hi) {
        const mid = (lo + hi + 1) >> 1;
        if (lineStarts[mid] <= index) lo = mid;
        else hi = mid - 1;
    }
    return lo + 1;
}

function extract(file, root, domain, entries, warnings) {
    const src = stripComments(fs.readFileSync(file, 'utf8'));
    const rel = path.relative(root, file).split(path.sep).join('/');

    const lineStarts = [0];
    for (let i = 0; i < src.length; i += 1) {
        if (src[i] === '\n') lineStarts.push(i + 1);
    }

    const names = Object.keys(FUNCTIONS).join('|');
    // Reject a preceding identifier char, `$`, `>` or `:` so `$obj->__()`,
    // `Foo::_e()` and `my__()` are not mistaken for gettext calls.
    const re = new RegExp(`(^|[^\\w$>:])(${names})\\s*\\(`, 'g');

    let m;
    while ((m = re.exec(src)) !== null) {
        const fn = m[2];
        const spec = FUNCTIONS[fn];
        const open = m.index + m[0].length - 1;

        const parsed = readArgs(src, open);
        if (!parsed) continue;

        const args = parsed.args;
        const singular = args[0];
        if (typeof singular !== 'string' || singular === '') continue;

        const foundDomain = args[spec.domain];
        const line = lineOf(lineStarts, open);

        if (foundDomain !== domain) {
            if (typeof foundDomain === 'string') {
                warnings.push(`${rel}:${line}  ${fn}() uses text domain '${foundDomain}'`);
            } else if (foundDomain === undefined) {
                warnings.push(`${rel}:${line}  ${fn}() is missing its text domain`);
            }
            continue;
        }

        const plural = spec.forms === 2 ? args[1] : null;
        if (spec.forms === 2 && typeof plural !== 'string') continue;

        const context = spec.context !== undefined ? args[spec.context] : null;
        const key = `${context === null || context === undefined ? '' : context}\u0004${singular}\u0004${plural || ''}`;

        let entry = entries.get(key);
        if (!entry) {
            entry = { context: typeof context === 'string' ? context : null, singular, plural: typeof plural === 'string' ? plural : null, refs: [] };
            entries.set(key, entry);
        }
        entry.refs.push(`${rel}:${line}`);
    }
}

function escapePo(value) {
    return value
        .replace(/\\/g, '\\\\')
        .replace(/"/g, '\\"')
        .replace(/\t/g, '\\t')
        .replace(/\r/g, '\\r')
        .replace(/\n/g, '\\n"\n"');
}

function poString(prefix, value) {
    return `${prefix} "${escapePo(value)}"`;
}

function buildPot(entries, meta) {
    const now = new Date().toISOString().replace('T', ' ').slice(0, 16) + '+0000';
    const lines = [
        `# Copyright (C) ${new Date().getFullYear()} ${meta.author}`,
        `# This file is distributed under the same license as the ${meta.name} plugin.`,
        'msgid ""',
        'msgstr ""',
        `"Project-Id-Version: ${meta.name} ${meta.version}\\n"`,
        `"Report-Msgid-Bugs-To: ${meta.bugs}\\n"`,
        `"POT-Creation-Date: ${now}\\n"`,
        '"MIME-Version: 1.0\\n"',
        '"Content-Type: text/plain; charset=UTF-8\\n"',
        '"Content-Transfer-Encoding: 8bit\\n"',
        '"Language-Team: LANGUAGE <LL@li.org>\\n"',
        '"Plural-Forms: nplurals=2; plural=(n != 1);\\n"',
        '"X-Generator: tools/make-pot.js\\n"',
        `"X-Domain: ${meta.domain}\\n"`,
        '',
    ];

    const sorted = [...entries.values()].sort((a, b) => {
        const fa = a.refs[0] || '';
        const fb = b.refs[0] || '';
        return fa.localeCompare(fb) || a.singular.localeCompare(b.singular);
    });

    for (const entry of sorted) {
        // Wrap references so no comment line runs away in width.
        let ref = '#:';
        for (const r of entry.refs) {
            if (ref.length + r.length + 1 > 78) {
                lines.push(ref);
                ref = '#:';
            }
            ref += ` ${r}`;
        }
        lines.push(ref);

        if (entry.context !== null) lines.push(poString('msgctxt', entry.context));
        lines.push(poString('msgid', entry.singular));

        if (entry.plural !== null) {
            lines.push(poString('msgid_plural', entry.plural));
            lines.push('msgstr[0] ""');
            lines.push('msgstr[1] ""');
        } else {
            lines.push('msgstr ""');
        }
        lines.push('');
    }

    return lines.join('\n');
}

function readHeader(pluginDir) {
    const files = fs.readdirSync(pluginDir).filter((f) => f.endsWith('.php'));
    for (const file of files) {
        const src = fs.readFileSync(path.join(pluginDir, file), 'utf8').slice(0, 4096);
        const name = /^[ \t\/*#@]*Plugin Name:\s*(.+)$/im.exec(src);
        if (!name) continue;
        const version = /^[ \t\/*#@]*Version:\s*(.+)$/im.exec(src);
        const author = /^[ \t\/*#@]*Author:\s*(.+)$/im.exec(src);
        const uri = /^[ \t\/*#@]*Author URI:\s*(.+)$/im.exec(src);
        const domain = /^[ \t\/*#@]*Text Domain:\s*(.+)$/im.exec(src);
        return {
            name: name[1].trim(),
            version: version ? version[1].trim() : '0.0.0',
            author: author ? author[1].trim() : 'Unknown',
            bugs: uri ? uri[1].trim() : '',
            domain: domain ? domain[1].trim() : null,
        };
    }
    return null;
}

function main() {
    const pluginDir = path.resolve(process.argv[2] || path.join(__dirname, '..'));
    const meta = readHeader(pluginDir);

    if (!meta) {
        console.error(`No plugin header found in ${pluginDir}`);
        process.exit(1);
    }

    const domain = process.argv[4] || meta.domain;
    if (!domain) {
        console.error('No text domain given and none in the plugin header.');
        process.exit(1);
    }
    meta.domain = domain;

    const outFile = path.resolve(
        process.argv[3] || path.join(pluginDir, 'languages', `${domain}.pot`)
    );

    const files = collectPhpFiles(pluginDir, pluginDir, []);
    const entries = new Map();
    const warnings = [];

    for (const file of files) {
        extract(file, pluginDir, domain, entries, warnings);
    }

    fs.mkdirSync(path.dirname(outFile), { recursive: true });
    fs.writeFileSync(outFile, buildPot(entries, meta), 'utf8');

    const refCount = [...entries.values()].reduce((n, e) => n + e.refs.length, 0);
    console.log(`${meta.name} (${domain})`);
    console.log(`  scanned  ${files.length} PHP files`);
    console.log(`  strings  ${entries.size} unique / ${refCount} occurrences`);
    console.log(`  written  ${path.relative(pluginDir, outFile).split(path.sep).join('/')}`);

    if (warnings.length) {
        console.log(`\n  ${warnings.length} string(s) skipped -- wrong or missing text domain:`);
        for (const w of warnings.slice(0, 25)) console.log(`    ${w}`);
        if (warnings.length > 25) console.log(`    ... and ${warnings.length - 25} more`);
    }
}

// Exported so tools/normalize-textdomain.js reuses this parser instead of
// growing a second one that can drift from it.
module.exports = { FUNCTIONS, SKIP_DIRS, readPhpString, skipTrivia, stripComments, collectPhpFiles, readHeader };

if (require.main === module) {
    main();
}
