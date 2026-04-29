# Berghs Festival 2026 — Information architecture and design decisions

---

## Site structure

Same nav on every page. The "Experiences" entry in the nav is a parent label with a submenu — it does not link anywhere on its own.

| Nav label   | Routes                                                  | Purpose                                     |
| ----------- | ------------------------------------------------------- | ------------------------------------------- |
| Home        | `/`                                                     | Emotional sell, logistics, theme manifesto  |
| Experiences | `/schedule`, `/music`, `/installations`, `/food-drink`  | Festival programming, split by section      |
| Projects    | `/projects`, `/projects/<slug>`                         | Graduation work from the class of 2026      |
| About       | `/about-berghs`                                         | Berghs intro + the team behind the festival |

### Changes from previous years

- "Installations" and "Happening" merged into a single **Experiences** group, but each section is its own top-level route (`/schedule`, `/music`, `/installations`, `/food-drink`) — there is no single `/experiences` page. The header surfaces them as a submenu under "Experiences".
- **About** expanded to include a new "The team" section crediting students and teachers, organized by festival role.

---

## Page-by-page content

### Home

- Hero moment (video or animation) at the top.
- Festival name, dates, times, location.
- Theme manifesto tied to the year's concept.
- Schedule overview (Friday and Saturday at a glance).
- Registration CTA (in addition to persistent header CTA).

### Experiences

Each section is its own top-level route — there is no single `/experiences` page. Sections are surfaced through a submenu on the "Experiences" item in the site header. Each section is a regular WordPress page using a dedicated page template (`page-schedule.php`, `page-experience-music.php`, `page-experience-installations.php`, `page-experience-food.php`); the URL is the page's WP slug.

1. **Schedule** (`/schedule`) — two-day timeline (Friday evening, Saturday daytime). Both days are always visible; the relevant day is preselected based on the current date. Before or after the festival, Friday is preselected. On Saturday, Saturday is preselected. Day toggle is sticky under the global header so day context stays put while the row list scrolls. Every row has the same shape: a muted bold tabular start time at `--font-size-lg`, a base-size bold title, and an optional muted description tucked underneath. The schedule has no images. An optional WYSIWYG description (`description` ACF field on the Schedule page template) renders above the day toggle, matching the intro pattern used by the other section pages.
2. **Music** (`/music`) — horizontal lineup. Image left (square, 40% width on ≥30rem), text right (large bold name + muted description). 1px dividers between rows, no card outline. Items can carry an optional `day`, `start_time`, and `location`; when `day` is set, items are grouped by day in schedule order, with a leading day heading. The horizontal rhythm reads as a tour-poster lineup and visually distinguishes Music from Installations.
3. **Installations** (`/installations`) — full-width gallery. One installation per row with a large landscape (2:1) image, large bold name, optional location, and muted description. Installations are art pieces and get visual presence accordingly. No card outlines — generous spacing does the separating.
4. **Food & drink** (`/food-drink`) — responsive card grid (1 → 2 → 3 columns). Bordered cards with a square image, large bold vendor name, optional location, and muted description. Density signals "there are options" and the bordered cards read as discrete menu items rather than banners.

The set of sections is fixed in code: each is its own page template + Astro template (`PageSchedule`, `PageExperienceMusic`, `PageExperienceInstallations`, `PageExperienceFood`), wired up in `PageTemplate.astro`'s `SLUG_TO_TEMPLATE`. Adding a new section means adding a template on both sides; there is no longer a generic "groups" repeater that produces sections at runtime.

**Shared subpage vocabulary:** All four subpages share the same typographic system — primary identifiers (time, name) at `--font-size-lg` with `--letter-spacing-tight`, descriptions at base size in `--color-text-muted`, 1px dividers from `var(--border-default)`. This is the same vocabulary used by the home page's hero schedule grid, so the subpages read as part of one family even though each grid is shaped to its content.

"Other activities" has been dropped.

Each section has a distinct layout treatment. This is intentional: the content types have different data shapes and different browsing behaviors.

**Why per-section routes instead of a single scrollable page:** The earlier single-page approach used an in-page sticky section nav with scroll-spy (IntersectionObserver) to highlight the active section. It worked, but URLs were not section-specific (everything lived at `/experiences/#anchor`), so links and bookmarks couldn't deep-link reliably to a section, and the page accumulated all sections' DOM and styles regardless of what the visitor wanted to see. Splitting the page makes each section its own URL — bookmarkable, shareable, and independently rendered — and replaces the scroll-spy machinery with a plain header submenu.

