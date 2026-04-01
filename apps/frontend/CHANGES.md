# Berghs Festival 2026 — Changes

This document tracks notable changes made to the site, organized by concern. It is intended as a portfolio reference and should be readable by recruiters, developers, and a general creative audience alike.

---

## How to use this document

Add an entry each time you make a meaningful change — a new feature, a design decision, a content update, or a backend/architecture choice worth explaining.

**When to add an entry:**
- A new page, component, or template is created
- A deliberate design or UX decision is made
- Content is added or restructured in a non-trivial way
- An architectural or technical pattern is introduced or changed

**Each entry should include:**
- **A clear heading** — name the feature or change
- **What was done** — one or two plain-language sentences anyone can follow
- **Why** — the problem it solves or the decision behind it
- **Technical notes** *(optional)* — implementation detail for a developer audience

**Tone:** Write as if explaining your work in a portfolio or job interview. Clear, honest, and specific. Avoid jargon where plain language works just as well.

---

## Content

### Projects page

A projects listing page was added with a fluid card grid, filter controls, and individual detail pages. Each card shows a 3:2 thumbnail, client name, project title, and team member names with programme. Filtering by All / Group / Individual is handled client-side by toggling visibility. Detail pages show a selection of related projects at the bottom.

**Why:** Graduation projects are a central part of the festival — visitors need to browse them quickly and dive into individual work without friction.

**Technical notes:** The listing is at `src/pages/projects/index.astro`, detail pages at `src/pages/projects/[slug].astro`. The `ProjectCard` component handles both contexts. Grid layout uses `auto-fit` with `minmax(min(100%, 20rem), 1fr)` for fluid column count. Thumbnail aspect ratio is 3:2.

### Experiences page

The Experiences page was built as a tabbed layout driven by ACF field groups. Each group (e.g. Installations, Food & drink) becomes a tab; switching tabs shows only that group's items. A schedule section above the tabs lists timed events by day.

**Why:** The experiences content is heterogeneous — grouping it into named tabs avoids a long, hard-to-scan single-column page while keeping everything on one URL.

**Technical notes:** Tabs are implemented with `role="tab"` / `aria-selected` and client-side JS in `src/templates/PageExperiences.astro`. Group slugs are derived from titles at parse time. Schedule and groups are parsed from ACF fields using Zod schemas defined in the same file.

### About Berghs — institutional intro

Four sections introducing Berghs School of Communication were added to the About page: Power of creativity, Action-based learning, Perspective, and Applied learning and amplified intelligence. A link to berghs.se closes the section.

**Why:** The About page needed a concise institutional description to give festival visitors context on who Berghs is and what the school stands for.

**Technical notes:** Content is hardcoded in `src/templates/PageAbout.astro` rather than pulled from WordPress, since it is stable institutional copy that does not need editorial control.

---

## Design

---

## Architecture

### Dedicated page templates per WordPress template

Page-specific layouts (e.g. About, Experiences) are implemented as separate Astro template files (`src/templates/`) and dispatched from `src/components/PageTemplate.astro` based on the WordPress page template slug. The generic `Page.astro` handles all pages that have no custom template assigned.

**Why:** Keeps page-specific markup and logic isolated without polluting the generic template with conditionals.

**Technical notes:** To add a new page template, create a PHP file in `apps/backend/theme/` with a `Template Name:` comment, create the corresponding `.astro` file in `src/templates/`, and register the mapping in `PageTemplate.astro`.

---

## Backend
