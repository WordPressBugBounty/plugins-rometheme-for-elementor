#!/usr/bin/env node
/**
 * Mirror horizontally-oriented linear-gradients for RTL.
 *
 *   node tools/rtl-mirror-gradients.js <path...> [--fix] [--quiet]
 *
 * CSS has no logical form for a gradient angle. `linear-gradient(90deg, …)`
 * always runs left-to-right, so a fade the design meant to trail away from the
 * text ends up leading into it once the page flips. Nothing in the logical
 * property set fixes this; the only route is an explicit `[dir="rtl"]` rule.
 *
 * Mirroring is exact: an angle becomes `360 - angle`, and `to right` becomes
 * `to left`. Vertical gradients (0deg, 180deg, `to top`, `to bottom`) have no
 * horizontal component and are left alone.
 *
 * Radial and conic gradients are reported but never rewritten -- their
 * mirroring depends on position keywords that need a human eye.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const SKIP_DIRS = new Set(['lib', 'fontawesome', 'bootstrap', 'node_modules', 'vendor']);
const VENDOR_FILE = /(^|[.\-_])(bootstrap|swiper|select2|animate|intlTelInput|slick|magnific|fancybox|jquery)([.\-_]|$)/i;

const MARKER = '/* rtl-mirror-gradients: generated, do not edit by hand */';

/** Read a balanced parenthesised run starting at the '(' in `open`. */
function readBalanced(text, open) {
    let depth = 0;
    for (let i = open; i < text.length; i += 1) {
        if (text[i] === '(') depth += 1;
        else if (text[i] === ')') {
            depth -= 1;
            if (depth === 0) return i;
        }
    }
    return -1;
}

/**
 * Mirror every linear-gradient in a value. Returns null when nothing has a
 * horizontal component, so callers can skip the rule entirely.
 */
function mirrorGradients(value) {
    let out = '';
    let i = 0;
    let changed = false;

    while (i < value.length) {
        const at = value.toLowerCase().indexOf('linear-gradient(', i);
        if (at === -1) {
            out += value.slice(i);
            break;
        }

        const open = value.indexOf('(', at);
        const close = readBalanced(value, open);
        if (close === -1) {
            out += value.slice(i);
            break;
        }

        out += value.slice(i, open + 1);
        const inner = value.slice(open + 1, close);

        // The orientation, when present, is the first comma-separated part.
        const comma = inner.indexOf(',');
        const head = comma === -1 ? inner : inner.slice(0, comma);
        const rest = comma === -1 ? '' : inner.slice(comma);

        const deg = /^\s*(-?\d+(?:\.\d+)?)deg\s*$/i.exec(head);
        const side = /^\s*to\s+(.+?)\s*$/i.exec(head);

        if (deg) {
            const normalised = ((parseFloat(deg[1]) % 360) + 360) % 360;
            if (normalised % 180 === 0) {
                out += inner; // purely vertical
            } else {
                const mirrored = (360 - normalised) % 360;
                out += head.replace(/-?\d+(?:\.\d+)?deg/i, `${mirrored}deg`) + rest;
                changed = true;
            }
        } else if (side && /\b(left|right)\b/i.test(side[1])) {
            out += head.replace(/\b(left|right)\b/gi, (w) =>
                w.toLowerCase() === 'left' ? 'right' : 'left'
            ) + rest;
            changed = true;
        } else {
            out += inner; // no orientation, or vertical keyword only
        }

        out += ')';
        i = close + 1;
    }

    return changed ? out : null;
}

function collectCss(target, out) {
    const stat = fs.statSync(target);
    if (stat.isDirectory()) {
        for (const entry of fs.readdirSync(target, { withFileTypes: true })) {
            if (entry.isDirectory() && SKIP_DIRS.has(entry.name)) continue;
            collectCss(path.join(target, entry.name), out);
        }
    } else if (target.endsWith('.css') && !VENDOR_FILE.test(path.basename(target))) {
        out.push(target);
    }
    return out;
}

function main() {
    const argv = process.argv.slice(2);
    const fix = argv.includes('--fix');
    const quiet = argv.includes('--quiet');
    const targets = argv.filter((a) => !a.startsWith('--'));

    if (!targets.length) {
        console.error('Usage: node tools/rtl-mirror-gradients.js <path...> [--fix] [--quiet]');
        process.exit(1);
    }

    const files = [];
    for (const t of targets) collectCss(path.resolve(t), files);

    let mirrored = 0;
    let filesTouched = 0;
    let radial = 0;

    for (const file of files) {
        let css = fs.readFileSync(file, 'utf8');

        // Drop a previous generated block so repeated runs stay idempotent.
        const existing = css.indexOf(MARKER);
        if (existing !== -1) css = css.slice(0, existing).replace(/\s+$/, '') + '\n';

        const overrides = [];
        const re = /([^{}]+)\{([^{}]*)\}/g;
        let m;

        while ((m = re.exec(css)) !== null) {
            const selector = m[1].trim().replace(/\s+/g, ' ');
            const body = m[2];

            if (/\[dir\s*=/.test(selector)) continue;          // already direction-scoped
            if (/@(keyframes|font-face)/i.test(selector)) continue;

            if (/(radial|conic)-gradient\(/i.test(body)) radial += 1;
            if (!/linear-gradient\(/i.test(body)) continue;

            const decls = [];
            for (const decl of body.split(';')) {
                const colon = decl.indexOf(':');
                if (colon === -1) continue;
                const prop = decl.slice(0, colon).trim();
                const value = decl.slice(colon + 1);
                if (!/linear-gradient\(/i.test(value)) continue;

                const flipped = mirrorGradients(value);
                if (flipped === null) continue;

                decls.push(`  ${prop}:${flipped.replace(/\s+$/, '')};`);
                mirrored += 1;
            }

            if (decls.length) {
                overrides.push(`[dir="rtl"] ${selector} {\n${decls.join('\n')}\n}`);
            }
        }

        if (!overrides.length) {
            if (existing !== -1 && fix) fs.writeFileSync(file, css, 'utf8');
            continue;
        }

        filesTouched += 1;

        if (!quiet) {
            const rel = path.relative(process.cwd(), file).split(path.sep).join('/');
            console.log(`${fix ? 'mirrored' : 'would mirror'}  ${rel}  (${overrides.length} rule(s))`);
        }

        if (fix) {
            const block = [
                '',
                MARKER,
                '/* A gradient angle has no logical form, so RTL needs it spelled out.',
                '   Regenerate with: node tools/rtl-mirror-gradients.js <path> --fix */',
                ...overrides,
                '',
            ].join('\n');
            fs.writeFileSync(file, css + block, 'utf8');
        }
    }

    console.log(
        `\n${mirrored} gradient declaration(s) in ${filesTouched} file(s) ${fix ? 'mirrored' : 'would be mirrored'}`
    );
    if (radial) {
        console.log(`${radial} rule(s) use radial/conic gradients — review those by hand.`);
    }
}

main();
