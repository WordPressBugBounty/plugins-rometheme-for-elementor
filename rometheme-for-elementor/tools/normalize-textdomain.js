#!/usr/bin/env node
/**
 * Rewrite the text-domain argument of gettext calls to a single canonical one.
 *
 *   node tools/normalize-textdomain.js <pluginDir> <canonicalDomain> [--dry-run]
 *
 * A plugin that spells its domain four different ways ships most of its UI
 * untranslatable: only the strings matching the domain the .mo file was built
 * for ever get looked up. The rest silently fall through to English no matter
 * how complete the translation is.
 *
 * Rewrites only the domain *argument* of a recognised gettext call, located by
 * parsing the call's arguments -- never by matching the domain text anywhere it
 * happens to appear, which would corrupt URLs, option keys and CSS classes that
 * share the name.
 */

'use strict';

const fs = require('fs');
const path = require('path');
const { FUNCTIONS, readPhpString, skipTrivia, stripComments, collectPhpFiles } = require('./make-pot');

/**
 * Parse an argument list starting at the '(' in `open`, returning each
 * argument's source range so one can be replaced in place.
 */
function readArgRanges(src, open) {
    const args = [];
    let i = open + 1;
    let depth = 0;
    let start = i;
    let sawContent = false;

    while (i < src.length) {
        const before = i;
        i = skipTrivia(src, i);
        if (i !== before && !sawContent) start = i;

        const ch = src[i];
        if (ch === undefined) break;

        if (ch === '"' || ch === "'") {
            const str = readPhpString(src, i);
            if (!str) return null;
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
                if (sawContent) args.push({ start, end: i });
                return { args, end: i + 1 };
            }
            depth -= 1;
            i += 1;
            continue;
        }

        if (ch === ',' && depth === 0) {
            args.push({ start, end: i });
            i += 1;
            start = i;
            sawContent = false;
            continue;
        }

        sawContent = true;
        i += 1;
    }

    return null;
}

function processFile(file, canonical, stats) {
    const original = fs.readFileSync(file, 'utf8');
    // Positions are shared between the stripped and original text, so matches
    // found outside comments can be spliced straight into the original.
    const scan = stripComments(original);

    const names = Object.keys(FUNCTIONS).join('|');
    const re = new RegExp(`(^|[^\\w$>:])(${names})\\s*\\(`, 'g');

    const edits = [];
    let m;

    while ((m = re.exec(scan)) !== null) {
        const fn = m[2];
        const spec = FUNCTIONS[fn];
        const open = m.index + m[0].length - 1;

        const parsed = readArgRanges(scan, open);
        if (!parsed) continue;

        const domainArg = parsed.args[spec.domain];

        // The domain slot is absent entirely, e.g. `esc_html__('Save')`.
        // WordPress then looks the string up in the *default* domain, so it
        // resolves against core's catalogue or not at all. Append the domain
        // rather than skip: an omitted argument is as untranslatable as a
        // wrong one, and the insertion point is unambiguous.
        if (!domainArg) {
            if (parsed.args.length === spec.domain) {
                const insertAt = parsed.end - 1;
                edits.push({ start: insertAt, end: insertAt, text: `, '${canonical}'` });
                stats.inserted += 1;
            } else {
                stats.missing += 1;
            }
            continue;
        }

        const text = scan.slice(domainArg.start, domainArg.end).trim();
        if (!/^'[^']*'$|^"[^"$\\]*"$/.test(text)) {
            // A constant or variable, not a literal -- leave it alone.
            stats.nonLiteral += 1;
            continue;
        }

        const current = text.slice(1, -1);
        if (current === canonical) {
            stats.alreadyCorrect += 1;
            continue;
        }

        stats.byDomain[current] = (stats.byDomain[current] || 0) + 1;
        stats.rewritten += 1;
        edits.push({ start: domainArg.start, end: domainArg.end, text: `'${canonical}'` });
    }

    if (!edits.length) return null;

    edits.sort((a, b) => b.start - a.start);
    let out = original;
    for (const e of edits) {
        out = out.slice(0, e.start) + e.text + out.slice(e.end);
    }
    return out;
}

function main() {
    const args = process.argv.slice(2);
    const dryRun = args.includes('--dry-run');
    const positional = args.filter((a) => !a.startsWith('--'));

    if (positional.length < 2) {
        console.error('Usage: node tools/normalize-textdomain.js <pluginDir> <canonicalDomain> [--dry-run]');
        process.exit(1);
    }

    const pluginDir = path.resolve(positional[0]);
    const canonical = positional[1];

    const files = collectPhpFiles(pluginDir, pluginDir, []);
    const stats = { rewritten: 0, inserted: 0, alreadyCorrect: 0, missing: 0, nonLiteral: 0, byDomain: {}, filesChanged: 0 };

    for (const file of files) {
        const out = processFile(file, canonical, stats);
        if (out !== null) {
            stats.filesChanged += 1;
            if (!dryRun) fs.writeFileSync(file, out, 'utf8');
        }
    }

    console.log(`${path.basename(pluginDir)} -> '${canonical}'`);
    console.log(`  scanned          ${files.length} PHP files`);
    console.log(`  already correct  ${stats.alreadyCorrect}`);
    console.log(`  ${dryRun ? 'would rewrite  ' : 'rewritten      '}  ${stats.rewritten} in ${stats.filesChanged} file(s)`);

    const sorted = Object.entries(stats.byDomain).sort((a, b) => b[1] - a[1]);
    if (sorted.length) {
        console.log('  from:');
        for (const [domain, count] of sorted) {
            console.log(`    ${String(count).padStart(5)}  '${domain}'`);
        }
    }

    if (stats.inserted) console.log(`  domain appended  ${stats.inserted} call(s) that had none`);
    if (stats.missing) console.log(`  no domain argument: ${stats.missing} (needs a human)`);
    if (stats.nonLiteral) console.log(`  non-literal domain: ${stats.nonLiteral} (left alone)`);
}

main();
