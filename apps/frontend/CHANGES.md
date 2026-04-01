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
