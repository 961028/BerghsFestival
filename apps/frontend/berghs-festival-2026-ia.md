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
- Replaces the previous approach of listing individual projects in a separate section at the bottom of a long page.

### Detail page

- Detail pages show a selection of related projects at the bottom.
- Allows visitors to continue browsing without returning to the listing.

---

## What was kept from previous years

- Eventbrite for registration.
- Two-day format: Friday evening festival + Saturday daytime exhibition.
- Sponsor logo bar on every page.
- Berghs branding in header, linking to berghs.se.
- Social links to LinkedIn, Instagram, Facebook.
