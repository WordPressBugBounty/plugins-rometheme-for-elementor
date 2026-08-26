#!/usr/bin/env node
/**
 * Find elements whose inline offset was made logical while a transform that
 * moves them horizontally stayed physical.
 *
 *   node tools/rtl-audit-transforms.js <path...>
 *
 * The codemod only refuses to convert `left`/`right` when an unmirrored
 * translateX sits in the *same* rule. That misses the common case where the
 * resting position and the moved state are written as separate rules:
 *
 *     .slider:before                   { left: 0.45rem }
 *     .switch:checked + .slider:before { transform: translateX(19px) }
 *
 * Converting the first without mirroring the second sends the element off in
 * the wrong direction under RTL -- by twice the offset, not zero.
 *
 * Rules are matched on their final compound selector, which is the element the
 * declarations actually land on regardless of what precedes it.
 */

'use strict';

const fs = require('fs');
const path = require('path');

const SKIP_DIRS = new Set(['lib', 'fontawesome', 'bootstrap', 'node_modules', 'vendor']);
const VENDOR_FILE = /(^|[.\-_])(bootstrap|swiper|select2|animate|intlTelInput|slick|magnific|fancybox|jquery)([.\-_]|$)/i;

/**
 * The final compound selector -- `.a .b:hover > .c::after` yields `.c::after`.
 * Pseudo-classes that only describe state (:hover, :checked, :focus) are
 * dropped so a resting rule and its state rule collapse onto the same key.
 */
function finalCompound(selector) {
    const last = selector
        .split(',')[0]
        .trim()
        .split(/\s+|(?=>)|(?=\+)|(?=~)/)
        .filter(Boolean)
        .pop();

    if (!last) return null;

    return last
        .replace(/^[>+~]\s*/, '')
        .replace(/:(hover|focus|active|checked|disabled|visited|focus-within|focus-visible)\b/g, '')
        .trim() || null;
}

function hasXTranslate(text) {
    const re = /\btranslate(3d|X)?\s*\(([^)]*)\)/gi;
    let m;
    while ((m = re.exec(text)) !== null) {
        const kind = (m[1] || '').toLowerCase();
        const args = m[2].split(',').map((a) => a.trim());
        if (kind === '' && args.length === 1) continue;
        const x = args[0] || '';
        if (x !== '' && !/^-?0(\D|$)/.test(x)) return true;
    }
    return false;
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
        console.error('Usage: node tools/rtl-audit-transforms.js <path...> [--fix] [--quiet]');
        process.exit(1);
    }

    const files = [];
    for (const t of targets) collectCss(path.resolve(t), files);

    let conflicts = 0;
    let filesFixed = 0;

    for (const file of files) {
        const css = fs.readFileSync(file, 'utf8');
        const rules = [];
        const re = /([^{}]+)\{([^{}]*)\}/g;
        let m;
        while ((m = re.exec(css)) !== null) {
            rules.push({
                selector: m[1].trim().replace(/\s+/g, ' '),
                body: m[2],
                bodyStart: m.index + m[0].indexOf('{') + 1,
                bodyEnd: m.index + m[0].length - 1,
            });
        }

        // Which elements are moved horizontally by a transform, anywhere.
        const movedBy = new Map();
        for (const rule of rules) {
            if (!hasXTranslate(rule.body)) continue;
            const key = finalCompound(rule.selector);
            if (key) movedBy.set(key, rule.selector);
        }

        const edits = [];

        for (const rule of rules) {
            const offsets = rule.body.match(/inset-inline-(start|end)\s*:[^;]*/g);
            if (!offsets) continue;

            const key = finalCompound(rule.selector);
            if (!key || !movedBy.has(key)) continue;

            // Same-rule pairings are already handled by the codemod.
            if (hasXTranslate(rule.body)) continue;

            conflicts += 1;

            if (!quiet) {
                const rel = path.relative(process.cwd(), file).split(path.sep).join('/');
                console.log(`${rel}`);
                console.log(`  offset    ${rule.selector}`);
                console.log(`            ${offsets.map((o) => o.trim()).join('; ')}`);
                console.log(`  moved by  ${movedBy.get(key)}`);
                console.log('');
            }

            if (fix) {
                // Put the offset back to physical so it agrees with the
                // physical transform. The pair then behaves exactly as it does
                // today in both directions: not mirrored, but not broken -- and
                // mirroring it properly needs an [dir="rtl"] transform written
                // by hand, which is a judgement call per element.
                const fixed = rule.body
                    .replace(/inset-inline-start(\s*:)/g, 'left$1')
                    .replace(/inset-inline-end(\s*:)/g, 'right$1');
                edits.push({ start: rule.bodyStart, end: rule.bodyEnd, text: fixed });
            }
        }

        if (fix && edits.length) {
            edits.sort((a, b) => b.start - a.start);
            let out = css;
            for (const e of edits) out = out.slice(0, e.start) + e.text + out.slice(e.end);
            fs.writeFileSync(file, out, 'utf8');
            filesFixed += 1;
        }
    }

    console.log(
        `${conflicts} cross-rule conflict(s) in ${files.length} file(s)` +
        (fix ? ` — reverted to physical in ${filesFixed} file(s)` : '')
    );
}

main();
