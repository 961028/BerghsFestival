import { chromium } from 'playwright';
import { writeFileSync, mkdirSync } from 'node:fs';

const BASE = 'http://localhost:4321';
const OUT = 'a11y-report';
mkdirSync(OUT, { recursive: true });

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } });
const page = await ctx.newPage();

const log = [];
const note = (s) => { console.log(s); log.push(s); };

async function describeFocus() {
  return await page.evaluate(() => {
    const el = document.activeElement;
    if (!el || el === document.body) return null;
    const r = el.getBoundingClientRect();
    return {
      tag: el.tagName.toLowerCase(),
      id: el.id || null,
      cls: el.className && typeof el.className === 'string' ? el.className.slice(0, 60) : null,
      text: (el.textContent || '').trim().slice(0, 50),
      role: el.getAttribute('role'),
      ariaLabel: el.getAttribute('aria-label'),
      ariaExpanded: el.getAttribute('aria-expanded'),
      tabindex: el.getAttribute('tabindex'),
      offscreen: r.top < 0 || r.bottom > window.innerHeight,
      hidden: r.width === 0 || r.height === 0,
    };
  });
}

// ===== FULL TAB ORDER: home end-to-end =====
note('========== FULL TAB ORDER /home ==========');
await page.goto(BASE + '/', { waitUntil: 'networkidle' });
await page.waitForTimeout(1500);
let last = null;
let cycled = false;
for (let i = 0; i < 80; i++) {
  await page.keyboard.press('Tab');
  const info = await describeFocus();
  const sig = info ? `${info.tag}#${info.id || ''}.${info.cls || ''}|${info.text}` : 'NULL';
  if (last && sig === last && i > 5) { note(`  -- focus stuck at iter ${i}`); break; }
  note(`${String(i + 1).padStart(2)}. ${JSON.stringify(info)}`);
  // detect wrap to skip-link
  if (i > 5 && info?.cls?.includes('skip-link')) { cycled = true; note(`  -- cycled back to skip-link at iter ${i + 1}`); break; }
  last = sig;
}
note(`tab cycle completed: ${cycled}`);

// ===== SCHEDULE DAY-TAB =====
note('\n========== SCHEDULE day-tab activation ==========');
await page.goto(BASE + '/schedule', { waitUntil: 'networkidle' });
await page.waitForTimeout(1000);
const t0 = await page.$('#day-tab-0');
await t0.focus();
const before = await page.evaluate(() => ({
  tabs: Array.from(document.querySelectorAll('[role="tab"]')).map(t => ({ id: t.id, selected: t.getAttribute('aria-selected'), tabindex: t.getAttribute('tabindex') })),
  panels: Array.from(document.querySelectorAll('[role="tabpanel"], [id^="day-"]')).map(p => ({ id: p.id, hidden: p.hidden, hasHiddenAttr: p.hasAttribute('hidden'), ariaHidden: p.getAttribute('aria-hidden') })),
}));
note(`before: ${JSON.stringify(before, null, 2)}`);

await page.keyboard.press('ArrowRight');
await page.waitForTimeout(200);
const afterRight = await page.evaluate(() => ({
  focused: document.activeElement?.id,
  tabs: Array.from(document.querySelectorAll('[role="tab"]')).map(t => ({ id: t.id, selected: t.getAttribute('aria-selected'), tabindex: t.getAttribute('tabindex') })),
  panels: Array.from(document.querySelectorAll('[id^="day-"]')).map(p => ({ id: p.id, hidden: p.hidden })),
}));
note(`after ArrowRight: ${JSON.stringify(afterRight, null, 2)}`);

await page.keyboard.press('Home');
await page.waitForTimeout(200);
const afterHome = await page.evaluate(() => ({
  focused: document.activeElement?.id,
  tabs: Array.from(document.querySelectorAll('[role="tab"]')).map(t => ({ id: t.id, selected: t.getAttribute('aria-selected') })),
}));
note(`after Home: ${JSON.stringify(afterHome)}`);

// ===== WpVideo overlay button =====
note('\n========== WpVideo button (find page) ==========');
const routes = ['/', '/about-berghs', '/projects/at-est-fugiat-a-consequuntur', '/music'];
for (const r of routes) {
  await page.goto(BASE + r, { waitUntil: 'networkidle' });
  const btns = await page.$$('button[aria-label="Play video"], button[aria-label="Pause video"]');
  if (btns.length) {
    note(`${r}: found ${btns.length} video toggle button(s)`);
    const info = await page.evaluate(() => {
      const b = document.querySelector('button[aria-label="Play video"], button[aria-label="Pause video"]');
      if (!b) return null;
      const r = b.getBoundingClientRect();
      const s = window.getComputedStyle(b);
      return {
        ariaLabel: b.getAttribute('aria-label'),
        text: (b.textContent || '').trim().slice(0, 40),
        rect: { w: r.width, h: r.height },
        outlineFocus: s.outlineColor,
        position: s.position,
        zIndex: s.zIndex,
      };
    });
    note(`  details: ${JSON.stringify(info)}`);
    break;
  }
}

// ===== Reduced motion regression after fixes =====
note('\n========== reduced-motion ==========');
const rctx = await browser.newContext({ viewport: { width: 1440, height: 900 }, reducedMotion: 'reduce' });
const rp = await rctx.newPage();
await rp.goto(BASE + '/', { waitUntil: 'networkidle' });
await rp.waitForTimeout(1000);
const rState = await rp.evaluate(() => {
  const els = Array.from(document.querySelectorAll('[data-scramble]'));
  const ariaLabelCount = els.filter(e => e.hasAttribute('aria-label')).length;
  const transparentCount = els.filter(e => window.getComputedStyle(e).color === 'rgba(0, 0, 0, 0)' || window.getComputedStyle(e).color === 'transparent').length;
  return { total: els.length, withAriaLabel: ariaLabelCount, transparent: transparentCount };
});
note(`reduced-motion: ${JSON.stringify(rState)}`);
await rctx.close();

// ===== Sponsors links target=_blank? =====
note('\n========== sponsor links target ==========');
await page.goto(BASE + '/', { waitUntil: 'networkidle' });
const sponsors = await page.evaluate(() => {
  const list = document.querySelector('[data-sponsors]');
  if (!list) return null;
  return Array.from(list.querySelectorAll('a')).slice(0, 5).map(a => ({ href: a.href.slice(0, 60), target: a.target, rel: a.rel, ariaLabel: a.getAttribute('aria-label'), text: (a.textContent || '').trim().slice(0, 30), imgAlt: a.querySelector('img')?.alt }));
});
note(`sponsors: ${JSON.stringify(sponsors, null, 2)}`);

writeFileSync(`${OUT}/keyboard-log.txt`, log.join('\n'));
await browser.close();
