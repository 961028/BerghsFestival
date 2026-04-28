# Experiences page split — design

## Goal

Split the single `/experiences` page into one page per section under `/experiences/*` and surface those sections in the main site nav (dropdown on desktop, indented in the hamburger on mobile). Remove the sticky in-page nav, IntersectionObserver scroll-spy, and the `--sticky-nav-height` machinery that supports it.

## Why

- **Code health.** `PageExperiences.astro` carries a sticky `.page-nav`, IntersectionObserver scroll-spy, a `--sticky-nav-height` ResizeObserver, and `scroll-margin-top` handling — all to make a single long page feel like several pages. Splitting deletes that complexity.
- **Sharing & SEO.** Each section gets its own URL, meta description, and OG image.
- **Smaller payloads per page.** `/experiences/food` doesn't load schedule + music data.
- **Fits real use.** Festival info is looked up, not browsed.

## Routing

- **New** `src/pages/experiences/[slug].astro` — dynamic route. `getStaticPaths` reads the experiences WP page once, parses `acf.schedule` and `acf.groups` with the shared Zod schemas, and emits one path per section: `schedule` + each `group.slug`. Props: `{ page, section }` where `section` is `{ kind: "schedule", schedule } | { kind: "group", group }`.
- **New** `src/pages/experiences/index.astro` — `Astro.redirect("/experiences/schedule")`.
- **Modify** `src/pages/[...link].astro` — exclude pages whose `template === "page-experiences.php"` from `getStaticPaths` so it doesn't double-render at `/experiences`.
- **Modify** `src/components/PageTemplate.astro` — remove the `page-experiences.php` entry from `SLUG_TO_TEMPLATE` (no longer reachable; remove rather than leave dead).

## Templates

- **New** `src/templates/ExperiencesSchedule.astro` — schedule list + day toggle. Lifted from the schedule section of the current `PageExperiences.astro` along with its day-toggle script and the day-toggle / schedule-rows CSS. The default-day logic stays as-is (`new Date().getDay() === 6 ? 1 : 0`).
- **New** `src/templates/ExperiencesGroup.astro` — takes a single `group` prop, renders the description + the layout-specific list (`music` / `installations` / `food`). Lifted from the group section of the current `PageExperiences.astro` along with its group/food CSS.
- **New** `src/lib/experiences.ts` — exports `ScheduleSchema` and `GroupsSchema` (currently inline in `PageExperiences.astro`) so the dynamic route and `Header.astro` share one source of truth.
- **Delete** `src/templates/PageExperiences.astro` once nothing references it.

`[slug].astro` picks `ExperiencesSchedule` if `slug === "schedule"`, otherwise `ExperiencesGroup` with the matching group. The page's `<Layout>` wrapper (title, metaDescription, ogImage, ctaCompact) lives in `[slug].astro` and uses per-section title/meta where it makes sense (e.g. `${page.title} — ${section.label}`).

## Header / nav

The WP primary menu still has one "Experiences" item. We don't change WordPress; we transform that item at render time.

- **`Header.astro`** fetches the experiences WP page via `getEntry`, parses `acf.groups` with the shared schema, and derives sub-items: `[{ label: "Schedule", href: "/experiences/schedule" }, ...groups.map(g => ({ label: g.title, href: `/experiences/${g.slug}` }))]`.
- For the menu item whose URL pathname is `/experiences`, replace the `<a>` with a `<button class="nav-link" aria-expanded="false" aria-controls="experiences-submenu">` containing the label and a chevron `<Svg>`. The button is **never a link** — its only job is to toggle the submenu. The same applies on mobile (rendered as a non-link header above the indented sub-items).
- **Desktop submenu** — `<ul class="submenu" id="experiences-submenu">` positioned absolutely below the parent `<li>`. Hidden by default; visible on `:hover` of the parent `<li>`, on `:focus-within`, and when the button has `aria-expanded="true"`. The chevron rotates 180° when open (CSS transition driven by `[aria-expanded="true"]`).
- **Mobile submenu** — within `.mobile-menu`, render the experiences entry as a non-link label followed by indented sub-item links inline. No toggle — the overlay is already a "show everything" view.
- **`header.ts`** — click handler on the experiences button toggles `aria-expanded`; outside-click and Escape close it (reuses the existing Escape listener). The existing `.menu-toggle` and mobile menu logic is unchanged.
- **`aria-current="page"`** — works automatically on sub-items via `Astro.url.pathname` matching the sub-item href, mirroring the existing pattern.

## Removed

From `PageExperiences.astro` (which is being deleted, but listing for clarity):
- The sticky `nav.page-nav` markup and its CSS
- The IntersectionObserver scroll-spy script
- The `--sticky-nav-height` ResizeObserver and the CSS variable
- `scroll-margin-top` on `section`
- The `sections` array derivation
- `--experiences-nav-height` (if defined elsewhere) — verify and remove if unused

## Behaviour notes

- **Day toggle stays as-is.** Default day, click handlers, panel hiding all unchanged. It only lives on `/experiences/schedule` now.
- **No client-side routing.** Each `/experiences/*` URL is a separate static page; navigation is normal.
- **Reduced motion.** The chevron rotation is a CSS `transform: rotate(180deg)` driven by `[aria-expanded="true"]`, with a short `transition` on `transform`. Under `prefers-reduced-motion: reduce` the transition is removed; the rotated state still applies (open vs. closed remains legible).

## Files touched

| Action  | Path |
|--------|------|
| New    | `src/pages/experiences/[slug].astro` |
| New    | `src/pages/experiences/index.astro` |
| New    | `src/templates/ExperiencesSchedule.astro` |
| New    | `src/templates/ExperiencesGroup.astro` |
| New    | `src/lib/experiences.ts` |
| Modify | `src/pages/[...link].astro` |
| Modify | `src/components/PageTemplate.astro` |
| Modify | `src/components/site/Header.astro` |
| Modify | `src/scripts/header.ts` |
| Delete | `src/templates/PageExperiences.astro` |

## Out of scope

- Changes to the WP menu structure (still one "Experiences" item).
- A real `/experiences` overview/landing page (the parent is a label, not a destination).
- Any redesign of the schedule, music, installations, or food layouts.
- Animations beyond the chevron rotate.
