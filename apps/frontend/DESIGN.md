# Design System: Berghs Festival 2026

## 1. Visual Theme & Atmosphere

High-contrast, black-dominant aesthetic with raw, bold energy. The design language is rooted in Swiss-style graphic design: grid-driven layouts, tight typographic hierarchy, and no decorative elements. The mood is confrontational and industrial. White elements punch through a void-black canvas. Accent colors are pure, saturated neon used at full intensity — and the primary source of visual dynamism on every page load.

## 2. Color Palette & Roles

**Static colors:**

- `--color-bg: #000000` — all surfaces and containers
- `--color-text-on-bg: #ffffff` — all text, icons, and UI elements on black
- `--color-text: #000000` — text placed on top of accent fills
- `--color-border: #ffffff` — all dividers and borders

**Accent palette (defined in `accents.ts` and mirrored in `global.css`):**

| Token | Hex |
|---|---|
| `--color-accent-green` | `#00ff00` |
| `--color-accent-red` | `#ff0000` |
| `--color-accent-blue` | `#0037ff` |
| `--color-accent-yellow` | `#eeff00` |
| `--color-accent-pink` | `#ff00d9` |

Accents are never mixed, blended, or used as gradients. They appear as solid, flat blocks at full saturation. No opacity variations, no tints, no shades.

**How accents are assigned at runtime:**

Three CSS custom properties — `--color-accent-1` (primary), `--color-accent-2` (secondary), `--color-accent-3` (tertiary) — are set per page region by JavaScript. The `pickTriple()` function in `accents.ts` picks three distinct colors from the palette. `makeColorPicker(gap=2)` enforces that at least 2 different colors appear before any color can repeat, preventing repetitive sequences.

## 3. Interactive Accent Behavior

The accent system is the site's defining interactive feature. Everything below is implemented in `src/scripts/`:

**Logo (`header.ts`)**
- On page load: strobes through accent colors at 50ms intervals for 600ms, then resets to white.
- On hover: continuously cycles through accent colors at 50ms. Resets to white on mouse leave.

**Nav links (`header.ts`)**
- On hover: simultaneously cycles through 7 fonts (Inria Sans, Georgia, Impact, Courier New, Arial Black, Times New Roman, Verdana) AND accent colors, every 50ms. Resets to the default typeface and color on mouse leave.
- Each link's text span is pre-measured across all 7 fonts and locked to the widest rendering to prevent layout shift during cycling.

**Text selection (`global.css`)**
- `::selection` background continuously cycles through random accent colors every 150ms via dynamically injected `<style>` tags (required due to Safari's limitations with CSS custom properties on `::selection`).

**Text scramble (`src/scripts/scramble.ts`)**
- Elements marked with `data-scramble` get a reveal animation on page load: each character scrambles through block glyphs (`█░▓▒╳╬▪▫◆◇✕…`) at 40ms per frame for 8 frames before snapping to its final character. Characters are staggered 15ms apart, sorted top-to-bottom.
- After reveal, each scrambled element independently re-glitches one random word briefly (5 frames at 50ms) on a ~6 second idle timer with ±1.5s jitter.
- Implemented via a CSS `--scramble` custom property driving a `::before` overlay — the real text stays in the DOM invisible, holding its space. No reflow or layout shift.
- Fully respects `prefers-reduced-motion`: animations are skipped entirely, text is shown immediately.

## 4. Typography

- **Typeface:** Inter (weights 400 and 700), loaded from Google Fonts. Fallback: `system-ui, sans-serif`.
- No italics. No underlines. Hierarchy is achieved through size and weight contrast only.
- Two weights only: `--font-weight-normal: 400` and `--font-weight-bold: 700`.

**Type scale:**

| Token | Value | Usage |
|---|---|---|
| `--font-size-xs` | `1rem` | Labels, metadata |
| `--font-size-base` | `1.125rem` | Body text, UI copy |
| `--font-size-lg` | `clamp(2rem, 6vw, 2.75rem)` | Headings, schedule, nav |
| `--font-size-display` | `clamp(2.75rem, 9vw, 4.5rem)` | Page titles only |

**Letter spacing:**

- `--letter-spacing-label: 0.15em` — all-caps section labels
- `--letter-spacing-logo: 0.125em` — logo wordmark

**Line heights:**

- `--line-height-base: 1.4` — body text
- `--line-height-tight: 1.2` — headings

Note: Inria Sans, Georgia, Impact, Arial Black, and other fonts appear only in the nav-hover cycling effect (`header.ts`). They are not part of the type system — Inter is the only design typeface.

## 5. Layout & Spacing

**Container sizing:**

- `--max-width: 64rem` — max page container width
- `--prose-width: 40rem` — max content column width
- `--site-padding-inline: clamp(1.25rem, 5vw, 2.5rem)` — horizontal page padding
- `--section-label-width: 7rem` — width of the label column in content sections
- `--breakpoint-md: 48rem` — mobile/desktop breakpoint (header nav, hero grid)

**3-column content grid:**

Used consistently across all content sections (About, Manifest, Sponsors, Footer):

```
[7rem label] [gap] [content up to 40rem] [gap] [7rem spacer]
```

The left column holds the all-caps section label. The right column is an empty spacer for visual symmetry. Use CSS grid with `grid-template-columns: var(--section-label-width) 1fr` (or equivalent subgrid patterns where nesting applies).

**Hero:**

Full viewport minus fixed chrome: `height: calc(100dvh - var(--header-height) - var(--cta-height))`. Both `--header-height` and `--cta-height` are set by ResizeObserver in JavaScript.

**Fluid spacing scale:**

| Token | Value |
|---|---|
| `--space-xxs` | `0.25rem` |
| `--space-xs` | `0.5rem` |
| `--space-sm` | `1rem` |
| `--space-md` | `1.25rem` |
| `--space-lg` | `clamp(1.25rem, 4vmax, 2.5rem)` |
| `--space-xl` | `clamp(2.5rem, 8vmax, 6.25rem)` |

Use CSS grid for page-level and multi-column layout. Use flexbox for one-dimensional alignment.

## 6. Components

**Header**
- Height tracked via ResizeObserver → `--header-height` CSS custom property.
- Mobile (< 48rem): hamburger button, mobile menu slides down with `--color-accent-1` background fill.
- Desktop (≥ 48rem): horizontal nav, full link list visible.
- Active page link: uppercase + bold weight.

**Borders & dividers**
- `--border-default: 1px solid var(--color-border)` — used for all section dividers and element borders.
- `--color-border` is `#ffffff` — borders are always white.

**Buttons**
- Solid white rectangle, black text, no border-radius.

## 7. Do's and Don'ts

**Do:**
- Keep backgrounds black at all times.
- Use `--color-text-on-bg` (white) for all foreground on black.
- Use accent colors only as solid, flat blocks at full saturation via `--color-accent-1/2/3`.
- Follow the 3-column section grid pattern for all content sections.
- Use all-caps + `--letter-spacing-label` for section labels.
- Use fluid `clamp()` values through the spacing token system — never hardcode.
- Mark text that should animate on load with `data-scramble`.

**Don't:**
- Use gradients, shadows, glows, or any depth effects.
- Round any corners — no `border-radius` anywhere in the design.
- Mix accent colors within a single element.
- Use decorative imagery or illustrations (photography in hero/project contexts is fine).
- Add opacity or tints to accent colors.
- Hardcode spacing, color, or typography values — always use a token.
- Use more than two font weights (regular and bold).
