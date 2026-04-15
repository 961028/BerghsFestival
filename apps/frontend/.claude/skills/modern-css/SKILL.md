---
name: modern-css
description: How to write modern, idiomatic CSS. Use this skill whenever generating CSS for any purpose — styling components, building layouts, theming, form controls, responsive design, animations, or accessibility. Also use when reviewing or refactoring existing CSS, answering CSS questions, or when any other skill (frontend-design, docx, etc.) involves CSS output. This skill should trigger for any task where CSS is part of the deliverable, even if CSS is not the primary focus.
---

# Modern CSS

This skill defines how to write CSS. Follow these rules whenever generating, reviewing, or refactoring CSS. The techniques here reflect the state of browser support as of early 2026 and favor platform-native solutions over JavaScript or preprocessor workarounds.

## Project architecture

### Cascade layers

Declare layer order at the top of every stylesheet. The first-declared layer has the lowest priority; the last has the highest. Un-layered styles beat all layered styles.

```css
@layer reset, theme, global, layout, components, utilities, states;
```

Import third-party CSS into its own layer so your component styles always win regardless of specificity:

```css
@layer vendor {
    @import url("bootstrap.css");
}
```

`@supports` cannot detect at-rules like `@layer`. If you need feature detection for layers, use the SupportsCSS script at supportscss.dev.

### Custom properties in the theme layer

Define all design tokens on `:root` inside `@layer theme`. Set `color-scheme` and `accent-color` here too.

```css
@layer theme {
    :root {
        --color-primary: oklch(0.45 0.2 265);
        --color-surface: oklch(0.98 0.01 265);
        --radius-md: 0.5rem;
        --space-md: clamp(1rem, 4vmax, 2rem);
    }
    html {
        color-scheme: light;
        accent-color: var(--color-primary);
    }
}
```

`accent-color` instantly themes checkboxes, radio buttons, range inputs, and progress bars. Often this is sufficient without fully restyling those controls.

### CSS nesting

Use native CSS nesting. Do not use Sass or other preprocessors solely for nesting — browser support is above 91%.

Syntax rules:

- Pseudo-classes and pseudo-elements require `&`: `&:hover`, `&::before`.
- Descendant selectors like `.child` or `[data-x]` work without `&`.
- `[data-type] { }` creates a descendant selector. `&[data-type] { }` creates a compound selector appended to the parent.

Keep nesting shallow and component-scoped. Deep nesting creates specificity problems and makes code harder to read.

---

## Layout

### The responsive grid pattern

This is the single most useful layout pattern in CSS. Memorize it.

```css
.grid {
    --grid-min: 30ch;
    display: grid;
    grid-template-columns: repeat(
        auto-fit,
        minmax(min(100%, var(--grid-min)), 1fr)
    );
    gap: var(--grid-gap, 3vw);
}
```

`auto-fit` creates as many columns as fit. `minmax()` sets each column's range. The nested `min(100%, ...)` prevents overflow in narrow containers. The custom property `--grid-min` can be overridden per-instance via inline styles. No media queries needed.

### The flexbox wrap pattern

```css
.flex-grid {
    --flex-min: 20rem;
    display: flex;
    flex-wrap: wrap;
    gap: var(--flex-gap, 3vmax);
}
.flex-grid > * {
    flex: 1 1 var(--flex-min);
}
```

Key difference from grid: flex children grow into unused space. A 3-item layout with 2 columns leaves the third item filling the entire second row. Grid children stay constrained to their implicit column widths.

### Sticky footer with grid

```css
body {
    display: grid;
    grid-template-rows: auto 1fr auto;
    min-height: 100vh;
}
```

The `1fr` on `main` stretches to fill remaining space. This is cleaner than the flexbox approach where `margin-top: auto` on the footer pushes it down without expanding `main`.

### Grid-as-canvas (stacking without absolute positioning)

Assign the same grid area to multiple children and they stack in a single cell:

```css
.hero {
    display: grid;
    grid-template-areas: "stack";
}
.hero > * {
    grid-area: stack;
}
```

