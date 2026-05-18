#!/usr/bin/env node
// Fails if any component <style> block contains a hardcoded value for a
// property that should reference a design token, or if a rule block pairs
// a known type role with the wrong letter-spacing token. Intent is documented
// in css-rules.md; this script catches drift mechanically.

import { readFileSync, readdirSync, statSync } from "node:fs";
import { join, relative } from "node:path";
import { fileURLToPath } from "node:url";

const ROOT = join(fileURLToPath(import.meta.url), "../../src");

// Spacing exceptions allowed per css-rules.md:
// - `0` (with any unit) — structural reset
// - `auto` — flex/grid sizing
// - `100%`, `100dvh`, `100vh`, `100vw`, `100dvw` — full-viewport sizing
// - negative pixel hairlines like `-1px` — border-overlap tricks
// - `max(...)`, `min(...)`, `clamp(...)`, `calc(...)` — composed values
// - `inherit`, `unset`, `revert`, `initial` — keywords
const SPACING_KEYWORDS = new Set([
    "0",
    "auto",
    "inherit",
    "unset",
    "revert",
    "initial",
    "none",
]);

function isSpacingTokenValue(value) {
    const v = value.trim();
    if (SPACING_KEYWORDS.has(v)) return true;
    // 0 with any unit
    if (/^-?0(\.\d+)?(px|rem|em|%|vh|vw|dvh|dvw|cqi|cqb)?$/.test(v)) return true;
    // 100% / 100dvh family
    if (/^100(%|dvh|vh|dvw|vw)$/.test(v)) return true;
    // hairline -1px / 0.5px / -0.5px for border tricks
    if (/^-?0?\.?\d+px$/.test(v) && Math.abs(parseFloat(v)) <= 1) return true;
    // var() — tokenised
    if (/^var\(--/.test(v)) return true;
    // composed: max/min/clamp/calc — assume composed of tokens; allow
    if (/^(calc|clamp|min|max)\(/.test(v)) return true;
    // multi-value (e.g. shorthand `var(--space-sm) var(--space-md)`)
    if (/\s/.test(v)) {
        return v.split(/\s+/).every((part) => isSpacingTokenValue(part));
    }
    return false;
}

const SPACING_PROPERTIES = [
    "margin",
    "margin-block",
    "margin-block-start",
    "margin-block-end",
    "margin-inline",
    "margin-inline-start",
    "margin-inline-end",
    "margin-top",
    "margin-right",
    "margin-bottom",
    "margin-left",
    "padding",
    "padding-block",
    "padding-block-start",
    "padding-block-end",
    "padding-inline",
    "padding-inline-start",
    "padding-inline-end",
    "padding-top",
    "padding-right",
    "padding-bottom",
    "padding-left",
    "gap",
    "column-gap",
    "row-gap",
];

const COLOR_KEYWORDS = new Set([
    "inherit",
    "currentColor",
    "currentcolor",
    "transparent",
    "unset",
    "revert",
    "initial",
    "none",
]);

function isColorTokenValue(value) {
    const v = value.trim();
    if (COLOR_KEYWORDS.has(v)) return true;
    if (/^var\(--/.test(v)) return true;
    return false;
}

const HARDCODE_RULES = [
    {
        property: "letter-spacing",
        violates: (value) =>
            !/^var\(--/.test(value) && value.trim() !== "inherit",
    },
    {
        property: "font-size",
        violates: (value) =>
            !/^var\(--/.test(value) &&
            value.trim() !== "inherit" &&
            !/\d+cqi/.test(value),
    },
    {
        property: "font-weight",
        violates: (value) =>
            !/^var\(--/.test(value) && value.trim() !== "inherit",
    },
    {
        property: "line-height",
        violates: (value) =>
            !/^var\(--/.test(value) &&
            value.trim() !== "inherit" &&
            !value.startsWith("calc("),
    },
    ...SPACING_PROPERTIES.map((property) => ({
        property,
        violates: (value) => !isSpacingTokenValue(value),
    })),
    {
        property: "color",
        violates: (value) => !isColorTokenValue(value),
    },
    {
        property: "background-color",
        violates: (value) => !isColorTokenValue(value),
    },
    {
        property: "border-color",
        violates: (value) => !isColorTokenValue(value),
    },
    {
        // `background` shorthand — token, color keyword, none, url(), or gradient()
        property: "background",
        violates: (value) => {
            const v = value.trim();
            if (v === "none" || v.startsWith("url(") || /gradient\(/.test(v))
                return false;
            return !isColorTokenValue(v);
        },
    },
    {
        // `border` shorthand — must be `0`, `none`, or `var(--border)`
        property: "border",
        violates: (value) => {
            const v = value.trim();
            if (v === "0" || v === "none") return false;
            if (/^var\(--/.test(v)) return false;
            return true;
        },
    },
];

// Role rules check a rule block's combined declarations. Each rule returns
// either null (ok / not applicable) or a string describing the violation.
const ROLE_RULES = [
    {
        name: "uppercase-large → caps-lg",
        check: (decls) => {
            if (decls["text-transform"] !== "uppercase") return null;
            const size = decls["font-size"];
            if (
                size !== "var(--font-size-lg)" &&
                size !== "var(--font-size-display)"
            )
                return null;
            const ls = decls["letter-spacing"];
            if (ls === "var(--letter-spacing-caps-lg)") return null;
            return `uppercase ${size} should use --letter-spacing-caps-lg, got ${ls ?? "<missing>"}`;
        },
    },
    {
        name: "uppercase-small → caps-sm",
        check: (decls) => {
            if (decls["text-transform"] !== "uppercase") return null;
            const size = decls["font-size"];
            if (
                size !== "var(--font-size-sm)" &&
                size !== "var(--font-size-base)"
            )
                return null;
            const ls = decls["letter-spacing"];
            if (ls === "var(--letter-spacing-caps-sm)") return null;
            return `uppercase ${size} should use --letter-spacing-caps-sm, got ${ls ?? "<missing>"}`;
        },
    },
    {
        name: "large-heading → display",
        check: (decls) => {
            if (decls["text-transform"] === "uppercase") return null;
            if (decls["font-size"] !== "var(--font-size-lg)") return null;
            if (decls["font-weight"] !== "var(--font-weight-bold)") return null;
            const ls = decls["letter-spacing"];
            if (ls === "var(--letter-spacing-display)") return null;
            return `lg bold heading should use --letter-spacing-display, got ${ls ?? "<missing>"}`;
        },
    },
    {
        name: "body-no-tight-tracking",
        check: (decls) => {
            const ls = decls["letter-spacing"];
            if (ls !== "var(--letter-spacing-display)") return null;
            const size = decls["font-size"];
            const weight = decls["font-weight"];
            // Skip override blocks that only adjust tracking without redefining
            // the role's size/weight — the parent rule owns the role check.
            if (!size && !weight) return null;
            const allowedSizes = new Set([
                "var(--font-size-lg)",
                "var(--font-size-display)",
            ]);
            const isHeadingSize = allowedSizes.has(size);
            const isBold = weight === "var(--font-weight-bold)";
            if (isHeadingSize && isBold) return null;
            return `--letter-spacing-display only belongs on lg/display bold headings (size=${size ?? "?"}, weight=${weight ?? "?"})`;
        },
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

// Parse a CSS source into top-level rule blocks. Tracks brace depth so nested
// blocks (@media, @container) become their own rules. Returns an array of
// { selector, startLine, decls } where decls maps property → trimmed value.
function parseBlocks(source) {
    const blocks = [];
    let depth = 0;
    let buf = "";
    let bufStartLine = 1;
    let line = 1;
    const stack = [];

    for (let i = 0; i < source.length; i++) {
        const ch = source[i];
        if (ch === "\n") line++;
        if (ch === "{") {
            const selector = buf.trim();
            stack.push({ selector, startLine: bufStartLine, body: "", line });
            buf = "";
            bufStartLine = line;
            depth++;
        } else if (ch === "}") {
            const frame = stack.pop();
            if (frame) {
                // Only collect leaf blocks (no nested rule starts in body via
                // re-scanning — simpler: re-parse later).
                blocks.push({
                    selector: frame.selector,
                    startLine: frame.startLine,
                    body: frame.body + buf,
                });
            }
            buf = "";
            bufStartLine = line;
            depth--;
        } else {
            if (stack.length > 0) stack[stack.length - 1].body += ch;
            else buf += ch;
        }
    }
    return blocks;
}

function extractDecls(body) {
    const decls = {};
    // Strip nested blocks so @media inside a rule doesn't pollute decls
    const stripped = body.replace(/\{[^{}]*\}/g, "");
    for (const stmt of stripped.split(";")) {
        const idx = stmt.indexOf(":");
        if (idx === -1) continue;
        const prop = stmt.slice(0, idx).trim();
        const value = stmt.slice(idx + 1).trim();
        if (!prop || prop.startsWith("--")) continue;
        if (!/^[a-z-]+$/.test(prop)) continue;
        decls[prop] = value;
    }
    return decls;
}

function extractStyleSources(file, text) {
    if (file.endsWith(".css")) return [{ text, offset: 0 }];
    // Blank out <script> blocks so `<style>` mentions in JS don't confuse the
    // style-block regex (whitespace preserves line numbers + offsets).
    const masked = text.replace(
        /<script[^>]*>[\s\S]*?<\/script>/g,
        (m) => m.replace(/[^\n]/g, " "),
    );
    const out = [];
    const re = /<style[^>]*>([\s\S]*?)<\/style>/g;
    let m;
    while ((m = re.exec(masked)) !== null) {
        const start = m.index + m[0].indexOf(m[1]);
        out.push({ text: m[1], offset: start });
    }
    return out;
}

function lineOf(text, offset) {
    let n = 1;
    for (let i = 0; i < offset && i < text.length; i++) {
        if (text[i] === "\n") n++;
    }
    return n;
}

const hardcodeViolations = [];
const roleViolations = [];

for (const file of walk(ROOT)) {
    const text = readFileSync(file, "utf8");

    // Hardcode check (scoped to style blocks only — skip <script> and markup)
    for (const src of extractStyleSources(file, text)) {
        // Strip CSS comments to avoid matching property names in comments
        const cleaned = src.text.replace(/\/\*[\s\S]*?\*\//g, (m) =>
            m.replace(/[^\n]/g, " "),
        );
        const startLine = lineOf(text, src.offset);
        const lines = cleaned.split("\n");
        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];
            if (/^\s*--[a-z-]+:/.test(line)) continue;
            for (const { property, violates } of HARDCODE_RULES) {
                const match = new RegExp(
                    `(?<!-)\\b${property}\\s*:\\s*([^;}]+)`,
                ).exec(line);
                if (!match) continue;
                const value = match[1].trim();
                if (violates(value)) {
                    hardcodeViolations.push({
                        file: relative(process.cwd(), file),
                        line: startLine + i,
                        property,
                        value,
                    });
                }
            }
        }
    }

    // Role-pairing check (block-based)
    for (const src of extractStyleSources(file, text)) {
        const blocks = parseBlocks(src.text);
        for (const block of blocks) {
            const decls = extractDecls(block.body);
            if (Object.keys(decls).length === 0) continue;
            for (const rule of ROLE_RULES) {
                const msg = rule.check(decls);
                if (!msg) continue;
                const absLine = lineOf(text, src.offset) + block.startLine - 1;
                roleViolations.push({
                    file: relative(process.cwd(), file),
                    line: absLine,
                    selector: block.selector.split("\n").pop().trim(),
                    rule: rule.name,
                    msg,
                });
            }
        }
    }
}

let failed = false;

if (hardcodeViolations.length > 0) {
    failed = true;
    console.error(
        `✗ Found ${hardcodeViolations.length} hardcoded CSS value(s):\n`,
    );
    for (const v of hardcodeViolations) {
        console.error(`  ${v.file}:${v.line}  ${v.property}: ${v.value}`);
    }
    console.error(
        "\nUse a design token from src/styles/global.css, or add one if none fits.",
    );
}

if (roleViolations.length > 0) {
    failed = true;
    if (hardcodeViolations.length > 0) console.error("");
    console.error(
        `✗ Found ${roleViolations.length} typography role mismatch(es):\n`,
    );
    for (const v of roleViolations) {
        console.error(`  ${v.file}:${v.line}  ${v.selector}  — ${v.msg}`);
    }
    console.error(
        "\nSee the role → token table in css-rules.md.",
    );
}

if (failed) process.exit(1);

console.log("✓ No CSS token or typography role violations found.");
