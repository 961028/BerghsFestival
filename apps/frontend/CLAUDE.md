# Berghs Festival 2026 — Frontend

## Communication style

Output: terse & exact, !filler
Fragments: ok
Bad ideas: flag
Em dashes: null
Pattern: [thing] → [action] → [reason] → [next]
Drift: !allowed
Format:

key: value (aligned)
code/literals: backticks
compare: table or columns
structure: predictable, scannable
prose: only when structure fails

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
- WordPress page templates (in `../backend/theme/`) register named templates for the WP admin. Current templates:
    - `page-about.php` — About page
    - `page-experiences.php` — Experiences page (rendered on the frontend by the dynamic `/experiences/[slug]` route, not by a single Astro template)
- When adding or modifying content collection schemas in `src/content.config.ts`, read the corresponding ACF file to verify field names and types.

## Frontend (Astro)

- **Content collections** in `src/content.config.ts` define schemas (with Zod) that map WP REST API responses to typed data. Helpers in `src/lib/` handle fetching (`wp-api.ts`), HTML stripping (`html.ts`), collection utilities (`content.ts`), and the experiences page schemas + page-load helper (`experiences.ts`).
- **Layouts** (`src/layouts/`) — `Layout.astro` is the main shell (header, main, footer).
- **Templates** (`src/templates/`) — page-specific layouts dispatched by `PageTemplate.astro`. `Page.astro` is the generic page template; `PageAbout.astro` handles the About page (hardcoded institutional content); `ExperiencesSchedule.astro` and `ExperiencesGroup.astro` render one section of the experiences page each (schedule, or one WP-defined group).
- **Pages** — `src/pages/[...link].astro` handles WP pages dynamically (excluding `page-experiences.php`). `src/pages/experiences/[slug].astro` is a dynamic route that emits one path per experiences section (`schedule` plus each ACF group slug); `src/pages/experiences/index.astro` is a static `Astro.redirect` to `/experiences/schedule`. `src/pages/projects/index.astro` and `[slug].astro` handle the project listing and detail pages.
- **Components** — `src/components/site/` for site-wide pieces (Header, Sponsors, Contact, Iq), `src/components/elements/` for reusable primitives (Svg, WpImage, WpVideo, ProjectCard), `src/components/experiences/` for experiences-specific list components (MusicList, InstallationsList).

## Styling

- **Design tokens** (colors, spacing, typography, borders) live as CSS custom properties in `src/styles/global.css`. Only tokens and true base styles (box-sizing, body font) belong there.
- **All other CSS** goes in component `<style>` blocks. Astro scopes these to the component automatically, so styles never leak out. Use design tokens from `global.css` via `var(--token-name)`.
- **CSS rules and conventions** are documented in [`css-rules.md`](css-rules.md). Read it before writing any CSS.
- **Design system** (visual theme, color palette, typography, component styling, spacing) is documented in [`DESIGN.md`](DESIGN.md). Read it before making any visual or styling decisions.
- **Token enforcement** — `npm run lint:css` runs [`scripts/check-css-tokens.mjs`](scripts/check-css-tokens.mjs), which fails if any component `<style>` block uses a hardcoded value for a tokenised property (currently `letter-spacing`, `font-size`, `font-weight`). Extend the script when adding new properties to the rule.

## Commands

```sh
.devcontainer % docker compose up -d
npm run dev      # Start dev server
npm run build    # Production build
npm run lint:css # Check that styles only use design tokens
```

## End of session

At the end of every session:

1. Update `berghs-festival-2026-ia.md` with any notable design decisions, content decisions, or UX choices made during the session.
2. Review `CLAUDE.md` and update it to reflect any new files, templates, patterns, or conventions introduced during the session.
3. Ask the user if they want to commit. If yes, stage the relevant files and commit with a descriptive commit message reflecting the work done.