Use this instead of `position: absolute` for overlapping elements like hero sections, image captions, and form controls with overlaid icons. It eliminates percentage math and media query headaches.

### The modern container class

```css
.container {
    width: min(80ch, 100vw - 2rem);
    margin-inline: auto;
}
```

This caps width at `80ch`, builds in side padding via the subtracted `2rem`, and centers the element. No `max-width` needed.

---

## Container queries

Use container queries for component-level responsiveness. Prefer them over media queries when a component's layout should respond to its parent's size rather than the viewport.

```css
.sidebar {
    container: sidebar / inline-size;
}

@container sidebar (min-width: 30rem) {
    .widget {
        flex-direction: row;
    }
}
```

The `container` shorthand is `name / type`.

### Container query units for fluid typography

`1cqi` equals 1% of the container's inline size. Without explicit containment, `cqi` falls back to the small viewport size.

```css
.card :is(h2, h3) {
    font-size: clamp(1.25rem, 5cqi, 1.5rem);
    text-wrap: balance;
}
```

Gate `cqi` usage behind `@supports (font-size: 1cqi)` to prevent browsers that don't support it from collapsing text due to custom property resolution quirks. Define `vw`-based fluid type as the default, then upgrade to `cqi` inside the `@supports` block.

### Combining size and style queries

Size and style container queries cannot be combined in one rule. Nest them instead:

```css
@container (min-width: 40ch) {
    @container style(--show-label: true) {
        .label {
            display: block;
        }
    }
}
```

Style queries use custom properties as boolean toggles.

---

## Spacing system

Use these unit conventions for spacing. The unit choices are deliberate.

**Padding** — use `clamp()` with `%` (relative to element inline size):

```css
--padding-md: clamp(1.5rem, 6%, 3rem);
```

**Margin / block flow** — use `min()` with `vh` (reduces spacing for zoomed or short viewports):

```css
--block-flow-md: min(4rem, 8vh);
```

**Gap** — use `clamp()` with `vmax` (uses whichever viewport dimension is larger, producing even spacing in both directions):

```css
--gap-md: clamp(1.5rem, 6vmax, 3rem);
```

Do not use `%` for gap. Percent-based gap calculates based on direction and produces uneven row vs. column gaps.

---

## Typography

### Fluid type scale

Use `clamp()` for fluid font sizes. Always include `+ 1rem` in the ideal value for WCAG SC 1.4.4 compliance (text must resize to 200%).

```css
:root {
    --type-ratio: 1.33; /* Perfect Fourth */
    --font-size-lg: calc(1rem * var(--type-ratio));
    --font-size-xl: calc(var(--font-size-lg) * var(--type-ratio));
    --font-size-2xl: calc(var(--font-size-xl) * var(--type-ratio));
}
```

### Text wrapping

Use `text-wrap: balance` on headlines (limited to 6 lines). Use `text-wrap: pretty` on body text (prevents orphan words, evaluates last 4 lines). These are safe progressive enhancements — no harm where unsupported.

### Number alignment

Use `font-variant-numeric: tabular-nums` for tables, prices, timers, and any UI where numbers should align vertically. All digits get equal width.

---

## Colors

### Think in relationships, not hex values

Define colors as transformations of a base. Use relative color syntax:

```css
:root {
    --brand: #4b6bff;
    --hover: oklch(from var(--brand) calc(l - 0.12) c h);
    --soft: oklch(from var(--brand) calc(l + 0.4) c h);
    --accent: oklch(from var(--brand) l c calc(h + 20));
    --border: rgb(from var(--brand) r g b / 0.18);
    --shadow: rgb(from var(--brand) r g b / 0.3);
}
```

Rules of thumb:

- `rgb()` for transparency adjustments.
- `oklch()` for lightness changes (most perceptually consistent across hues). OKLCH lightness runs 0–1, not 0–100, so `+0.1` is already significant.
- `hsl()` for quick hue or saturation shifts.

Use `color-mix()` for simpler blending:

