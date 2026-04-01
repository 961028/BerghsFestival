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
  - `40-page-experiences.php` — Experiences page template (groups)
- When adding or modifying content collection schemas in `src/content.config.ts`, read the corresponding ACF file to verify field names and types.

## Frontend (Astro)

- **Content collections** in `src/content.config.ts` define schemas (with Zod) that map WP REST API responses to typed data. Helpers in `src/lib/` handle fetching (`wp-api.ts`), HTML stripping (`html.ts`), and collection utilities (`content.ts`).
- **Layouts** (`src/layouts/`) — `Layout.astro` is the main shell (header, main, footer).
- **Templates** (`src/templates/`) — page-specific layouts dispatched by `PageTemplate.astro`. `Page.astro` is the generic page template; `PageExperiences.astro` handles the experiences page (derives tabs from groups).
- **Pages** — `src/pages/[...link].astro` handles WP pages dynamically. `src/pages/projects/index.astro` and `[slug].astro` handle the project listing and detail pages.
- **Components** — `src/components/site/` for site-wide pieces (Header, Sponsors, Contact, Iq), `src/components/elements/` for reusable primitives (Svg, WpImage, WpVideo, ProjectCard).

## Styling

- **Design tokens** (colors, spacing, typography, borders) live as CSS custom properties in `src/styles/global.css`. Only tokens and true base styles (box-sizing, body font) belong there.
- **All other CSS** goes in component `<style>` blocks. Astro scopes these to the component automatically, so styles never leak out. Use design tokens from `global.css` via `var(--token-name)`.

## Commands

```sh
npm run dev      # Start dev server
npm run build    # Production build
```
