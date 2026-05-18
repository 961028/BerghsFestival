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
    - `40-page-schedule.php` — Schedule page template (locations repeater + day/event repeater). Locations defined here are the single source of truth for the Location dropdowns on Music artists, Installations, and Food & drink — pulled via `app_acf_load_schedule_location_choices` (helper: `app_acf_get_schedule_location_choices`). Schedule events have a free-form `link_url` text field (relative paths like `/music` or absolute URLs) — events no longer reference Music artists.
    - `40-page-experience-music.php` — Music page template. Owns an `artists` repeater (name, image, description, url, social_url, shows). Each artist has a nested `shows` repeater (day, start_time, end_time, location). Day and location selects are populated dynamically from the Schedule page. Note: `app_acf_get_schedule_location_choices()` uses raw `get_post_meta` instead of `get_field()` to avoid circular filter recursion.
    - `40-page-experience-installations.php` — Installations page template (items repeater with name, location, image, description, url). Location choices come from the Schedule page.
    - `40-page-experience-food.php` — Food & drink page template (items repeater with name, location, image, description, url). Location choices come from the Schedule page.
- WordPress page templates (in `../backend/theme/`) register named templates for the WP admin. Current templates:
    - `page-about-berghs.php` — About page
    - `page-schedule.php` — Schedule page
    - `page-experience-music.php` — Music page
    - `page-experience-installations.php` — Installations page
    - `page-experience-food.php` — Food & drink page
    - `page-projects.php` — Projects listing page (no ACF fields; the standard WP editor content is used as the page description above the grid)
- When adding or modifying content collection schemas in `src/content.config.ts`, read the corresponding ACF file to verify field names and types.

## Frontend (Astro)

- **Content collections** in `src/content.config.ts` define schemas (with Zod) that map WP REST API responses to typed data. Helpers in `src/lib/` handle fetching (`wp-api.ts`), HTML stripping (`html.ts`), collection utilities (`content.ts`), and the per-experience-page schemas + page-load helpers (`experience-pages.ts`).
- **Layouts** (`src/layouts/`) — `Layout.astro` is the main shell (header, main, footer).
- **Templates** (`src/templates/`) — page-specific layouts dispatched by `PageTemplate.astro`. `Page.astro` is the generic page template; `PageAboutBerghs.astro` handles the About page (hardcoded institutional content); `PageSchedule.astro`, `PageExperienceMusic.astro`, `PageExperienceInstallations.astro`, and `PageExperienceFood.astro` render the four experience-section pages, each backed by its own WP page template. `PageProjects.astro` renders the projects listing — a real WP page (Projects template) with the page editor content as its description and the project CPT items rendered as a filterable grid below.
- **Pages**:
    - `src/pages/[...link].astro` handles all WP pages dynamically. Each section page is a regular WP page using its own template (`page-schedule.php`, `page-experience-music.php`, `page-projects.php`, etc.) and reaches the right `Page*.astro` template through `PageTemplate.astro`'s `SLUG_TO_TEMPLATE` map. Section URLs (`/schedule`, `/music`, `/installations`, `/food-drink`, `/projects`) come from each WP page's slug — there is no separate `[section].astro` route or `/experiences` hub.
    - `src/pages/projects/[slug].astro` handles the project detail pages. The listing at `/projects/` is served by the WP Projects page through `[...link].astro` → `PageProjects.astro` (not a hardcoded Astro route).
- **Components** — `src/components/site/` for site-wide pieces (Header, Sponsors, Contact, Iq, CtaBanner), `src/components/elements/` for reusable primitives (Svg, WpImage, WpVideo, ProjectCard), `src/components/experiences/` for experience-section list components (MusicList, InstallationsList). The food page renders its grid inline in `PageExperienceFood.astro`; the schedule day-toggle UI lives inline in `PageSchedule.astro`.

## Styling

- **Design tokens** (colors, spacing, typography, borders) live as CSS custom properties in `src/styles/global.css`. Only tokens and true base styles (box-sizing, body font) belong there.
- **All other CSS** goes in component `<style>` blocks. Astro scopes these to the component automatically, so styles never leak out. Use design tokens from `global.css` via `var(--token-name)`.
- **CSS rules and conventions** are documented in [`css-rules.md`](css-rules.md). Read it before writing any CSS.
- **Design system** (visual theme, color palette, typography, component styling, spacing) is documented in [`DESIGN.md`](DESIGN.md). Read it before making any visual or styling decisions.
- **Token enforcement** — `npm run lint:css` runs [`scripts/check-css-tokens.mjs`](scripts/check-css-tokens.mjs), which fails if any component `<style>` block uses a hardcoded value for a tokenised property. Covers typography (`letter-spacing`, `font-size`, `font-weight`, `line-height`), spacing (all `margin-*`, `padding-*`, `gap`, `column-gap`, `row-gap`), and color (`color`, `background-color`, `background`, `border-color`, `border`). Scoped to `<style>` blocks; skips CSS comments and `<script>` tags. Extend the script when adding new properties to the rule.

## Commands

Dev runs in two parts: Docker (Apache + WP + MySQL) and Astro dev (port 4321). Both must be cleanly stopped before restarting, or stale processes hold port 4321 and the proxy returns 503.

Devcontainer: `app/public/.devcontainer/` (the outer `berghsfestivalen/.devcontainer/` is stale — ignore).

```sh
# Stop previous session
cd "/Users/emilholmsten/Local Sites/berghsfestivalen/app/public/.devcontainer" && \
    docker compose -f docker-compose.yml -f docker-compose.override.yml down
lsof -ti:4321 | xargs -r kill -9

# Start Docker
cd "/Users/emilholmsten/Local Sites/berghsfestivalen/app/public/.devcontainer" && \
    docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

# Start Astro dev (in this directory)
npm run dev
npm run build
npm run lint:css
```

Site: http://berghs-festival-2026.test/ (WP admin at `/wp/wp-admin/`). DB volume: `berghs-festival-2026_db_data`, backup at `~/berghs-db-backup/db.tar.gz`.

## End of session

At the end of every session:

1. Update `berghs-festival-2026-ia.md` with any notable design decisions, content decisions, or UX choices made during the session.
2. Review `CLAUDE.md` and update it to reflect any new files, templates, patterns, or conventions introduced during the session.
