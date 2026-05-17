# Design System: Berghs Festival 2026

## 1. Visual Theme & Atmosphere

High-contrast, black-dominant aesthetic with raw, bold energy. The design language is rooted in Swiss-style graphic design: grid-driven layouts, tight typographic hierarchy, and no decorative elements. The mood is confrontational and industrial. White elements punch through a void-black canvas. Accent colors are pure, saturated neon used at full intensity — and the primary source of visual dynamism on every page load.

## 2. Color Palette & Roles

**Static colors:**

- `--color-ink: #000000` — black; used as page background and as text on light/accent fills
- `--color-paper: #ffffff` — white; used as text/borders on black and as inverted surface fills

The two static colors describe ink vs. paper, not foreground vs. background — either may serve as the surface or the mark depending on context (e.g. white CTA hover state uses `paper` as background and `ink` as text).

**Accent palette (single source of truth in `accents.ts`):**

| Hex | Name |
|---|---|
| `#00ff00` | green |
| `#ff0000` | red |
| `#00ffff` | cyan |
| `#eeff00` | yellow |
| `#ff00d9` | pink |

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
| `--font-size-sm` | `1rem` | Labels, metadata |
| `--font-size-base` | `1.125rem` | Body text, UI copy |
| `--font-size-lg` | `clamp(2rem, 6vw, 2.75rem)` | Headings, schedule, nav |
| `--font-size-display` | `clamp(2.75rem, 9vw, 4.5rem)` | Page titles only |

**Letter spacing:**

- `--letter-spacing-label: 0.15em` — all-caps section labels and small uppercase metadata lines
- `--letter-spacing-tight: -0.03em` — large bold display text at `--font-size-lg` (schedule headlines, item names across experiences subpages)

**Line heights:**

- `--line-height-base: 1.5` — body text
- `--line-height-tight: 1.2` — headings

Note: Inria Sans, Georgia, Impact, Arial Black, and other fonts appear only in the nav-hover cycling effect (`header.ts`). They are not part of the type system — Inter is the only design typeface.

## 5. Layout & Spacing

**Container sizing:**

- `--max-width: 64rem` — max page container width
- `--prose-width: 40rem` — max content column width
- `--site-padding-inline: clamp(1.25rem, 5vw, 2.5rem)` — horizontal page padding
- `--section-label-width: 7rem` — width of the label column in content sections

**Content section grid:**

Content sections (About, Manifest) use a 2-column grid with an all-caps label on the left and prose on the right:

```
[auto label] [gap] [content up to 40rem]
```

`grid-template-columns: auto 1fr` — the label column sizes to its content. This layout activates via container query when `main` is at least `40rem` wide; below that it stacks single-column with the label above.

**Sponsors row:**

`grid-template-columns: var(--section-label-width) 1fr` — fixed-width label column, sponsors fill the rest. Always 2 columns, no breakpoint.

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

Layout responsiveness follows a strict priority: intrinsic (auto-fit/wrap) first, container queries second, viewport media queries only for genuinely viewport-driven UI (hamburger, sticky nav). See `css-rules.md` for the full hierarchy.

Dividers between grid cells use the gap-as-border technique: `gap: 1px` on the container with `background: var(--color-paper)`, and `background: var(--color-ink)` on the cells. This ensures dividers are always between cells and never on the outside edges, regardless of whether the layout is 1-column or 2-column.

## 6. Components

**Header**
- Height tracked via ResizeObserver → `--header-height` CSS custom property.
- Mobile (< 48rem): hamburger button, mobile menu slides down with `--color-accent-1` background fill.
- Desktop (≥ 48rem): horizontal nav, full link list visible.
- Active page link: uppercase + bold weight.

**Borders & dividers**
- `--border: 1px solid currentColor` — used for all section dividers and element borders. Inherits the element's text color, so a default page-context border is white (body color = `--color-paper`) while components that override `color` (e.g. CTA banner with `color: var(--color-ink)`) get a black border automatically.

**Buttons**
- Solid white rectangle, black text, no border-radius.

## 7. Do's and Don'ts

**Do:**
- Keep backgrounds black at all times.
- Use `--color-paper` (white) for all foreground on black, and `--color-ink` (black) for foreground on accent fills or inverted surfaces.
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
