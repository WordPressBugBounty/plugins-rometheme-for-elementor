#!/usr/bin/env node
/**
 * Rewrite physical CSS direction properties to their logical equivalents, so
 * one stylesheet serves both LTR and RTL without a generated `-rtl.css` twin.
 *
 *   node tools/rtl-codemod.js <path...> [--dry-run] [--quiet]
 *
 * Paths may be files or directories; directories are walked for .css files.
 *
 * Why a scanner and not a regex: `left` is a property in `left: 0` but a value
 * in `background-position: left top`, a keyword in `transform-origin: left`,
 * and a property *name* inside `transition: left .3s`. Only the first should
 * become `inset-inline-start`, and the transition needs the new name too. A
 * regex cannot tell those apart; a declaration-aware pass can.
 *
 * Anything ambiguous is reported for manual review rather than rewritten.
 */

'use strict';

const fs = require('fs');
const path = require('path');

/** Property renames that are always safe. */
const PROPERTY_MAP = {
    'margin-left': 'margin-inline-start',
    'margin-right': 'margin-inline-end',
    'padding-left': 'padding-inline-start',
    'padding-right': 'padding-inline-end',
    'border-left': 'border-inline-start',
    'border-right': 'border-inline-end',
    'border-left-width': 'border-inline-start-width',
    'border-right-width': 'border-inline-end-width',
    'border-left-style': 'border-inline-start-style',
    'border-right-style': 'border-inline-end-style',
    'border-left-color': 'border-inline-start-color',
    'border-right-color': 'border-inline-end-color',
    'border-top-left-radius': 'border-start-start-radius',
    'border-top-right-radius': 'border-start-end-radius',
    'border-bottom-left-radius': 'border-end-start-radius',
    'border-bottom-right-radius': 'border-end-end-radius',
    left: 'inset-inline-start',
    right: 'inset-inline-end',
    'scroll-margin-left': 'scroll-margin-inline-start',
    'scroll-margin-right': 'scroll-margin-inline-end',
    'scroll-padding-left': 'scroll-padding-inline-start',
    'scroll-padding-right': 'scroll-padding-inline-end',
};

/** Value renames, keyed by property. */
const VALUE_MAP = {
    'text-align': { left: 'start', right: 'end' },
    'float': { left: 'inline-start', right: 'inline-end' },
    'clear': { left: 'inline-start', right: 'inline-end' },
};

/**
 * Properties whose *values* name other properties, so a rename has to follow
 * through: `transition: left .3s` must become `transition: inset-inline-start`.
 */
const PROPERTY_REFERENCING = new Set(['transition', 'transition-property', 'will-change']);

