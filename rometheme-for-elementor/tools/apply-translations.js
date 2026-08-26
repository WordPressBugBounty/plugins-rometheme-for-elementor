#!/usr/bin/env node
/**
 * Merge a JSON translation map into a .po and compile the .mo.
 *
 *   node tools/apply-translations.js <pluginDir> <locale> <map.json> [--force]
 *
 * The map is `{ "source string": "translation" }`. Anything absent stays
 * untranslated, so a catalogue can be filled in passes.
 *
 * Reuses the PO parser, MO writer and placeholder check from translate-pot.js
 * rather than restating them, so a fix to one applies to both paths. The
 * placeholder check matters most here: a hand-written `%s` that drifts to `%$s`
 * is a fatal printf() error, not a typo.
 *
 * Existing translations are preserved unless --force is passed.
 */

'use strict';

const fs = require('fs');
const path = require('path');
const lib = require('./translate-pot');

function main() {
    const argv = process.argv.slice(2);
    const force = argv.includes('--force');
    const [dir, locale, mapPath] = argv.filter((a) => !a.startsWith('--'));

    if (!dir || !locale || !mapPath) {
        console.error('Usage: node tools/apply-translations.js <pluginDir> <locale> <map.json> [--force]');
        process.exit(1);
    }

    const pluginDir = path.resolve(dir);
    const langDir = path.join(pluginDir, 'languages');
    const potName = fs.readdirSync(langDir).find((f) => f.endsWith('.pot'));

    if (!potName) {
        console.error(`No .pot in ${langDir}`);
        process.exit(1);
    }

    const domain = potName.replace(/\.pot$/, '');
    const potText = fs.readFileSync(path.join(langDir, potName), 'utf8');
    const potEntries = lib.parsePo(potText).filter((e) => e.msgid !== '');

    const poPath = path.join(langDir, `${domain}-${locale}.po`);
    const moPath = path.join(langDir, `${domain}-${locale}.mo`);
    // WP 6.5+ reads this in preference to the .mo, so it must be rewritten in
    // the same pass. A stale one left behind wins silently.
    const phpPath = path.join(langDir, `${domain}-${locale}.l10n.php`);

    // Whatever is already translated, so repeated runs accumulate.
    const existing = new Map();
    if (fs.existsSync(poPath)) {
        for (const e of lib.parsePo(fs.readFileSync(poPath, 'utf8'))) {
            if (e.msgid && typeof e.msgstr === 'string' && e.msgstr !== '') {
                existing.set(e.msgid, e.msgstr);
            }
        }
    }

    const map = JSON.parse(fs.readFileSync(path.resolve(mapPath), 'utf8'));

    let applied = 0;
    let kept = 0;
    let rejected = 0;
    const unknown = [];

    const entries = potEntries.map((e) => {
        const previous = existing.get(e.msgid) || '';
        const proposed = Object.prototype.hasOwnProperty.call(map, e.msgid) ? map[e.msgid] : null;

        if (proposed !== null && proposed !== '' && (force || !previous)) {
            if (!lib.placeholdersMatch(e.msgid, proposed)) {
                rejected += 1;
                console.error(`  placeholder mismatch, skipped: ${JSON.stringify(e.msgid).slice(0, 70)}`);
                return { ...e, msgstr: previous };
            }
            applied += 1;
            return { ...e, msgstr: proposed };
        }

        if (previous) kept += 1;
        return { ...e, msgstr: previous };
    });

    // Anything in the map that no longer exists in the .pot -- usually a string
    // that changed in the source since the map was written.
    const known = new Set(potEntries.map((e) => e.msgid));
    for (const key of Object.keys(map)) {
        if (!known.has(key)) unknown.push(key);
    }

    const header = lib.buildHeader(lib.extractHeader(potText), locale);

    fs.writeFileSync(poPath, lib.serializePo(entries, header), 'utf8');
    fs.writeFileSync(moPath, lib.compileMo(entries));
    fs.writeFileSync(phpPath, lib.compilePhp(entries, header, locale), 'utf8');

    const translated = entries.filter((e) => e.msgstr).length;

    console.log(`${domain} / ${locale}`);
    console.log(`  applied    ${applied}`);
    if (kept) console.log(`  kept       ${kept} (already translated)`);
    if (rejected) console.log(`  rejected   ${rejected} (placeholder mismatch)`);
    console.log(`  coverage   ${translated}/${entries.length} (${Math.round((translated / entries.length) * 100)}%)`);
    console.log(`  wrote      ${path.basename(poPath)} + ${path.basename(moPath)}`);

    if (unknown.length) {
        console.log(`\n  ${unknown.length} entry in the map is not in the .pot (source string changed?):`);
        for (const u of unknown.slice(0, 10)) console.log(`    ${JSON.stringify(u).slice(0, 80)}`);
    }
}

main();
