# Berghs Festival 2026 — Information architecture and design decisions

---

## Site structure

Four pages. Same nav on every page.

| Nav label   | Purpose                                     |
| ----------- | ------------------------------------------- |
| Home        | Emotional sell, logistics, theme manifesto  |
| Experiences | Everything on the festival and schedule     |
| Projects    | Graduation work from the class of 2026      |
| About       | Berghs intro + the team behind the festival |

### Changes from previous years

- "Installations" and "Happening" merged to **Experiences** to cover schedule, installations, food, drink, tattoo studios, and everything else.
- **About** expanded to include a new "The team" section crediting students and teachers, organized by festival role.

---

## Page-by-page content

### Home

- Hero moment (video or animation) at the top.
- Festival name, dates, times, location.
- Theme manifesto tied to the year's concept.
- Schedule overview (Friday and Saturday at a glance).
- Registration CTA (in addition to persistent header CTA).

### Experiences

Four clear sections within one page:

1. **Installations** — interactive and immersive experiences, themed to the year's concept. Each with a name and short description.
2. **Food and drink** — vendor profiles with what they serve.
3. **Other activities** — tattoo studio, merch shop, or similar.
4. **Schedule** — visual timeline, ideally showing parallel tracks if events overlap across spaces. Artist profiles with photo, short bio, and Instagram link.

### Projects

- Filter between **All**, **Group**, and **Individual** projects at the top of the page.
- Grid layout with 3:2 thumbnail images and minimal text per card (project title + client name).
- Each card links to a detail page.
- Detail pages show a selection of related projects.

### About

Two sections:

1. **Berghs School of Communication** — short institutional intro. Link to berghs.se for more.
2. **The team** — students and supporting teachers who built the festival, organized by role (creative direction, production, web, marketing, etc.).

---

## Global elements

### Persistent registration CTA

- Built into the sticky navigation header.
- Visible at all scroll positions, on every page.
- Links to Eventbrite registration.
- Replaces the previous approach of in-page links that disappeared on scroll.

### Navigation header

- Festival logo/name on the left.
- Nav items: Experiences, Projects, About.
- Register button on the right.
- Sticky on scroll.

### Footer (every page)

- Sponsor logos.
- Berghs contact info.
- Social links.
- Short responsible drinking notice (one line) linking to a dedicated policy page.
- Photo/video consent notice.

### Responsible drinking

- One-line notice in the footer of every page.
- Links to a standalone page with the full policy text and a link to IQ.se.
- Replaces the full-paragraph notice that was duplicated across all pages in previous years.

---

## Projects page — design decisions

### Layout: fluid grid with team names

Chosen over alternatives for the following reasons:

- Large enough 3:2 images to show the quality of the work.
- Room for client name, project title, and team member names on each card.
- Balances visual impact with information density.
- Fluid grid adapts to available space — narrower on mobile, wider on desktop.

### Card structure

Each card contains:

1. Thumbnail image (3:2 aspect ratio).
2. Client name (small text above the title).
3. Project title.
4. Team member names + Programme (small text below the title).

### Image requirements

- **Format:** 3:2 aspect ratio only.
- **Minimum resolution:** 1200 x 800px to support retina displays.
- Students provide one image per project. No other formats are needed.

### Responsive behavior

The grid uses `auto-fit` with a minimum column width of 20rem, so the number of columns adjusts fluidly to the viewport width rather than switching at fixed breakpoints.

### Group/Individual filter

- Three buttons at the top of the page: All, Group, Individual.
- Defaults to "All" selected.
- Group and individual projects are mixed together in the same grid rather than separated into distinct sections. This is my decision — previous years kept them separate, but mixing them and adding a filter gives every project a fair and equal position in the listing.
- One of the main reasons to mix them is fairness in the random ordering (see below).

### Project ordering

Projects are displayed in a random order to give every project an equal chance of being seen. This has been the approach in previous years.

My change from previous years: the order is randomised once per visitor session rather than on every page refresh. Per-refresh randomisation causes projects to reorder mid-visit, which is a poor experience. Per-session randomisation keeps the order stable while a visitor is browsing, while still being random across visitors.

### Project name length

There is no enforced maximum length on project names, but the display will truncate with an ellipsis beyond a set limit.

The limit may differ by context:

- **Listing page (card):** shorter limit — less space available per card.
- **Detail page (heading):** potentially longer — more space and prominence.

Exact limits are TBD.

### Detail page

- Detail pages show a selection of related projects at the bottom.
- Allows visitors to continue browsing without returning to the listing.

### Project content sections

Each project detail page is structured into three fixed sections. Berghs decided the headings are permanent — used across all years so they can display projects from previous festivals alongside current ones consistently.

| Section | Heading |
| ------- | ----------- |
| 1 | The Company |
| 2 | Background |
| 3 | Solution |

**Editorial note:** Students must use these headings exactly as written. They are not editable.

**Text length constraints:**

Each section has a minimum and maximum character count, and there is a total cap across all three sections combined. These limits are a decision made for this project to keep detail pages consistent in length and readable without the need for editorial review of every submission.

| Section | Min | Max |
| ----------- | --- | --- |
| The Company | TBD | TBD |
| Background | TBD | TBD |
| Solution | TBD | TBD |
| **Total** | — | TBD |

*Limits will be enforced in the content collection schema (Zod validation at build time).*

---

## What was kept from previous years

- Eventbrite for registration.
- Two-day format: Friday evening festival + Saturday daytime exhibition.
- Sponsor logo bar on every page.
- Berghs branding in header, linking to berghs.se.
- Social links to LinkedIn, Instagram, Facebook.
