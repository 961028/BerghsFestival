import { chromium } from 'playwright';
import { AxeBuilder } from '@axe-core/playwright';
import { writeFileSync, mkdirSync } from 'node:fs';

const BASE = process.env.BASE_URL || 'http://localhost:4321';
const ROUTES = [
  '/',
  '/about-berghs',
  '/schedule',
  '/music',
  '/installations',
  '/food-drink',
  '/projects',
  '/projects/at-est-fugiat-a-consequuntur',
];

const OUT = 'a11y-report';
mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();

const summary = [];

for (const route of ROUTES) {
  const url = BASE + route;
  await page.goto(url, { waitUntil: 'networkidle' });
  const results = await new AxeBuilder({ page })
    .withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'best-practice'])
    .analyze();

  const slug = route === '/' ? 'home' : route.replace(/^\//, '').replace(/\//g, '_');
  writeFileSync(`${OUT}/${slug}.json`, JSON.stringify(results, null, 2));

  const counts = results.violations.reduce((acc, v) => {
    acc[v.impact || 'unknown'] = (acc[v.impact || 'unknown'] || 0) + v.nodes.length;
    return acc;
  }, {});

  summary.push({
    route,
    violations: results.violations.length,
    nodes: results.violations.reduce((n, v) => n + v.nodes.length, 0),
    byImpact: counts,
    rules: results.violations.map((v) => ({
      id: v.id,
      impact: v.impact,
      help: v.help,
      nodes: v.nodes.length,
      sample: v.nodes.slice(0, 2).map((n) => ({
        target: n.target,
        html: n.html.slice(0, 220),
        failureSummary: n.failureSummary,
      })),
    })),
  });
}

writeFileSync(`${OUT}/summary.json`, JSON.stringify(summary, null, 2));

for (const r of summary) {
  console.log(`\n=== ${r.route} — ${r.violations} rules / ${r.nodes} nodes ===`);
  for (const rule of r.rules) {
    console.log(`  [${rule.impact}] ${rule.id} (${rule.nodes}): ${rule.help}`);
  }
}

await browser.close();
