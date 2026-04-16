import { chromium } from "playwright";
import AxeBuilder from "@axe-core/playwright";

const PAGES = [
    { name: "Home", url: "http://localhost:4321/" },
    { name: "Projects", url: "http://localhost:4321/projects" },
    { name: "About", url: "http://localhost:4321/om-berghs" },
];

const browser = await chromium.launch();
const context = await browser.newContext();
const allIssues = [];

for (const { name, url } of PAGES) {
    const page = await context.newPage();
    await page.goto(url, { waitUntil: "networkidle" });

    const results = await new AxeBuilder({ page })
        .withTags(["wcag2a", "wcag2aa", "wcag21a", "wcag21aa", "best-practice"])
        .analyze();

    if (results.violations.length === 0) {
        console.log(`\n✓ ${name} (${url}) — no violations`);
    } else {
        console.log(
            `\n✗ ${name} (${url}) — ${results.violations.length} violation(s)`,
        );
        for (const v of results.violations) {
            console.log(`\n  [${v.impact?.toUpperCase()}] ${v.id}`);
            console.log(`  ${v.description}`);
            console.log(`  Help: ${v.helpUrl}`);
            for (const node of v.nodes) {
                console.log(`  → ${node.target.join(", ")}`);
                if (node.failureSummary) {
                    console.log(
                        `    ${node.failureSummary.replace(/\n/g, "\n    ")}`,
                    );
                }
            }
        }
    }

    allIssues.push({ name, url, violations: results.violations });
    await page.close();
}

await browser.close();

const total = allIssues.reduce((n, p) => n + p.violations.length, 0);
console.log(`\n─────────────────────────────`);
console.log(`Total violations: ${total} across ${PAGES.length} pages`);
