# Berghs Festival 2026 — Frontend

## Architecture

Headless WordPress (with ACF) serves content via REST API. Astro fetches this data at build time through content collections (`src/content.config.ts`) and generates a static site.

## Backend (WordPress)

The backend lives at `../backend/` relative to this directory (absolute: `apps/backend/`).

- **Advanced Custom Fields (ACF)** defines the content model — field groups, repeaters, options pages.
- ACF field definitions are in `../backend/theme/includes/` (numbered for load order):
  - `0-acf.php` — ACF helper functions
  - `10-menus.php` — Menu registrations (`primary`, `footer`)
  - `20-contact.php` — Contact options (address, phone, social)
  - `20-iq.php` — IQ section (title, content)
  - `20-sponsors.php` — Sponsors repeater (name, image, url)
  - `30-projects.php` — Project post type and fields
  - `40-page-happenings.php` — Happenings page template (schedule, groups)
  - `40-page-installations.php` — Installations page template (groups)
- When adding or modifying content collection schemas in `src/content.config.ts`, read the corresponding ACF file to verify field names and types.

## Frontend (Astro)

- **Content collections** in `src/content.config.ts` define schemas (with Zod) that map WP REST API responses to typed data.
- **Layouts** (`src/layouts/`) — `Layout.astro` is the main shell (header, main, footer).
- **Templates** (`src/templates/`) — page-specific layouts dispatched by `PageTemplate.astro`.
- **Components** — `src/components/site/` for site-wide pieces (Header, Sponsors, Contact, Iq), `src/components/elements/` for reusable primitives (Svg, WpImage, WpVideo).

## Design System

All visual tokens are CSS custom properties in `src/styles/global.css`. Change colors, spacing, typography, or borders there and the entire site updates.

## Commands

```sh
npm run dev      # Start dev server
npm run build    # Production build
```