/** Patterns that a rewrite cannot express; surfaced for a human instead. */
const MANUAL_REVIEW = [
    { re: /\btranslateX\s*\(\s*-?[.\d]/i, why: 'translateX does not flip; needs an [dir="rtl"] override' },
    { re: /\btranslate3d\s*\(\s*-?[.\d]/i, why: 'translate3d X offset does not flip' },
    { re: /\brotate[YZ]?\s*\(/i, why: 'rotation direction may need flipping for arrows/carets' },
    { re: /\bdirection\s*:/i, why: 'explicit direction overrides the document' },
];

function isIdentChar(ch) {
    return /[A-Za-z0-9_-]/.test(ch);
}

/**
 * Split a shorthand value on top-level whitespace, keeping `var(--a, 4px)`
 * and `calc(1px + 2px)` in one piece.
 */
function splitTopLevel(value) {
    const parts = [];
    let depth = 0;
    let current = '';
    for (const ch of value) {
        if (ch === '(') depth += 1;
        else if (ch === ')') depth -= 1;
        if (depth === 0 && /\s/.test(ch)) {
            if (current) { parts.push(current); current = ''; }
            continue;
        }
        current += ch;
    }
    if (current) parts.push(current);
    return parts;
}

/**
 * Expand a `border-radius` shorthand into the four logical corner properties.
 *
 * The shorthand is ordered by physical corner -- top-left, top-right,
 * bottom-right, bottom-left -- and nothing about it responds to direction. A
 * pill rounded down its left edge stays rounded on the left when the row it
 * belongs to flips, so the outer edge goes square and the inner edge goes
 * round. The logical corners mirror on their own.
 *
 * Returns null when the value is already direction-neutral (one value, or all
 * corners equal) or too complex to rewrite safely (elliptical `/` syntax).
 */
function expandBorderRadius(value) {
    let raw = value.trim();
    let important = '';

    const bang = /!\s*important\s*$/i.exec(raw);
    if (bang) {
        important = ' !important';
        raw = raw.slice(0, bang.index).trim();
    }

    if (raw.includes('/')) return null;       // elliptical radii: leave to a human
    if (!raw) return null;

    const parts = splitTopLevel(raw);
    if (parts.length < 2 || parts.length > 4) return null;
    if (new Set(parts).size === 1) return null;   // all corners equal

    let tl;
    let tr;
    let br;
    let bl;

    if (parts.length === 2) {
        [tl, tr] = parts;
        br = tl;
        bl = tr;
    } else if (parts.length === 3) {
        [tl, tr, br] = parts;
        bl = tr;
    } else {
        [tl, tr, br, bl] = parts;
    }

    return [
        ['border-start-start-radius', tl],
        ['border-start-end-radius', tr],
        ['border-end-end-radius', br],
        ['border-end-start-radius', bl],
    ].map(([prop, v]) => `${prop}: ${v}${important}`);
}

/**
 * An X translation the browser will not mirror for us.
 *
 * `translate(-50%, -50%)` and `translateX(-50%)` count; `translateY(...)`,
 * `translateX(0)` and a zero X in `translate(0, 8px)` do not.
 */
function hasUnmirroredXTranslate(text) {
    const re = /\btranslate(3d|X)?\s*\(([^)]*)\)/gi;
    let m;
    while ((m = re.exec(text)) !== null) {
        const kind = (m[1] || '').toLowerCase();
        const args = m[2].split(',').map((a) => a.trim());
        // translateY() has no X component at all.
        if (kind === '' && args.length === 1) continue;
        const x = args[0] || '';
        if (x !== '' && !/^-?0(\D|$)/.test(x)) return true;
    }
    return false;
}

/**
 * The final compound selector -- `.a .b:checked + .c::after` yields `.c::after`.
 * State pseudo-classes are dropped so a resting rule and its state rule collapse
 * onto the same key.
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

/**
 * Elements moved horizontally by a transform *anywhere* in the stylesheet.
 *
 * The resting position and the moved state are routinely written as separate
 * rules -- `.slider:before { left: .45rem }` next to
 * `.switch:checked + .slider:before { transform: translateX(19px) }`. Looking
 * only inside the rule being edited would convert the first and leave the
 * second physical, which under RTL sends the element off by twice the offset
 * instead of mirroring it.
 */
function elementsMovedByTransform(css) {
    const moved = new Set();
    const re = /([^{}]+)\{([^{}]*)\}/g;
    let m;
    while ((m = re.exec(css)) !== null) {
        if (!hasUnmirroredXTranslate(m[2])) continue;
        const key = finalCompound(m[1].trim().replace(/\s+/g, ' '));
        if (key) moved.add(key);
    }
    return moved;
}

/**
 * Map every '{' to its matching '}', skipping comments and strings, so a
 * declaration can be judged against the whole rule it belongs to.
 */
function matchBraces(css) {
    const pairs = new Map();
    const stack = [];
    let i = 0;
    while (i < css.length) {
        const ch = css[i];
        if (ch === '/' && css[i + 1] === '*') {
            const end = css.indexOf('*/', i + 2);
            i = end === -1 ? css.length : end + 2;
            continue;
        }
        if (ch === '"' || ch === "'") {
            const q = ch;
            i += 1;
            while (i < css.length && css[i] !== q) {
                if (css[i] === '\\') i += 1;
                i += 1;
            }
            i += 1;
            continue;
        }
        if (ch === '{') stack.push(i);
        else if (ch === '}') {
            const open = stack.pop();
            if (open !== undefined) pairs.set(open, i);
        }
        i += 1;
    }
    return pairs;
}

/**
 * Split a stylesheet into declarations, tracking comments, strings and nesting
 * so property positions are trustworthy.
 *
 * Returns edits as {start, end, text} splices applied right-to-left.
 */
function transform(css, report) {
    const edits = [];
    const braces = matchBraces(css);
    const movedElements = elementsMovedByTransform(css);
    const blockStack = [];
    let i = 0;
    const n = css.length;

    // True when the next identifier begins a declaration (i.e. a property).
    let atDeclStart = true;

    while (i < n) {
        const ch = css[i];

        // Comments
        if (ch === '/' && css[i + 1] === '*') {
            const end = css.indexOf('*/', i + 2);
            i = end === -1 ? n : end + 2;
            continue;
        }

        // Strings
        if (ch === '"' || ch === "'") {
            const quote = ch;
            i += 1;
            while (i < n && css[i] !== quote) {
                if (css[i] === '\\') i += 1;
                i += 1;
            }
            i += 1;
            continue;
        }

        if (ch === '{') {
            blockStack.push(i);
            atDeclStart = true;
            i += 1;
            continue;
        }

        if (ch === '}') {
            blockStack.pop();
            atDeclStart = true;
            i += 1;
            continue;
        }

        if (ch === ';') {
            atDeclStart = true;
            i += 1;
            continue;
        }

        if (/\s/.test(ch)) {
            i += 1;
            continue;
        }

        // At-rules and selectors are not declarations.
        if (ch === '@') {
            while (i < n && css[i] !== '{' && css[i] !== ';') i += 1;
            continue;
        }

        if (!atDeclStart || !isIdentChar(ch)) {
            atDeclStart = false;
            i += 1;
            continue;
        }

        // Read an identifier and check it is followed by ':' -- otherwise this
        // is a selector, not a property.
        const propStart = i;
        while (i < n && isIdentChar(css[i])) i += 1;
        const prop = css.slice(propStart, i);

        let j = i;
        while (j < n && /\s/.test(css[j])) j += 1;

        if (css[j] !== ':') {
            // A selector such as `.left { ... }` -- skip to the block.
            atDeclStart = false;
            continue;
        }

        // Read the value up to ';' or the closing '}', honouring nesting.
        let k = j + 1;
        let depth = 0;
        while (k < n) {
            const c = css[k];
            if (c === '/' && css[k + 1] === '*') {
                const end = css.indexOf('*/', k + 2);
                k = end === -1 ? n : end + 2;
                continue;
            }
            if (c === '"' || c === "'") {
                const quote = c;
                k += 1;
                while (k < n && css[k] !== quote) {
                    if (css[k] === '\\') k += 1;
                    k += 1;
                }
                k += 1;
                continue;
            }
            if (c === '(') depth += 1;
            else if (c === ')') depth -= 1;
            else if (depth === 0 && (c === ';' || c === '}')) break;
            k += 1;
        }

        const valueStart = j + 1;
        const value = css.slice(valueStart, k);
        const lower = prop.toLowerCase();

        report.declarations += 1;

        // Context for the guards below: the enclosing rule and its selector.
        const open = blockStack.length ? blockStack[blockStack.length - 1] : null;
        const close = open !== null ? braces.get(open) : undefined;
        const body = close !== undefined ? css.slice(open + 1, close) : '';

        let selector = '';
        let selectorKey = null;
        if (open !== null) {
            const prev = css.lastIndexOf('}', open - 1);
            const brace = css.lastIndexOf('{', open - 1);
            const from = Math.max(prev, brace) + 1;
            selector = css.slice(from, open)
                .replace(/\/\*[\s\S]*?\*\//g, '')
                .trim()
                .replace(/\s+/g, ' ');
            selectorKey = finalCompound(selector);
        }

        // A rule already scoped to one direction states its physical values on
        // purpose. Rewriting them logically would flip them a second time and
        // undo exactly the correction the block exists to apply. This guards
        // every transformation below, not just the property renames.
        if (/\[dir\s*=\s*["']?rtl["']?\]|\[dir\s*=\s*["']?ltr["']?\]|(^|\s|\.)rtl(\s|\.|$)/i.test(selector)) {
            report.skipped += 1;
            report.counts[`${lower} (kept: direction-scoped rule)`] =
                (report.counts[`${lower} (kept: direction-scoped rule)`] || 0) + 1;
            i = k;
            atDeclStart = false;
            continue;
        }

        // 1. Property rename.
        if (Object.prototype.hasOwnProperty.call(PROPERTY_MAP, lower)) {
            // `left: 50%` + `transform: translateX(-50%)` is the centring
            // idiom. Its result is direction-neutral, but the parts are not:
            // inset-inline-start flips under RTL while the transform does not,
            // so converting only half of the pair moves the element a full
            // width off centre. Leave physical offsets alone wherever an
            // unmirrored X translation shares the rule -- or a sibling rule.
            const pairedWithTranslate =
                (lower === 'left' || lower === 'right') &&
                (hasUnmirroredXTranslate(body) ||
                    (selectorKey !== null && movedElements.has(selectorKey)));

            if (pairedWithTranslate) {
                report.skipped += 1;
                report.counts[`${lower} (kept: paired with translateX)`] =
                    (report.counts[`${lower} (kept: paired with translateX)`] || 0) + 1;
            } else {
                edits.push({ start: propStart, end: propStart + prop.length, text: PROPERTY_MAP[lower] });
                report.propertyRewrites += 1;
                report.counts[lower] = (report.counts[lower] || 0) + 1;
            }
        }

        // 2. Value rename for direction keywords.
        if (Object.prototype.hasOwnProperty.call(VALUE_MAP, lower)) {
            const map = VALUE_MAP[lower];
            const replaced = value.replace(/(^|[\s,])(left|right)(?=$|[\s,;!])/gi, (m, pre, word) => {
                const to = map[word.toLowerCase()];
                if (!to) return m;
                report.valueRewrites += 1;
                report.counts[`${lower}: ${word.toLowerCase()}`] =
                    (report.counts[`${lower}: ${word.toLowerCase()}`] || 0) + 1;
                return pre + to;
            });
            if (replaced !== value) {
                edits.push({ start: valueStart, end: k, text: replaced });
            }
        }

        // 3. Values that name properties must track the rename.
        if (PROPERTY_REFERENCING.has(lower)) {
            const replaced = value.replace(/(^|[\s,])(left|right)(?=$|[\s,])/gi, (m, pre, word) => {
                report.valueRewrites += 1;
                return pre + PROPERTY_MAP[word.toLowerCase()];
            });
            if (replaced !== value) {
                edits.push({ start: valueStart, end: k, text: replaced });
            }
        }

        // 4. Flag what cannot be expressed logically.
        for (const rule of MANUAL_REVIEW) {
            if (rule.re.test(value)) {
                report.manual.push({ prop: lower, value: value.trim().slice(0, 60), why: rule.why });
            }
        }

        // A multi-value border-radius is ordered by physical corner. There is
        // no logical shorthand, but the four logical corner properties cover it
        // exactly, so expand rather than flag.
        if (lower === 'border-radius') {
            const expanded = expandBorderRadius(value);

            if (expanded) {
                // Reuse the indentation of the line this declaration sits on.
                const lineStart = css.lastIndexOf('\n', propStart) + 1;
                const indentMatch = /^[ \t]*/.exec(css.slice(lineStart, propStart));
                const indent = indentMatch ? indentMatch[0] : '';
                const joiner = indent ? `;\n${indent}` : '; ';

                edits.push({
                    start: propStart,
                    end: k,
                    text: expanded.join(joiner),
                });
                report.propertyRewrites += 1;
                report.counts['border-radius (expanded to logical corners)'] =
                    (report.counts['border-radius (expanded to logical corners)'] || 0) + 1;
            } else if (value.includes('/')) {
                report.manual.push({
                    prop: lower,
                    value: value.trim().slice(0, 60),
                    why: 'elliptical border-radius; mirror the two axes by hand',
                });
            }
        }

        i = k;
        atDeclStart = false;
    }

    if (!edits.length) return null;

    edits.sort((a, b) => b.start - a.start);
    let out = css;
    for (const edit of edits) {
        out = out.slice(0, edit.start) + edit.text + out.slice(edit.end);
    }
    return out;
}

/** Directories that only ever hold third-party CSS. */
const SKIP_DIRS = new Set(['lib', 'fontawesome', 'bootstrap', 'node_modules', 'vendor']);

/**
 * Third-party stylesheets. Rewriting these is wrong on two counts: the upstream
 * project owns them, and the edits vanish on the next version bump.
 */
const VENDOR_FILE = /(^|[.\-_])(bootstrap|swiper|select2|animate|intlTelInput|slick|magnific|fancybox|jquery)([.\-_]|$)/i;

/**
 * Stylesheets that exist to correct direction. Their physical declarations are
 * the whole point -- a reset like `margin-left: 0` sits there precisely so the
 * logical property beside it is not doubled up under RTL -- so converting them
 * would dismantle the fix.
 */
const DIRECTION_FILE = /(^|[.\-_])(rtl|ltr)([.\-_]|$)/i;

function collectCss(target, out) {
    const stat = fs.statSync(target);
    if (stat.isDirectory()) {
        for (const entry of fs.readdirSync(target, { withFileTypes: true })) {
            if (entry.isDirectory() && SKIP_DIRS.has(entry.name)) continue;
            collectCss(path.join(target, entry.name), out);
        }
    } else if (
        target.endsWith('.css') &&
        !VENDOR_FILE.test(path.basename(target)) &&
        !DIRECTION_FILE.test(path.basename(target))
    ) {
        out.push(target);
    }
    return out;
}

function main() {
    const args = process.argv.slice(2);
    const dryRun = args.includes('--dry-run');
    const quiet = args.includes('--quiet');
    const targets = args.filter((a) => !a.startsWith('--'));

    if (!targets.length) {
        console.error('Usage: node tools/rtl-codemod.js <path...> [--dry-run] [--quiet]');
        process.exit(1);
    }

    const files = [];
    for (const t of targets) collectCss(path.resolve(t), files);

    const totals = { files: 0, changed: 0, propertyRewrites: 0, valueRewrites: 0, skipped: 0, counts: {} };
    const manualByFile = new Map();

    for (const file of files) {
        const css = fs.readFileSync(file, 'utf8');
        const report = {
            declarations: 0,
            propertyRewrites: 0,
            valueRewrites: 0,
            skipped: 0,
            counts: {},
            manual: [],
        };

        const out = transform(css, report);
        totals.files += 1;
        totals.propertyRewrites += report.propertyRewrites;
        totals.valueRewrites += report.valueRewrites;
        totals.skipped += report.skipped;
        for (const [k, v] of Object.entries(report.counts)) {
            totals.counts[k] = (totals.counts[k] || 0) + v;
        }

        if (report.manual.length) manualByFile.set(file, report.manual);

        if (out !== null) {
            totals.changed += 1;
            if (!dryRun) fs.writeFileSync(file, out, 'utf8');
            if (!quiet) {
                const rel = path.relative(process.cwd(), file).split(path.sep).join('/');
                console.log(
                    `${dryRun ? 'would fix' : 'fixed'}  ${rel}  (${report.propertyRewrites} props, ${report.valueRewrites} values)`
                );
            }
        }
    }

    console.log(
        `\n${totals.changed}/${totals.files} file(s) ${dryRun ? 'would change' : 'changed'} — ` +
        `${totals.propertyRewrites} property + ${totals.valueRewrites} value rewrites, ${totals.skipped} kept physical`
    );

    const sorted = Object.entries(totals.counts).sort((a, b) => b[1] - a[1]);
    if (sorted.length) {
        console.log('\nby declaration:');
        for (const [name, count] of sorted) console.log(`  ${String(count).padStart(4)}  ${name}`);
    }

    if (manualByFile.size) {
        let total = 0;
        for (const list of manualByFile.values()) total += list.length;
        console.log(`\n${total} declaration(s) need manual review:`);
        for (const [file, list] of manualByFile) {
            const rel = path.relative(process.cwd(), file).split(path.sep).join('/');
            console.log(`  ${rel}`);
            const seen = new Set();
            for (const item of list) {
                const key = `${item.prop}|${item.why}`;
                if (seen.has(key)) continue;
                seen.add(key);
                console.log(`    ${item.prop}: ${item.value}`);
                console.log(`      -> ${item.why}`);
            }
        }
    }
}

main();