```css
background: color-mix(in hsl, var(--primary), transparent 95%);
```

Use `currentColor` aggressively. It automatically updates when `color` changes. Example: `border: 1px solid rgb(from currentColor r g b / 0.2)`.

---

## Form styling

Follow this pattern for custom checkboxes, radio buttons, and selects. It preserves semantics, scales with font size, and supports forced-colors mode.

### Core technique

1. `appearance: none` strips native chrome while keeping semantics.
2. `currentColor` for automatic theme inheritance.
3. `em` units for proportional scaling.
4. CSS grid for layout (`place-content: center` on the input, grid for label-input alignment).
5. Pseudo-elements (`::before`) for custom checked/unchecked indicators.

### Radio buttons

```css
input[type="radio"] {
    appearance: none;
    margin: 0;
    font: inherit;
    color: currentColor;
    width: 1.15em;
    height: 1.15em;
    border: 0.15em solid currentColor;
    border-radius: 50%;
    display: grid;
    place-content: center;
    transform: translateY(-0.075em);
}
input[type="radio"]::before {
    content: "";
    width: 0.65em;
    height: 0.65em;
    border-radius: 50%;
    transform: scale(0);
    transition: 120ms transform ease-in-out;
    box-shadow: inset 1em 1em var(--form-control-color);
    background-color: CanvasText;
}
input[type="radio"]:checked::before {
    transform: scale(1);
}
```

Why `box-shadow: inset 1em 1em` instead of `background-color`: it stays visible when printed. The `background-color: CanvasText` is a forced-colors-mode fallback — Windows High Contrast Mode strips `box-shadow` but retains `background-color` when it uses system color keywords.

The `transform: translateY(-0.075em)` aligns the input with the first line of multi-line label text. Do not use `align-items: center` for this — it vertically centers against the full label height, which looks wrong with wrapped text.

### Checkboxes

Same as radio buttons but with `border-radius: 0.15em` and a checkmark via clip-path:

```css
clip-path: polygon(14% 44%, 0 65%, 50% 100%, 100% 16%, 80% 0%, 43% 62%);
```

### Select dropdowns

Use `clip-path: polygon(100% 0%, 0 0%, 50% 100%)` for the triangle arrow. This is preferred over SVG because it retains access to CSS custom properties for theming.

### Text inputs and textareas

Set `font-size: max(16px, 1em)` to prevent iOS Safari from zooming on focus (Safari triggers zoom when input font-size is below 16px).

---

## Selectors

### `:has()` for state management without JavaScript

Wrap `:has()` with `:where()` to null its specificity contribution:

```css
.button:where(:has(.icon)) {
    display: flex;
    gap: 0.5em;
    align-items: center;
}
```

Use `:has()` for layout switching based on native input states:

```css
:root:has(#darkmode:checked) {
    --color-text: #fff;
    --color-bg: #111;
}
```

### `:is()` vs `:where()`

`:is(h1, h2, h3)` takes the highest specificity of its arguments. Use when you want resilience against accidental overrides. `:where(h1, h2, h3)` has zero specificity. Use in resets, defaults, and base layers where easy override is the goal.

### `:focus-visible` with progressive fallback

```css
button:focus {
    outline: max(1px, 0.1em) dashed currentColor;
    outline-offset: -0.25em;
}
button:focus:not(:focus-visible) {
    outline: none;
}
button:focus-visible {
    outline: max(1px, 0.1em) dashed currentColor;
    outline-offset: -0.25em;
}
```

Browsers that don't understand `:focus-visible` discard the `:focus:not(:focus-visible)` rule entirely, so the base `:focus` style remains as a safe fallback.

---

## Accessibility

### Focus styles

Define focus styles with custom properties so components can override them:

```css
:focus-visible {
    --outline-size: max(2px, 0.15em);
    outline: var(--outline-width, var(--outline-size))
        var(--outline-style, solid) var(--outline-color, currentColor);
    outline-offset: var(--outline-offset, var(--outline-size));
}
```

