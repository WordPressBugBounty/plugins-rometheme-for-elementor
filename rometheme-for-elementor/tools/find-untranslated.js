#!/usr/bin/env node
/**
 * Find user-facing text in PHP templates that is not wrapped in a gettext call.
 *
 *   node tools/find-untranslated.js <path...> [--list]
 *
 * A .pot file only ever contains what `__()` and friends already mark. A view
 * full of bare HTML text produces an empty catalogue and looks, from the
 * outside, exactly like a fully translated one -- which is how a whole
 * dashboard can stay English after the site language is switched.
 *
 * Reports text nodes and the handful of attributes that carry copy
 * (placeholder, title, alt, aria-label, value on buttons). Markup, PHP blocks,
 * SVG geometry, scripts and styles are skipped.
 */

'use strict';

const fs = require('fs');
const path = require('path');

/** Attributes whose values are shown to a person. */
const TEXT_ATTRS = ['placeholder', 'title', 'alt', 'aria-label'];

/**
 * Blank out a region, preserving length and newlines so offsets stay valid.
 */
function blank(chars, from, to) {
    for (let i = from; i < to && i < chars.length; i += 1) {
        if (chars[i] !== '\n') chars[i] = ' ';
    }
}

/** Remove PHP blocks, scripts, styles, SVG bodies and HTML comments. */
function stripNonText(src) {
    const chars = src.split('');

    const regions = [
        /<\?[\s\S]*?(\?>|$)/g,
        /<script\b[\s\S]*?<\/script>/gi,
        /<style\b[\s\S]*?<\/style>/gi,
        /<svg\b[\s\S]*?<\/svg>/gi,
        /<!--[\s\S]*?-->/g,
    ];

    for (const re of regions) {
        let m;
        re.lastIndex = 0;
        while ((m = re.exec(src)) !== null) {
            blank(chars, m.index, m.index + m[0].length);
        }
    }

    return chars.join('');
}

/** Text worth translating: has letters, is not a lone number or symbol. */
function isCopy(text) {
    const t = text.trim();
    if (t.length < 2) return false;
    if (!/[A-Za-z]/.test(t)) return false;
    if (/^[\d\s.,:;%+\-/()]*$/.test(t)) return false;
    // Bare entities and stray punctuation runs.
    if (/^(&[a-z]+;|\s|[|·•—–-])+$/i.test(t)) return false;
    return true;
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

function scan(file, root) {
    const src = fs.readFileSync(file, 'utf8');
    const stripped = stripNonText(src);

    const lineStarts = [0];
    for (let i = 0; i < src.length; i += 1) {
        if (src[i] === '\n') lineStarts.push(i + 1);
    }

    const found = [];

    // Text nodes: everything between a '>' and the next '<'.
    const textRe = />([^<>]+)</g;
    let m;
    while ((m = textRe.exec(stripped)) !== null) {
        const raw = m[1];
        if (!isCopy(raw)) continue;
        found.push({
            line: lineOf(lineStarts, m.index + 1),
            kind: 'text',
            text: raw.trim().replace(/\s+/g, ' '),
        });
    }

    // Attributes that carry copy.
    const attrRe = new RegExp(`\\b(${TEXT_ATTRS.join('|')})\\s*=\\s*"([^"]*)"`, 'gi');
    while ((m = attrRe.exec(stripped)) !== null) {
        if (!isCopy(m[2])) continue;
        found.push({
            line: lineOf(lineStarts, m.index),
            kind: m[1].toLowerCase(),
            text: m[2].trim().replace(/\s+/g, ' '),
        });
    }

    found.sort((a, b) => a.line - b.line);

    return { file: path.relative(root, file).split(path.sep).join('/'), found };
}

function collect(target, out) {
    const stat = fs.statSync(target);
    if (stat.isDirectory()) {
        for (const entry of fs.readdirSync(target, { withFileTypes: true })) {
            if (entry.isDirectory() && ['vendor', 'node_modules', '.git'].includes(entry.name)) continue;
            collect(path.join(target, entry.name), out);
        }
    } else if (target.endsWith('.php')) {
        out.push(target);
    }
    return out;
}

function main() {
    const argv = process.argv.slice(2);
    const list = argv.includes('--list');
    const targets = argv.filter((a) => !a.startsWith('--'));

    if (!targets.length) {
        console.error('Usage: node tools/find-untranslated.js <path...> [--list]');
        process.exit(1);
    }

    const root = process.cwd();
    const files = [];
    for (const t of targets) collect(path.resolve(t), files);

    let total = 0;
    const perFile = [];

    for (const file of files) {
        const result = scan(file, root);
        if (!result.found.length) continue;
        total += result.found.length;
        perFile.push(result);
    }

    perFile.sort((a, b) => b.found.length - a.found.length);

    for (const entry of perFile) {
        console.log(`${String(entry.found.length).padStart(4)}  ${entry.file}`);
        if (!list) continue;
        for (const f of entry.found) {
            const label = f.kind === 'text' ? '' : `[${f.kind}] `;
            console.log(`        ${String(f.line).padStart(4)}: ${label}${f.text.slice(0, 88)}`);
        }
    }

    console.log(`\n${total} untranslated string(s) in ${perFile.length} of ${files.length} file(s)`);
}

main();
