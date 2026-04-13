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

```css
/* Spacing */
--space-xxs: 0.25rem;
--space-xs:  0.5rem;
--space-sm:  1rem;
--space-md:  1.25rem;
--space-lg:  clamp(1.25rem, 4vmax, 2.5rem);
--space-xl:  clamp(2.5rem, 8vmax, 6.25rem);

/* Font sizes */
--font-size-xs:      0.875rem;   /* labels, metadata, timestamps */
--font-size-base:    1.125rem;   /* body text, UI copy */
--font-size-lg:      clamp(2rem, 5vw, 2.75rem);   /* nav, card titles, section headings */
--font-size-display: clamp(2.75rem, 8vw, 4.5rem); /* page titles only */

/* Layout */
--max-width:           64rem;
--site-padding-inline: clamp(1.25rem, 5vw, 2.5rem);

/* Borders */
--border-default: 1px solid var(--color-border);

/* Accent colors */
--color-accent-green:  #00ff00;
--color-accent-red:    #ff0000;
--color-accent-blue:   #1e00ff;
--color-accent-yellow: #eeff00;

/* Label tracking */
--letter-spacing-label: 0.15em;
```

No border-radius tokens exist — the design uses sharp corners everywhere.

If you need a value that no token covers, add the token to `global.css` first, then use it. Do not add one-off values.

---

## No magic numbers

Every spacing, sizing, or colour value in a `<style>` block must come from a token. The only exceptions are:

- `0`, `100%`, `100dvh` — structural/reset values
- `aspect-ratio` values (`3/2`, `16/9`) — intrinsic content ratios
- `flex: 1`, `flex-shrink: 0` — layout mechanics
- `line-height` overrides when deliberately differing from `--line-height-base` (e.g. `1.2` on a large heading)
- `opacity` values for hover/muted states (no opacity token system exists)
- Pixel-precise CSS tricks like `margin-bottom: -1px` for border overlap or `0.5px` hairline borders — document why
- Component-specific fixed dimensions like image heights (`140px`, `200px`) — these are content constraints, not spacing

When in doubt: if the value feels arbitrary, it should be a token.

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

The responsive grid pattern used throughout this project — no media queries needed:

```css
grid-template-columns: repeat(auto-fill, minmax(min(100%, VAR), 1fr));
```

`auto-fill` keeps empty columns. `auto-fit` collapses them. Prefer `auto-fill` when the number of items is fixed, `auto-fit` when it varies.

For stacking elements without `position: absolute`, use the grid-as-canvas pattern:

```css
.wrapper { display: grid; }
.wrapper > * { grid-area: 1 / 1; }
```

---

## Responsive design

Use `clamp()` for fluid values rather than breakpoints wherever possible. The token system already uses this for spacing and font sizes.

For hover/pointer interactions, gate them behind a capability media query so touch devices don't get stuck states:

```css
@media (any-hover: hover) and (any-pointer: fine) {
    .thing:hover { opacity: 0.7; }
}
```

---

## Accessibility

Touch targets must be at least 44px. Use `max(44px, 2em)` so they also scale with font size. See the hamburger button in `Header.astro` for the reference implementation.

The global reset already handles reduced-motion:

```css
@media (prefers-reduced-motion: reduce) {
    *, *::before, *::after {
        animation-duration: 0.01ms !important;
        transition-duration: 0.01ms !important;
    }
}
```

Use `0.01ms` not `0` — animations still complete rather than freezing on the wrong frame.

Focus styles are defined globally in `global.css`. Do not override them without a good reason.

---

## Colours

Colours are defined as tokens using `oklch()` for base values and `rgb(from ...)` relative syntax for derived values:

```css
--color-border: rgb(from var(--color-primary) r g b / 0.15);
--color-text-secondary: rgb(from var(--color-text) r g b / 0.55);
```

Use relative colour syntax to derive variants from a base token rather than hardcoding separate values. This keeps the colour system coherent when the base changes.

---

## What not to do

- Do not put component styles in `global.css`
- Do not hardcode spacing, colour, or typography values — add a token
- Do not use `position: absolute` to achieve layouts that CSS grid can handle
- Do not add redundant `margin: 0; padding: 0` resets — the global reset already handles them
- Do not duplicate style blocks across templates — extract a shared class or a component
- Do not write styles for states that cannot happen
