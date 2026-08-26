#!/usr/bin/env node
/**
 * Reuse translations already written for one plugin in another.
 *
 *   node tools/translation-memory.js <targetPluginDir> [--source <dir>]... [--emit <outDir>] [--scope <regex>]
 *
 * The six plugins share a vocabulary -- "Settings", "Enable", "Border Radius",
 * "Save Changes" -- so translating each catalogue from scratch would both waste
 * effort and let the same term drift ("Aktifkan" here, "Nyalakan" there). This
 * builds a memory from every .po in the source plugins and reports, per locale,
 * which of the target's strings it already covers.
 *
 * With --emit it writes one JSON map per locale in the shape apply-translations.js
 * expects, containing only the exact hits. The residual list is what still needs
 * a human.
 *
 * Matching is exact on msgid. Fuzzy matching is deliberately absent: a near-miss
 * silently shipped as a real translation is worse than an untranslated string,
 * because nothing downstream flags it.
 *
 * --scope limits the residual report to references matching a regex, which is how
 * the admin-visible subset gets separated from Elementor control labels.
 */

'use strict';

const fs = require('fs');
const path = require('path');
const lib = require('./translate-pot');

const DEFAULT_SOURCES = [path.resolve(__dirname, '..')];

function localeOf(file, domain) {
    const m = file.match(new RegExp(`^${domain.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}-(.+)\\.po$`));
    return m ? m[1] : null;
}

/** msgid -> locale -> msgstr, across every .po found in the source plugins. */
function buildMemory(sourceDirs) {
    const memory = new Map();
    const seen = [];

    for (const dir of sourceDirs) {
        const langDir = path.join(dir, 'languages');
        if (!fs.existsSync(langDir)) continue;

        const potName = fs.readdirSync(langDir).find((f) => f.endsWith('.pot'));
        if (!potName) continue;
        const domain = potName.replace(/\.pot$/, '');

        for (const file of fs.readdirSync(langDir)) {
            const locale = file.endsWith('.po') ? localeOf(file, domain) : null;
            if (!locale) continue;

            let count = 0;
            for (const e of lib.parsePo(fs.readFileSync(path.join(langDir, file), 'utf8'))) {
                if (!e.msgid || typeof e.msgstr !== 'string' || e.msgstr === '') continue;
                if (!memory.has(e.msgid)) memory.set(e.msgid, new Map());
                // First source wins, so the order of --source expresses priority.
                if (!memory.get(e.msgid).has(locale)) {
                    memory.get(e.msgid).set(locale, e.msgstr);
                    count += 1;
                }
            }
            seen.push({ plugin: path.basename(dir), locale, count });
        }
    }

    return { memory, seen };
}

function main() {
    const argv = process.argv.slice(2);
    const sources = [];
    let emitDir = null;
    let scope = null;
    const positional = [];

    for (let i = 0; i < argv.length; i += 1) {
        if (argv[i] === '--source') sources.push(path.resolve(argv[++i]));
        else if (argv[i] === '--emit') emitDir = path.resolve(argv[++i]);
        else if (argv[i] === '--scope') scope = new RegExp(argv[++i]);
        else positional.push(argv[i]);
    }

    if (!positional[0]) {
        console.error('Usage: node tools/translation-memory.js <targetPluginDir> [--source <dir>] [--emit <outDir>] [--scope <regex>]');
        process.exit(1);
    }

    const target = path.resolve(positional[0]);
    const sourceDirs = (sources.length ? sources : DEFAULT_SOURCES).filter((d) => d !== target);

    const { memory, seen } = buildMemory(sourceDirs);

    const langDir = path.join(target, 'languages');
    const potName = fs.readdirSync(langDir).find((f) => f.endsWith('.pot'));
    const potText = fs.readFileSync(path.join(langDir, potName), 'utf8');
    const entries = lib.parsePo(potText).filter((e) => e.msgid !== '');

    const inScope = entries.filter((e) => {
        if (!scope) return true;
        return scope.test((e.refs || []).join(' '));
    });

    const locales = [...new Set(seen.map((s) => s.locale))].sort();

    console.log(`memory   ${memory.size} unique string from ${new Set(seen.map((s) => s.plugin)).size} plugin, ${locales.length} locale`);
    console.log(`target   ${path.basename(target)} -- ${entries.length} string, ${inScope.length} in scope\n`);

    const residualByLocale = new Map();

    for (const locale of locales) {
        const map = {};
        const missing = [];

        for (const e of inScope) {
            const hit = memory.get(e.msgid)?.get(locale);
            if (hit && lib.placeholdersMatch(e.msgid, hit)) map[e.msgid] = hit;
            else missing.push(e.msgid);
        }

        residualByLocale.set(locale, missing);

        const pct = inScope.length ? Math.round((Object.keys(map).length / inScope.length) * 100) : 0;
        console.log(`  ${locale.padEnd(7)} ${String(Object.keys(map).length).padStart(4)}/${inScope.length}  (${pct}%)  sisa ${missing.length}`);

        if (emitDir) {
            fs.mkdirSync(emitDir, { recursive: true });
            fs.writeFileSync(path.join(emitDir, `${locale}.json`), JSON.stringify(map, null, 2) + '\n', 'utf8');
        }
    }

    // The residual is the same set for every locale (memory coverage is uniform
    // when the sources were all filled from the same map), so report it once.
    const union = [...new Set([...residualByLocale.values()].flat())].sort();
    console.log(`\nsisa gabungan  ${union.length} string perlu terjemahan baru`);

    if (emitDir) {
        fs.writeFileSync(path.join(emitDir, '_residual.json'), JSON.stringify(union, null, 2) + '\n', 'utf8');
        console.log(`emit           ${emitDir}`);
    }
}

if (require.main === module) main();

module.exports = { buildMemory };
