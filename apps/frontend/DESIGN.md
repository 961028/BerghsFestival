# Design System: Friction Festival

## 1. Visual Theme & Atmosphere

High-contrast, black-dominant aesthetic with raw, bold energy. The design language is rooted in Swiss-style graphic design: grid-driven layouts, tight typographic hierarchy, and no decorative elements. The mood is confrontational and industrial. White elements punch through a void-black canvas. Accent colors are pure, saturated primaries used sparingly and at full intensity.

## 2. Color Palette & Roles

- **Void Black** (`#000000`) — Primary background. Used for all surfaces and containers.
- **Pure White** (`#FFFFFF`) — Primary foreground. All text, icons, dividers, and UI elements.
- **Signal Green** (`#00FF00`) — Accent 1.
- **Signal Red** (`#FF0000`) — Accent 2.
- **Electric Blue** (`#1E00FF`) — Accent 3.
- **Acid Yellow** (`#EEFF00`) — Accent 4.

Accent colors can be used freely throughout the design. They are never mixed, blended, or used as gradients. They appear as solid, flat blocks at full saturation. No opacity variations. No tints or shades.

## 3. Typography Rules

- **Primary Typeface:** Inria Sans
- **Fallback:** system sans-serif stack
- **Logo / Display:** Custom condensed sans-serif with heavy weight, tightly tracked, all-caps. Used only for the brand name "FRICTION FESTIVAL." Letters are set with negative tracking and slightly overlap or interlock.
- **Section Labels:** All-caps, wide letter-spacing (tracking ~0.15em), regular weight, small size (~11–13px). Used for grid section headers like "SYMBOL," "COLORS," "TYPOGRAPHY," "LOGO."
- **Body Text:** Inria Sans, regular weight, 12–14px. Used for descriptive copy. Set in sentence case.
- **Large Display (Aa):** Inria Sans, bold weight, used at very large sizes for typographic specimens.

No italic usage. No underlines. Hierarchy is achieved through size and weight contrast only.

## 4. Component Stylings

- **Dividers:** 1.27px solid white lines. Used to separate grid sections both horizontally and vertically. Lines span the full width or height of the layout.
- **Color Swatches:** Equal-width rectangular blocks, arranged in a horizontal row with no gaps. No rounded corners. No borders except on the black swatch, which gets a 1px white stroke to distinguish it from the background.
- **Logo Lockup:** The brand name is set in a custom ultra-bold condensed sans-serif, stacked in two lines ("FRICTION" / "FESTIVAL"). White on black. Text is left-aligned to the grid. The type is massive, filling the available space.
- **Symbol/Icon:** A geometric circle and rectangle composition. The circle is solid white. The rectangle is solid white, overlapping or adjacent. These are abstract brand marks, not functional icons.
- **Buttons:** Not defined in this system. If needed, use a solid white rectangle with black text, no border-radius.
- **Cards/Containers:** No visible card containers. Content is organized by the grid and divider lines. Background is always black.
- **Inputs/Forms:** Not defined. If needed, use a 1px white border on black background, white text, no border-radius.

## 5. Spacing & Layout

- **Grid:** The layout uses a clear 2-column, 2-row grid. A vertical divider splits the canvas roughly at 35% from the left. A horizontal divider splits the canvas at roughly 52% from the top.
- **Margins:** Generous outer margins (~75px on a 1920px canvas, roughly 4%).
- **Section Padding:** Content within grid cells has consistent internal padding (~20–30px from divider lines).
- **Spacing Scale:** Not token-based. Spacing is determined by the grid structure. Elements within sections use optical spacing rather than a fixed scale.

## 6. Do's and Don'ts

**Do:**

- Keep backgrounds black at all times.
- Use white for all foreground elements.
- Use accent colors freely as solid, flat blocks at any size or context.
- Maintain strict grid alignment with visible divider lines.
- Use all-caps with wide tracking for labels and section headers.
- Keep typography minimal: one typeface family (Inria Sans) plus the display/logo face.

**Don't:**

- Use gradients, shadows, glows, or any depth effects.
- Round any corners. All shapes are sharp rectangles or geometric primitives.
- Mix accent colors within a single element.
- Use decorative imagery, illustrations, or photography.
- Add opacity or transparency to any element.
- Use more than two font weights in body content (regular and bold).
