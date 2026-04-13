# Experiences Page: Tabs → Anchor Navigation

## Goal

Replace the tab-based layout on the experiences page with a scrollable single-page layout. Sections are navigated via anchor links in a sticky nav bar (desktop only).

## Structure

All sections are rendered in the DOM at once — no `hidden` toggling. Each section gets an `id` matching its slug:

- `#schedule` — the schedule section (keeps internal day toggle)
- `#<group-slug>` — one per group (music, installations, food, etc.)

The `<nav>` contains plain `<a href="#section-id">` anchor links. No `role="tablist"`, `role="tab"`, or `role="tabpanel"` — those are replaced with `<nav aria-label="Page sections">` and standard `<section>` elements.

The tab-switching JavaScript is removed entirely. The day-toggle JavaScript is kept as-is.

## Sticky Nav

- Desktop: `position: sticky; top: 0` with a solid `var(--color-bg)` background.
- A `--nav-height` CSS custom property (defined inline on the nav, e.g. `3rem`) is used for:
  - `top: 0` sticky offset (nav itself)
  - `scroll-margin-top: var(--nav-height)` on each `<section>` so anchor jumps land below the bar
- Mobile (below a reasonable breakpoint, e.g. `48rem`): nav is not sticky, scrolls naturally with the page.

## CSS Constraints

- Minimal — use existing design tokens from `global.css` throughout.
- No new tokens needed.
- Active-link highlighting (via `IntersectionObserver` or scroll listener) is out of scope for this iteration.

## Files Changed

- `src/templates/PageExperiences.astro` — markup, script, and style changes only. No other files touched.