**Why top-level URLs (`/music`) instead of nested (`/experiences/music`):** The sections need to be punchable into a search bar. `berghsfestivalen.se/music` is short, memorable, and easy to type or read aloud — `berghsfestivalen.se/experiences/music` is not. The nesting carried no information visitors needed; the submenu already communicates the grouping. There is no `/experiences` hub page — the "Experiences" header item is a label-only parent, not a link.

**Why each section is its own WP page template instead of one shared "groups" repeater:** The sections diverge enough in shape (Schedule has days + events; Music has items grouped by day with time + location; Installations has wide images; Food & drink is a card grid) that a single repeater either grew a long list of optional fields polluting every section, or forced the renderer into branching by group type. Giving each section its own ACF field group and Astro template makes the editing UI in WP match the page's actual shape, and lets each section's renderer be straightforward.

**Why the section nav lives in the site header, not on the page:** The header is already the persistent navigation surface. Promoting "Experiences" to a submenu reuses that surface instead of inventing a second navigation pattern below it. It also means the section list is always visible at the top of the viewport without any sticky-nav layout cost.

**How the submenu opens:** On desktop the "Experiences" toggle is a `<button>` that opens the submenu on click; the submenu closes on click outside, on Escape (returns focus to the toggle), or after a child link is clicked. On mobile the entire menu collapses into the hamburger overlay and submenus are flattened — every child link appears as a top-level entry, since the overlay has the room and a nested toggle on touch is unnecessary friction.

**Why both days are always accessible:** An earlier approach hid Saturday during Friday evening. This was overengineered. Simple preselection is less code, less confusing, and more respectful of user autonomy.

**Why a compact registration CTA on this page (mobile + tablet only):** Carried over from the single-page version. Below the desktop breakpoint (64rem), the CTA drops the dates/venue line to reduce its height. On desktop the full CTA returns. Originally justified by the in-page sticky nav (now gone), but kept for now — revisit if the constraint that motivated it no longer applies.

### Projects

- Filter between **All**, **Group**, and **Individual** projects at the top of the page.
- Grid layout with 3:2 thumbnail images and minimal text per card (project title + client name).
- Each card links to a detail page.
- Detail pages show a selection of related projects.

### About

Two sections:

1. **Berghs School of Communication** — short institutional intro. Link to berghs.se for more.
2. **The team** — students and supporting teachers who built the festival, organized by role (creative direction, production, web, marketing, etc.).

---

## Global elements

### Persistent registration CTA

- Built into the sticky navigation header.
- Visible at all scroll positions, on every page.
- Links to Eventbrite registration.
- Replaces the previous approach of in-page links that disappeared on scroll.

### Navigation header

- Festival logo/name on the right (Berghs SoC mark, links to berghs.se).
- Nav items on the left: Experiences (submenu — Schedule, Music, Installations, Food & drink), Projects, About.
- Below the tablet breakpoint the nav collapses into a hamburger overlay; the Experiences submenu is flattened so every section appears as its own top-level link in the overlay.

### Footer (every page)

- Sponsor logos.
- Berghs contact info.
- Social links.
- Short responsible drinking notice (one line) linking to a dedicated policy page.
- Photo/video consent notice.

### Responsible drinking

- One-line notice in the footer of every page.
- Links to a standalone page with the full policy text and a link to IQ.se.
- Replaces the full-paragraph notice that was duplicated across all pages in previous years.

---

## Projects page — design decisions

### Layout: fluid grid with team names

Chosen over alternatives for the following reasons:

- Large enough 3:2 images to show the quality of the work.
- Room for client name, project title, and team member names on each card.
- Balances visual impact with information density.
- Fluid grid adapts to available space — narrower on mobile, wider on desktop.

### Card structure

Each card contains:

1. Thumbnail image (3:2 aspect ratio).
2. Client name (small text above the title).
3. Project title.
4. Team member names + Programme (small text below the title).

### Image requirements

- **Format:** 3:2 aspect ratio only.
- **Minimum resolution:** 1200 x 800px to support retina displays.
- Students provide one image per project. No other formats are needed.

### Responsive behavior

The grid uses `auto-fit` with a minimum column width of 20rem, so the number of columns adjusts fluidly to the viewport width rather than switching at fixed breakpoints.

### Group/Individual filter

- Three buttons at the top of the page: All, Group, Individual.
- Defaults to "All" selected.
- Group and individual projects are mixed together in the same grid rather than separated into distinct sections. This is my decision — previous years kept them separate, but mixing them and adding a filter gives every project a fair and equal position in the listing.
- One of the main reasons to mix them is fairness in the random ordering (see below).