Use negative `outline-offset` (like `-0.35em`) on buttons to place the indicator inside the element boundary. This improves contrast against varied backgrounds.

### Reduced motion

Use `0.01ms` duration instead of `0`. Animations still complete rather than freezing on the wrong frame:

```css
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}
```

### Touch targets

Use `max(44px, 2em)` for width and height. This guarantees the WCAG 2.5.5 minimum of 44px while scaling with context.

Gate hover-dependent interactions behind capability detection:

```css
@media (any-hover: hover) and (any-pointer: fine) {
    .tooltip:hover .tooltip-content {
        display: block;
    }
}
```

### Zoom resilience (WCAG 1.4.10 Reflow)

400% zoom at 1280px equals a 320px equivalent viewport. Use `min()` to handle this:

```css
grid-template-columns: min(64px, 15%, 10vw) 1fr;
```

The three values handle normal viewports (64px), narrow containers (15%), and zoomed desktops (10vw).

### Windows High Contrast Mode (forced colors)

Windows High Contrast Mode strips `box-shadow` and non-system `background-color`. Always pair visual `box-shadow` effects with `outline: Xpx solid transparent`. The transparent outline becomes visible in forced-colors mode.

---

## One-line upgrades

Apply these as defaults. They require no architectural changes.

`aspect-ratio: 16/9` — replaces the padding-bottom hack. Content can exceed the ratio unless constrained. Pair with `object-fit: cover` for images.

`margin-inline: auto` — replaces `margin-left: auto; margin-right: auto`. Adapts to writing direction.

`text-underline-offset: 0.25em` — clears descenders on links. Pair with `text-decoration-thickness: max(0.08em, 1px)`.

`overscroll-behavior: contain` — on scrollable containers, prevents scroll chaining. When a sidebar or modal's scroll is exhausted, the page behind it stays put.

`scrollbar-gutter: stable both-edges` — on `html`, reserves space for scrollbars, preventing layout shift when dialogs open. No effect on macOS (overlay scrollbars). Do not drop padding in favor of this — overlay scrollbar users would lose all spacing.

`width: fit-content` — shrink-wraps an element without changing `display`. The logical property version is `inline-size: fit-content`.

`isolation: isolate` — on component wrappers, prevents `z-index` leaks from decorative pseudo-elements.

---

## Animations

### `@starting-style` for entry animations

Define an element's initial state without `@keyframes`. CSS transitions handle the animation when the element enters the DOM. Use for modals, popovers, and drawers. ~88% support as of early 2026.

### Height transitions via grid

Solve `height: auto` animation with grid-template-rows:

```css
.dropdown {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.5s ease;
}
.dropdown.open {
    grid-template-rows: 1fr;
}
.dropdown > .inner {
    overflow: hidden;
}
```

`calc-size(auto)` will solve this more elegantly, but `grid-template-rows` works now.

### Scroll state queries

CSS can now detect scroll position and sticky state without JavaScript (~68.5% support):

```css
.header-wrap {
    position: sticky;
    top: 0;
    container-type: scroll-state;
}
@container scroll-state(stuck: top) {
    .header {
        box-shadow: 0 8px 30px rgb(0 0 0 / 0.12);
    }
}
```

This is progressive enhancement territory. Use it to eliminate `IntersectionObserver` boilerplate for sticky header effects.

---

## Summary of non-negotiable patterns

Three patterns should appear in every project:

1. **`appearance: none` + pseudo-elements + custom properties** for form control styling. This preserves semantics, scales with font size, and supports forced-colors mode.

2. **`repeat(auto-fit, minmax(min(100%, VAR), 1fr))`** for responsive grid layouts without media queries.

3. **Every visual effect needs an accessibility counterpart**: `box-shadow` gets a transparent `outline` for WHCM. Animations get `prefers-reduced-motion` overrides. Hover effects get `@media (any-hover: hover)` gates. Touch targets get `max(44px, 2em)` minimums.

Modern CSS declares intent and lets the platform do the work. Reach for JavaScript last.
