# CSS Rules — Berghs Festival Frontend

This document describes the conventions for writing CSS in this project. Follow these rules when adding styles to any file.

---

## Where CSS lives

There are exactly two places CSS belongs:

**`src/styles/global.css`** — design tokens, reset, and base styles that apply to the whole site. Nothing else. If you find yourself wanting to put a component style here, put it in the component instead.

**Component `<style>` blocks** — every other style goes in the component that uses it. Astro scopes these automatically. No styles leak between components.

No external CSS files. No utility frameworks. No preprocessors.

---

## Layer order

`global.css` declares the layer order at the top:

```css
@layer reset, theme, global;
```

- `reset` — box-sizing, margin/padding resets, reduced-motion
- `theme` — all design tokens as custom properties on `:root`
- `global` — site-wide base styles (body, anchors, focus, shared classes like `.page-title`)

Component `<style>` blocks are un-layered, so they always win over the layered global styles regardless of specificity. This is intentional.

---

## Design tokens

All design tokens are CSS custom properties defined in the `theme` layer of `global.css`. Use them everywhere. Never hardcode a value that has a corresponding token.

No border-radius tokens exist — the design uses sharp corners everywhere.

If you need a value that no token covers, add the token to `global.css` first, then use it. Do not add one-off values.

---

## No magic numbers

Every spacing, sizing, typographic, or colour value in a `<style>` block must come from a token. This includes `letter-spacing`, `font-size`, `font-weight`, `color`, `border-*`, and the spacing scale. The only exceptions are:

- `0`, `100%`, `100dvh` — structural/reset values
- `aspect-ratio` values (`3/2`, `16/9`) — intrinsic content ratios
- `flex: 1`, `flex-shrink: 0` — layout mechanics
- `line-height` overrides when deliberately differing from `--line-height-base` (e.g. `1.2` on a large heading)
- `opacity` values for hover/muted states (no opacity token system exists)
- Pixel-precise CSS tricks like `margin-bottom: -1px` for border overlap or `0.5px` hairline borders — document why
- Component-specific fixed dimensions like image heights (`140px`, `200px`) — these are content constraints, not spacing
- Semantic keywords like `inherit`, `currentColor`, `transparent`

When in doubt: if the value feels arbitrary, it should be a token.

`npm run lint:css` enforces the rule mechanically for typography (`letter-spacing`, `font-size`, `font-weight`, `line-height`), spacing (all `margin-*`, `padding-*`, `gap`, `column-gap`, `row-gap`), and color (`color`, `background-color`, `background`, `border-color`, `border`). The check runs only inside `<style>` blocks and skips CSS comments. Extend [`scripts/check-css-tokens.mjs`](scripts/check-css-tokens.mjs) when adding new properties to the rule.

---

## Typography — role → token table

Letter-spacing follows the type role, not the size in isolation. Tracking scales inversely with size, and uppercase always needs more tracking than mixed-case at the same size.

| Role | Selector traits | `font-size` | `font-weight` | `text-transform` | `letter-spacing` |
|------|----------------|-------------|---------------|------------------|------------------|
| Display heading | big bold headline | `display` | `bold` | `uppercase` (optional) | `--letter-spacing-caps-lg` if uppercase, else `--letter-spacing-display` |
| Large heading | section/card/item title | `lg` | `bold` | none | `--letter-spacing-display` |
| Large uppercase | CTA button, filter button, nav link | `lg` | `bold` (usually) | `uppercase` | `--letter-spacing-caps-lg` |
| Small uppercase label | eyebrow, meta label, footer label | `sm` / `base` | `bold` | `uppercase` | `--letter-spacing-caps-sm` |
| Body / running text | paragraphs, list items, descriptions | `sm` / `base` / `lg` | `normal` / `light` | none | none (omit the property) |

**Never** apply `--letter-spacing-display` (negative tracking) to body, small, or light-weight text — it reduces legibility.

`npm run lint:css` enforces this mechanically for uppercase + large headings (see [`scripts/check-css-tokens.mjs`](scripts/check-css-tokens.mjs)).

---

## Shared classes

Some styles are semantic patterns used across multiple pages and belong in `global.css` rather than any single component:

- `.page-title` — the `h1` on every page. Defined in the `global` layer, used in `Page.astro`, `PageAbout.astro`, `PageExperiences.astro`, and `[slug].astro`.

If you find yourself duplicating a style block across templates, it probably belongs in `global.css`.

---

## Component scope

Astro scopes `<style>` blocks to the component automatically. This means:

- Class names only need to be unique within the component, not globally
- You do not need BEM or other namespacing conventions
- To style a child element rendered by a sub-component (e.g. an `<img>` inside `WpImage`), use `:global()`: `.thumbnail :global(img) { ... }`. Use this sparingly — it means the child component isn't managing its own presentation

---

## Layout

Use CSS grid for page-level layout and multi-column lists. Use flexbox for one-dimensional alignment (nav bars, button groups, icon+label pairs).

The intrinsic responsive grid pattern used throughout this project — no breakpoints needed:

```css
grid-template-columns: repeat(auto-fit, minmax(min(100%, VAR), 1fr));
```

`auto-fit` collapses empty columns. `auto-fill` keeps them. Prefer `auto-fit` when the number of items varies, `auto-fill` when it is fixed.

For stacking elements without `position: absolute`, use the grid-as-canvas pattern:

```css
.wrapper {
    display: grid;
}
.wrapper > * {
    grid-area: 1 / 1;
}
```

### Gap-as-border

To draw dividers between grid cells that are always correct regardless of layout direction (1-column vs 2-column), use the gap-as-border technique instead of conditional `border` properties:

```css
.grid-container {
    display: grid;
    gap: 1px;
    background: var(--color-paper); /* exposed in the gap */
}

.grid-item {
    background: var(--color-ink); /* punches out the container color */
}
```

The `1px` gap exposes the container's background as a visible divider. No border is ever outside the grid — only between cells. No breakpoints or direction-switching needed. Used in `.footer-row--info` and `.schedule-days`.

---

## Responsive design

Follow this priority order — reach for the next level only when the previous cannot express the intent:

1. **Intrinsic layout** — `auto-fit`/`auto-fill` grids, `flex-wrap`, `min-content`/`max-content`. No threshold needed; the content itself determines when layout changes.
2. **Container queries** — `@container (min-width: X)` when a component's layout should respond to its own available width, not the viewport. Declare `container-type: inline-size` on the parent. Used for `.schedule-days`, `.hero-footer`, `.section-label`, `.manifest`, `.about`.
3. **Viewport media queries** — `@media` only for UI that is genuinely viewport-driven: show/hide hamburger, sticky nav stacking. Not for content layout.

Use `clamp()` for fluid values (spacing, type sizes) — the token system already does this.

For hover/pointer interactions, always gate behind a capability query so touch devices don't get stuck states:

```css
@media (any-hover: hover) and (any-pointer: fine) {
    .thing:hover {
        opacity: 0.7;
    }
}
```

---

## Accessibility

Touch targets must be at least 44px. Use `max(44px, 2em)` so they also scale with font size. See the hamburger button in `Header.astro` for the reference implementation.

The global reset already handles reduced-motion:

```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

Use `0.01ms` not `0` — animations still complete rather than freezing on the wrong frame.

Focus styles are defined globally in `global.css`. Do not override them without a good reason.

---

## Colours

Colours are static tokens defined in the `theme` layer of `global.css`. The design uses a fixed black/white palette — no derived or semi-transparent variants.

Two static tokens — `--color-ink` (`#000000`) and `--color-paper` (`#ffffff`) — name the paint, not a role. Either may serve as background or as text/border depending on context.

Borders use `--border: 1px solid currentColor`. Borders inherit their colour from the element's `color` property, so the page-default border is white (body `color: var(--color-paper)`) and components that override `color` (e.g. CTA banner with `color: var(--color-ink)`) get a black border without a separate token.

---

## What not to do

- Do not put component styles in `global.css`
- Do not hardcode spacing, colour, or typography values — add a token
- Do not use `position: absolute` to achieve layouts that CSS grid can handle
- Do not add redundant `margin: 0; padding: 0` resets — the global reset already handles them
- Do not duplicate style blocks across templates — extract a shared class or a component
- Do not write styles for states that cannot happen
