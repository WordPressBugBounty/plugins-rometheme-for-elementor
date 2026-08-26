#!/usr/bin/env node
/**
 * Wrap bare HTML copy in PHP templates with gettext calls.
 *
 *   node tools/wrap-untranslated.js <path...> --domain=<text-domain> [--fix]
 *
 * Companion to find-untranslated.js: that one measures, this one edits.
 *
 * Text nodes become `<?php esc_html_e('…', 'domain'); ?>` and copy attributes
 * become `<?php esc_attr_e('…', 'domain'); ?>`, both preserving the surrounding
 * whitespace so the rendered markup is byte-identical.
 *
 * Anything sharing a text run with PHP output is left alone and reported.
 * `Hello, <?php echo $name ?>` would otherwise be split into the fragment
 * "Hello," -- a string no translator can place, and one that hard-codes English
 * word order. Those want `printf( esc_html__( 'Hello, %s', … ), $name )`, which
 * is a judgement call about where the placeholder belongs.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const TEXT_ATTRS = ['placeholder', 'title', 'alt', 'aria-label'];

function blank(chars, from, to) {
    for (let i = from; i < to && i < chars.length; i += 1) {
        if (chars[i] !== '\n') chars[i] = ' ';
    }
}

/**
 * Blank regions that are not translatable copy, preserving offsets.
 * Returns the masked text plus the spans that were masked, so callers can tell
 * whether a match sat next to PHP.
 */
function mask(src) {
    const chars = src.split('');

    // Regions whose contents are never translatable copy. Text may still sit
    // *after* one of these on the same run -- an icon followed by a button
    // label is the common case -- so the spans are recorded rather than used
    // to reject the whole run.
    const deadSpans = [];
    let m;

    for (const re of [
        /<\?[\s\S]*?(\?>|$)/g,
        /<script\b[\s\S]*?<\/script>/gi,
        /<style\b[\s\S]*?<\/style>/gi,
        /<svg\b[\s\S]*?<\/svg>/gi,
        /<!--[\s\S]*?-->/g,
    ]) {
        re.lastIndex = 0;
        while ((m = re.exec(src)) !== null) {
            deadSpans.push([m.index, m.index + m[0].length]);
            blank(chars, m.index, m.index + m[0].length);
        }
    }

    return { masked: chars.join(''), deadSpans };
}

function isCopy(text) {
    const t = text.trim();
    if (t.length < 2) return false;
    if (!/[A-Za-z]/.test(t)) return false;
    if (/^[\d\s.,:;%+\-/()]*$/.test(t)) return false;
    if (/^(&[a-z]+;|\s|[|·•—–-])+$/i.test(t)) return false;
    return true;
}

/** Escape for a PHP single-quoted string. */
function phpQuote(text) {
    return "'" + text.replace(/\\/g, '\\\\').replace(/'/g, "\\'") + "'";
}

function overlapsDead(deadSpans, from, to) {
    return deadSpans.some(([s, e]) => s < to && e > from);
}

function wrapFile(file, domain, report) {
    const src = fs.readFileSync(file, 'utf8');
    const { masked, deadSpans } = mask(src);

    const edits = [];

    // Text nodes: between '>' and the next '<', matched against the original so
    // an icon's closing tag still delimits the label that follows it.
    const textRe = />([^<>]+)</g;
    let m;
    while ((m = textRe.exec(src)) !== null) {
        const start = m.index + 1;
        const end = start + m[1].length;

        if (!isCopy(m[1])) continue;

        // The delimiters must be HTML tags. Scanning the raw source means `?>`
        // and `<?php` also present a '>' and a '<', so markup sitting between
        // two PHP blocks -- `?>" alt="<?php` -- would otherwise look like a text
        // node and get wrapped, corrupting the attribute it belongs to.
        if (src[m.index - 1] === '?') continue;
        if (src[end + 1] === '?') continue;

        // Only the text itself must be clear of dead regions. A run that begins
        // right after `</svg>` is fine; one containing `<?php echo $x ?>` is not,
        // because wrapping it would strand a sentence fragment.
        if (overlapsDead(deadSpans, start, end)) {
            report.skipped.push({ file, text: m[1].trim().slice(0, 70), why: 'shares a run with PHP output' });
            continue;
        }

        const raw = m[1];
        const lead = raw.match(/^\s*/)[0];
        const tail = raw.match(/\s*$/)[0];
        const body = raw.trim();

        edits.push({
            start,
            end,
            text: `${lead}<?php esc_html_e(${phpQuote(body)}, '${domain}'); ?>${tail}`,
        });
        report.wrapped += 1;
    }

    // Copy-bearing attributes.
    const attrRe = new RegExp(`\\b(${TEXT_ATTRS.join('|')})\\s*=\\s*"([^"]*)"`, 'gi');
    while ((m = attrRe.exec(masked)) !== null) {
        const valueStart = m.index + m[0].indexOf('"') + 1;
        const valueEnd = valueStart + m[2].length;
        const raw = src.slice(valueStart, valueEnd);

        if (!isCopy(m[2])) continue;

        if (overlapsDead(deadSpans, valueStart, valueEnd)) {
            report.skipped.push({ file, text: m[2].trim().slice(0, 70), why: 'attribute contains PHP' });
            continue;
        }

        edits.push({
            start: valueStart,
            end: valueEnd,
            text: `<?php esc_attr_e(${phpQuote(raw.trim())}, '${domain}'); ?>`,
        });
        report.wrapped += 1;
    }

    if (!edits.length) return null;

    edits.sort((a, b) => b.start - a.start);
    let out = src;
    for (const e of edits) {
        out = out.slice(0, e.start) + e.text + out.slice(e.end);
    }
    return out;
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
    const fix = argv.includes('--fix');
    const domainArg = argv.find((a) => a.startsWith('--domain='));
    const targets = argv.filter((a) => !a.startsWith('--'));

    if (!targets.length || !domainArg) {
        console.error('Usage: node tools/wrap-untranslated.js <path...> --domain=<text-domain> [--fix]');
        process.exit(1);
    }

    const domain = domainArg.slice('--domain='.length);
    const files = collect(path.resolve(targets[0]), []);
    for (const t of targets.slice(1)) collect(path.resolve(t), files);

    const report = { wrapped: 0, skipped: [] };
    let changed = 0;

    for (const file of files) {
        const out = wrapFile(file, domain, report);
        if (out === null) continue;
        changed += 1;
        if (fix) fs.writeFileSync(file, out, 'utf8');
    }

    console.log(
        `${report.wrapped} string(s) ${fix ? 'wrapped' : 'would be wrapped'} in ${changed} file(s) -> '${domain}'`
    );

    if (report.skipped.length) {
        console.log(`\n${report.skipped.length} left for a human (interleaved with PHP):`);
        const seen = new Set();
        for (const s of report.skipped) {
            const rel = path.relative(process.cwd(), s.file).split(path.sep).join('/');
            const key = `${rel}|${s.text}`;
            if (seen.has(key)) continue;
            seen.add(key);
            console.log(`  ${rel}`);
            console.log(`      ${s.text}`);
        }
    }
}

main();
