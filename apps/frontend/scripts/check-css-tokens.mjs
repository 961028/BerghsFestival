#!/usr/bin/env node
// Fails if any component <style> block contains a hardcoded value for a
// property that should reference a design token. The intent is enforced by
// css-rules.md; this script catches drift mechanically.
//
// Allowed exceptions are encoded per-property below. Token definitions in
// global.css are skipped by ignoring lines that start a `--*:` declaration.

import { readFileSync, readdirSync, statSync } from "node:fs";
import { join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(fileURLToPath(import.meta.url), "../../src");

// Each rule: a property name and a predicate that returns true if a value is
// a violation (i.e. should be a token reference but isn't).
const RULES = [
    {
        property: "letter-spacing",
        violates: (value) => !/^var\(--/.test(value),
    },
    {
        property: "font-size",
        violates: (value) => !/^var\(--/.test(value),
    },
    {
        property: "font-weight",
        // `inherit` is a semantic keyword, not a value
        violates: (value) =>
            !/^var\(--/.test(value) && value.trim() !== "inherit",
    },
];

function* walk(dir) {
    for (const name of readdirSync(dir)) {
        const path = join(dir, name);
        if (statSync(path).isDirectory()) {
            yield* walk(path);
        } else if (/\.(astro|css)$/.test(name)) {
            yield path;
        }
    }
}

const violations = [];

for (const file of walk(ROOT)) {
    const text = readFileSync(file, "utf8");
    const lines = text.split("\n");

    for (let i = 0; i < lines.length; i++) {
        const line = lines[i];

        // Skip token definitions (lines that define a custom property)
        if (/^\s*--[a-z-]+:/.test(line)) continue;

        for (const { property, violates } of RULES) {
            const match = new RegExp(
                `(?<!-)\\b${property}\\s*:\\s*([^;}]+)`,
            ).exec(line);
            if (!match) continue;

            const value = match[1].trim();
            if (violates(value)) {
                violations.push({
                    file: relative(process.cwd(), file),
                    line: i + 1,
                    property,
                    value,
                });
            }
        }
    }
}

if (violations.length === 0) {
    console.log("✓ No hardcoded CSS values found.");
    process.exit(0);
}

console.error(`✗ Found ${violations.length} hardcoded CSS value(s):\n`);
for (const v of violations) {
    console.error(`  ${v.file}:${v.line}  ${v.property}: ${v.value}`);
}
console.error(
    "\nUse a design token from src/styles/global.css, or add one if none fits.",
);
console.error("See css-rules.md for the full rule.");
process.exit(1);