### Project ordering

Projects are displayed in a random order to give every project an equal chance of being seen. This has been the approach in previous years.

My change from previous years: the order is randomised once per visitor session rather than on every page refresh. Per-refresh randomisation causes projects to reorder mid-visit, which is a poor experience. Per-session randomisation keeps the order stable while a visitor is browsing, while still being random across visitors.

### Project name length

There is no enforced maximum length on project names, but the display will truncate with an ellipsis beyond a set limit.

The limit may differ by context:

- **Listing page (card):** shorter limit — less space available per card.
- **Detail page (heading):** potentially longer — more space and prominence.

Exact limits are TBD.

### Detail page

- Detail pages show a selection of related projects at the bottom.
- Allows visitors to continue browsing without returning to the listing.

### Project content sections

Each project detail page is structured into three fixed sections. Berghs decided the headings are permanent — used across all years so they can display projects from previous festivals alongside current ones consistently.

| Section | Heading     |
| ------- | ----------- |
| 1       | The Company |
| 2       | Background  |
| 3       | Solution    |

**Editorial note:** Students must use these headings exactly as written. They are not editable.

**Text length constraints:**

Each section has a minimum and maximum character count, and there is a total cap across all three sections combined. These limits are a decision made for this project to keep detail pages consistent in length and readable without the need for editorial review of every submission.

| Section     | Min | Max |
| ----------- | --- | --- |
| The Company | TBD | TBD |
| Background  | TBD | TBD |
| Solution    | TBD | TBD |
| **Total**   | —   | TBD |

_Limits will be enforced in the content collection schema (Zod validation at build time)._

---

## Cross-page consistency pass (April 2026)

Several small, interlocking changes to make the experiences subpages read as part of the same family as the rest of the site, without altering the per-section layouts the user wanted preserved (Music as image-left rows, Food & Drink as a card grid, Installations as a wide gallery).

- **`.section-body` shared class.** Body prose styling (`font-size-base`, `line-height-base`, `max-width: prose-width`) was duplicated across the home and experience-section templates. Promoted to a single class in the `global` layer of `global.css` (per `css-rules.md` shared-classes rule). Used by the home content sections and by every experience section's intro block.
- **Installations row rhythm.** Installations previously stacked items with a large `--space-xl` gap and no dividers. Switched to the same top-border row pattern used by Music and the home schedule list (image-left at ≥30rem). Music and Food & Drink layouts kept unchanged.
- **Header text-transform consistency.** The desktop "Experiences" submenu toggle is a `<button>`, which doesn't reliably inherit `text-transform` from `.nav-links` across browsers — added `text-transform: inherit` on `.submenu-toggle` so it follows the nav's lowercase rule. Mobile menu links also gained the same lowercase / uppercase-on-`[aria-current="page"]` treatment as the desktop nav (was previously always bold, no case toggle).

---

## Experiences split + per-section item fields (April 2026)

The single Experiences page (one ACF page with a schedule repeater + a groups repeater) was split into four independent WP pages, one per section, each with its own page template and ACF field group. Replaces the previous `page-experiences.php` template + the `Page*Schedule` / `Page*Group` Astro templates that rendered all sections from one document.

Per-section item fields, all optional:

| Section       | name | image | description | day | start_time | location | url |
| ------------- | :--: | :---: | :---------: | :-: | :--------: | :------: | :-: |
| Music         | ✓    | ✓     | ✓           | ✓   | ✓          | ✓        | ✓   |
| Installations | ✓    | ✓     | ✓           | —   | —          | ✓        | ✓   |
| Food & drink  | ✓    | ✓     | ✓           | —   | —          | ✓        | ✓   |

- **`day`** (Music only) is an ACF Select. Choices are populated dynamically from the Schedule page's `schedule` repeater (`acf/load_field` filter), so the day choices stay in sync with the festival days — no parallel list to maintain. Music items with a known day are grouped under a day heading on the rendered page; items without a day, or with a day that no longer matches a schedule day, fall into a single trailing bucket.
- **`start_time`** (Music only) is plain text, matching the format used by `schedule.events.start_time`. Renders alongside `location` in the row meta line.
- **`location`** is an ACF Select with admin-managed choices (edit via Custom Fields → Field Groups). Used as a stage / venue / area label. Rendered on all three section pages.

---

## What was kept from previous years

- Eventbrite for registration.
- Two-day format: Friday evening festival + Saturday daytime exhibition.
- Sponsor logo bar on every page.
- Berghs branding in header, linking to berghs.se.
- Social links to LinkedIn, Instagram, Facebook.
