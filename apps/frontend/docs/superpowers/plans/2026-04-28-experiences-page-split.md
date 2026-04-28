# Experiences Page Split Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Split `/experiences` into per-section pages under `/experiences/*` and surface those sections as a header dropdown (desktop) / indented mobile menu items, removing the in-page sticky nav and its scroll-spy.

**Architecture:** A new dynamic route `/experiences/[slug]` reads the experiences WP page once at build time and emits one path per section (`schedule` plus each ACF group slug). Two new templates render the schedule and a single group respectively. The header derives the same sub-items at build time and renders a non-link toggle button + dropdown. The legacy `PageExperiences.astro` and its scroll-spy machinery are deleted.

**Tech Stack:** Astro 5 (static, content collections, Zod schemas), TypeScript, plain CSS in scoped `<style>` blocks. No test runner — verification is via `npm run build` + visiting the dev server.

**Spec:** [docs/superpowers/specs/2026-04-28-experiences-page-split-design.md](../specs/2026-04-28-experiences-page-split-design.md)

**Conventions used in this plan:**
- Paths are relative to `apps/frontend/` (the `npm` project root).
- "Run dev" means `npm run dev` from `apps/frontend/`. The dev server URL is whatever Astro prints (typically `http://localhost:4321`).
- "Run build" means `npm run build` from `apps/frontend/`. Expected: exits 0, no Zod parse errors.
- Each task ends with a commit. Use Conventional Commits style; existing repo commits are short imperative summaries (e.g. "Add compact CTA variant…"), so match that.
- The CSS rules of this project (in `css-rules.md`) and design tokens (`src/styles/global.css`) apply throughout.

---

## File structure

| Action  | Path | Responsibility |
|---------|------|----------------|
| Create  | `src/lib/experiences.ts` | Shared Zod schemas (`ScheduleSchema`, `GroupsSchema`) + a `getExperiencesPage()` helper that returns `{ page, schedule, groups }` for the WP page with `template === "page-experiences.php"`. |
| Create  | `src/templates/ExperiencesSchedule.astro` | Renders the schedule list with the day toggle. Owns the day-toggle script + day/schedule CSS. |
| Create  | `src/templates/ExperiencesGroup.astro` | Renders a single group: description + `music`/`installations`/`food` layout. Owns the group/food CSS. |
| Create  | `src/pages/experiences/[slug].astro` | Dynamic route. `getStaticPaths` emits `schedule` + each `group.slug`. Picks the right template per section. Wraps in `<Layout>` with per-section title. |
| Create  | `src/pages/experiences/index.astro` | Static redirect to `/experiences/schedule`. |
| Create  | `src/svg/chevron.svg` | Down-pointing chevron icon for the dropdown trigger. Inherits `currentColor`. |
| Modify  | `src/pages/[...link].astro` | Filter `page-experiences.php` out of `getStaticPaths`. |
| Modify  | `src/components/PageTemplate.astro` | Remove `page-experiences.php` from `SLUG_TO_TEMPLATE`; remove the `PageExperiences` import. |
| Modify  | `src/components/site/Header.astro` | Fetch experiences sub-items at build; render the experiences menu item as a button + submenu (desktop) and as a label + indented links (mobile). |
| Modify  | `src/scripts/header.ts` | Add click toggle, outside-click close, and Escape close for the experiences dropdown. |
| Delete  | `src/templates/PageExperiences.astro` | Once nothing imports it. |

---

## Task 1: Extract experiences schemas + page-load helper into `src/lib/experiences.ts`

**Why first:** Both the dynamic route and `Header.astro` need the parsed groups. Putting them in one place avoids duplicating the Zod schemas.

**Files:**
- Create: `src/lib/experiences.ts`

- [ ] **Step 1: Create the shared module**

Create `src/lib/experiences.ts` with the schemas (lifted verbatim from `src/templates/PageExperiences.astro:16-57`) plus a `getExperiencesPage()` helper.

```ts
import { getCollection, type CollectionEntry } from "astro:content";
import { z } from "astro/zod";

import { nullableIntReference, repeater } from "./schema";

export const ScheduleSchema = repeater(
    z.object({
        day: z.string(),
        events: repeater(
            z
                .object({
                    start_time: z.string(),
                    title: z.string(),
                    image: nullableIntReference("media"),
                    description: z.string().nullable().optional(),
                })
                .transform((item) => ({
                    startTime: item.start_time,
                    title: item.title,
                    image: item.image ?? null,
                    description: item.description ?? null,
                })),
        ),
    }),
);

export const GroupsSchema = repeater(
    z
        .object({
            title: z.string(),
            slug: z.string().optional(),
            layout: z.enum(["music", "installations", "food"]),
            description: z.string().transform((val) => ({ html: val })),
            items: repeater(
                z.object({
                    name: z.string(),
                    image: nullableIntReference("media"),
                    description: z.string().transform((val) => ({ html: val })),
                    url: z.string().optional(),
                }),
            ),
        })
        .transform((val) => ({
            ...val,
            slug: val.slug || z.string().slugify().parse(val.title),
        })),
);

export type Schedule = z.infer<typeof ScheduleSchema>;
export type Group = z.infer<typeof GroupsSchema>[number];

export async function getExperiencesPage(): Promise<{
    page: CollectionEntry<"pages">;
    schedule: Schedule;
    groups: Group[];
} | null> {
    const pages = await getCollection("pages");
    const page = pages.find(
        (p) => p.data.template === "page-experiences.php",
    );

    if (!page) {
        return null;
    }

    return {
        page,
        schedule: ScheduleSchema.parse(page.data.acf.schedule),
        groups: GroupsSchema.parse(page.data.acf.groups),
    };
}
```

- [ ] **Step 2: Verify the build still works (the file is unused for now)**

Run: `npm run build`
Expected: exits 0. (No callers yet, but a TS error in this file would still fail.)

- [ ] **Step 3: Commit**

```bash
git add src/lib/experiences.ts
git commit -m "Extract experiences schemas and page-load helper"
```

---

## Task 2: Create `ExperiencesSchedule.astro` template

**Files:**
- Create: `src/templates/ExperiencesSchedule.astro`

This is the schedule section of `PageExperiences.astro` lifted into its own component, with no surrounding `<Layout>` (the parent page provides that).

- [ ] **Step 1: Create the template**

```astro
---
import type { Schedule } from "../lib/experiences";

import WpImage from "../components/elements/WpImage.astro";

interface Props {
    schedule: Schedule;
}

const { schedule } = Astro.props;
---

<section>
    <ol class="day-toggle" role="group" aria-label="Select day">
        {
            schedule.map((dayData, i) => (
                <li>
                    <button
                        data-day={i}
                        aria-pressed={i === 0 ? "true" : "false"}
                    >
                        {dayData.day}
                    </button>
                </li>
            ))
        }
    </ol>

    {
        schedule.map((dayData, dayIndex) => (
            <div data-day-panel={dayIndex} hidden>
                <ul>
                    {dayData.events.map((event) =>
                        event.image && event.description ? (
                            <li data-rich>
                                <span>{event.startTime}</span>
                                <div>
                                    <div>
                                        <p>{event.title}</p>
                                        <p>{event.description}</p>
                                    </div>
                                    <WpImage
                                        image={event.image}
                                        width={140}
                                        height={140}
                                        alt={event.title}
                                    />
                                </div>
                            </li>
                        ) : (
                            <li>
                                <span>{event.startTime}</span>
                                <p>{event.title}</p>
                            </li>
                        ),
                    )}
                </ul>
            </div>
        ))
    }
</section>

<script>
    const dayPills = document.querySelectorAll<HTMLButtonElement>("[data-day]");
    const dayPanels =
        document.querySelectorAll<HTMLElement>("[data-day-panel]");

    function selectDay(dayIndex: number) {
        dayPills.forEach((p) => {
            p.setAttribute(
                "aria-pressed",
                String(Number(p.dataset.day) === dayIndex),
            );
        });
        dayPanels.forEach((p) => {
            p.hidden = Number(p.dataset.dayPanel) !== dayIndex;
        });
    }

    dayPills.forEach((pill) => {
        pill.addEventListener("click", () =>
            selectDay(Number(pill.dataset.day)),
        );
    });

    // Default day: Saturday (day 6) → index 1, otherwise → index 0.
    const defaultDay = new Date().getDay() === 6 ? 1 : 0;

    selectDay(defaultDay);
</script>

<style>
    section {
        padding-block: var(--space-xl);
    }

    /* ── Day toggle ───────────────────────────────────────────────────
       Same 1px-grid vocabulary as the page nav, but at base size. */
    .day-toggle {
        display: grid;
        grid-auto-flow: column;
        grid-auto-columns: 1fr;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .day-toggle button {
        appearance: none;
        background: none;
        border: none;
        width: 100%;
        padding: var(--space-sm) var(--space-xs);
        font-size: var(--font-size-base);
        font-family: inherit;
        line-height: var(--line-height-tight);
        color: var(--color-text-muted);
        cursor: pointer;
        text-align: center;
        text-transform: lowercase;
    }

    .day-toggle button[aria-pressed="true"] {
        background: var(--color-text-on-bg);
        color: var(--color-text);
        font-weight: var(--font-weight-bold);
        text-transform: uppercase;
    }

    /* ── Schedule rows ────────────────────────────────────────────── */
    [data-day-panel] ul {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    [data-day-panel] li {
        display: grid;
        grid-template-columns: var(--schedule-time-col) 1fr;
        gap: var(--space-sm);
        padding-block: var(--space-md);
        border-block-start: var(--border-default);
    }

    [data-day-panel] li span {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-bold);
        color: var(--color-text-muted);
        line-height: var(--line-height-tight);
        padding-block-start: 0.1em;
    }

    [data-day-panel] li p {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-normal);
        margin: 0;
    }

    /* Rich row */
    [data-day-panel] li[data-rich] > div {
        display: flex;
        align-items: flex-start;
        gap: var(--space-md);
    }

    [data-day-panel] li[data-rich] > div > div {
        flex: 1;
    }

    [data-day-panel] li[data-rich] p:first-child {
        font-size: var(--font-size-base);
        margin-block-end: var(--space-xs);
    }

    [data-day-panel] li[data-rich] p:last-child {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-normal);
        line-height: var(--line-height-base);
        margin: 0;
    }

    [data-day-panel] li[data-rich] :global(img) {
        flex-shrink: 0;
        width: clamp(5rem, 20%, 8.75rem);
        aspect-ratio: 1;
        object-fit: cover;
        display: block;
    }
</style>
```

- [ ] **Step 2: Verify the build**

Run: `npm run build`
Expected: exits 0. (Still no callers, but TS/Zod errors would fail.)

- [ ] **Step 3: Commit**

```bash
git add src/templates/ExperiencesSchedule.astro
git commit -m "Add ExperiencesSchedule template"
```

---

## Task 3: Create `ExperiencesGroup.astro` template

**Files:**
- Create: `src/templates/ExperiencesGroup.astro`

This is the group section of `PageExperiences.astro` lifted into a single-group component.

- [ ] **Step 1: Create the template**

```astro
---
import type { Group } from "../lib/experiences";

import WpImage from "../components/elements/WpImage.astro";
import MusicList from "../components/experiences/MusicList.astro";
import InstallationsList from "../components/experiences/InstallationsList.astro";

interface Props {
    group: Group;
}

const { group } = Astro.props;
---

<section>
    <div set:html={group.description.html} />

    {group.layout === "music" && <MusicList items={group.items} />}
    {group.layout === "installations" && (
        <InstallationsList items={group.items} />
    )}
    {group.layout === "food" && (
        <ul>
            {group.items.map((item) => (
                <li>
                    {item.image && (
                        <WpImage
                            image={item.image}
                            width={600}
                            height={300}
                            alt={item.name}
                        />
                    )}
                    <div>
                        <p>{item.name}</p>
                        <div set:html={item.description.html} />
                    </div>
                </li>
            ))}
        </ul>
    )}
</section>

<style>
    section {
        padding-block: var(--space-xl);
    }

    /* ── Group intro ──────────────────────────────────────────────── */
    section > div:first-child {
        font-size: var(--font-size-base);
        line-height: var(--line-height-base);
        max-width: var(--prose-width);
        margin-block-end: var(--space-md);
    }

    /* ── Food & drink ─────────────────────────────────────────────── */
    section > ul {
        list-style: none;
        margin: 0;
        padding: 0;
        display: grid;
        grid-template-columns: repeat(
            auto-fill,
            minmax(min(100%, var(--schedule-food-col-min)), 1fr)
        );
        gap: var(--space-xs);
    }

    section > ul li {
        border: var(--border-default);
        overflow: hidden;
    }

    section > ul li :global(img) {
        display: block;
        width: 100%;
        aspect-ratio: 2 / 1;
        object-fit: cover;
    }

    section > ul li > div {
        padding: var(--space-sm);
    }

    section > ul li p {
        font-size: var(--font-size-base);
        font-weight: var(--font-weight-bold);
        margin-block-end: var(--space-xxs);
    }

    section > ul li div > div {
        font-size: var(--font-size-base);
        line-height: var(--line-height-base);
        margin: 0;
    }
</style>
```

- [ ] **Step 2: Verify the build**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 3: Commit**

```bash
git add src/templates/ExperiencesGroup.astro
git commit -m "Add ExperiencesGroup template"
```

---

## Task 4: Create the dynamic route `src/pages/experiences/[slug].astro`

**Files:**
- Create: `src/pages/experiences/[slug].astro`

`getStaticPaths` emits one path per section. Section data is passed via props so the page renders without re-parsing.

- [ ] **Step 1: Create the dynamic route**

```astro
---
import Layout from "../../layouts/Layout.astro";
import ExperiencesSchedule from "../../templates/ExperiencesSchedule.astro";
import ExperiencesGroup from "../../templates/ExperiencesGroup.astro";
import {
    getExperiencesPage,
    type Group,
    type Schedule,
} from "../../lib/experiences";

type Section =
    | { kind: "schedule"; label: string; schedule: Schedule }
    | { kind: "group"; label: string; group: Group };

export async function getStaticPaths() {
    const data = await getExperiencesPage();

    if (!data) {
        return [];
    }

    const { page, schedule, groups } = data;

    const sections: Array<{ slug: string; section: Section }> = [
        {
            slug: "schedule",
            section: { kind: "schedule", label: "Schedule", schedule },
        },
        ...groups.map((group) => ({
            slug: group.slug,
            section: {
                kind: "group" as const,
                label: group.title,
                group,
            },
        })),
    ];

    return sections.map(({ slug, section }) => ({
        params: { slug },
        props: { page: page.data, section },
    }));
}

const { page, section } = Astro.props;

const title = `${page.title} — ${section.label}`;
---

<Layout
    title={title}
    metaDescription={page.metaDescription}
    ogImage={page.image}
    ctaCompact
>
    {section.kind === "schedule" && (
        <ExperiencesSchedule schedule={section.schedule} />
    )}
    {section.kind === "group" && <ExperiencesGroup group={section.group} />}
</Layout>
```

- [ ] **Step 2: Verify the build emits the new routes**

Run: `npm run build`
Expected: exits 0. Among the printed pages, you should see `/experiences/schedule/` plus one path per ACF group slug (e.g. `/experiences/music/`, `/experiences/installations/`, `/experiences/food/` — exact slugs depend on WP content).

Also expected: `/experiences/` (without a trailing segment) is **still** generated by the legacy `[...link].astro` route at this point — that's fixed in Task 6.

- [ ] **Step 3: Spot-check one page in the dev server**

Run dev and visit `http://localhost:4321/experiences/schedule`. Expected: schedule renders with day toggle working (default day = today's index per the script). Visit `/experiences/<group-slug>` for one group; expected: description + the layout-specific list renders.

- [ ] **Step 4: Commit**

```bash
git add src/pages/experiences/[slug].astro
git commit -m "Add /experiences/[slug] dynamic route"
```

---

## Task 5: Create the `/experiences` index redirect

**Files:**
- Create: `src/pages/experiences/index.astro`

Astro's `Astro.redirect` works in static output — it emits an HTML page with a `<meta http-equiv="refresh">` and a redirect status (when supported). For a static deploy this renders as a meta-refresh page; that's the conventional approach for content collections.

- [ ] **Step 1: Create the redirect page**

```astro
---
return Astro.redirect("/experiences/schedule");
---
```

- [ ] **Step 2: Verify**

Run: `npm run build`
Expected: exits 0. `dist/experiences/index.html` exists and contains a redirect to `/experiences/schedule` (Astro emits a meta-refresh + canonical link for static redirects).

Also run dev and visit `http://localhost:4321/experiences/`. Expected: lands on `/experiences/schedule` (the dev server honours the `Astro.redirect` directly).

- [ ] **Step 3: Commit**

```bash
git add src/pages/experiences/index.astro
git commit -m "Redirect /experiences to /experiences/schedule"
```

---

## Task 6: Stop `[...link].astro` from rendering `/experiences`

**Files:**
- Modify: `src/pages/[...link].astro`

Right now `[...link].astro` renders **every** WP page, including the experiences page (which gets the legacy `PageExperiences` template). After this task, that route stops covering `/experiences` so the new index redirect takes over.

- [ ] **Step 1: Filter the experiences template out of `getStaticPaths`**

Replace the body of `getStaticPaths` (currently lines 8–31) so it skips `template === "page-experiences.php"`:

```astro
---
import { getCollection } from "astro:content";

import { resolvePageLink } from "../lib/content";
import PageTemplate from "../components/PageTemplate.astro";
import type { CollectionEntry } from "astro:content";

export async function getStaticPaths() {
    const settings = (await getCollection("settings"))[0].data;

    async function staticPathForPage(page: CollectionEntry<"pages">) {
        let link;

        if (page.id === settings.pageOnFront?.id) {
            link = undefined;
        } else {
            link = await resolvePageLink(page);
        }

        return {
            params: { link },
            props: {
                page,
            },
        };
    }

    const pages = (await getCollection("pages")).filter(
        (page) => page.data.template !== "page-experiences.php",
    );

    return await Promise.all(pages.map(staticPathForPage));
}

const { page } = Astro.props;
---

<PageTemplate page={page} />
```

- [ ] **Step 2: Verify the build no longer collides**

Run: `npm run build`
Expected: exits 0 with no "duplicate route" warning. `dist/experiences/index.html` is now the redirect page (from Task 5), not the legacy long-page render.

- [ ] **Step 3: Sanity-check in dev**

Run dev. Visit `http://localhost:4321/experiences/`. Expected: redirects to `/experiences/schedule` (no longer the long single-page version with the sticky nav).

- [ ] **Step 4: Commit**

```bash
git add src/pages/[...link].astro
git commit -m "Exclude experiences page template from generic page route"
```

---

## Task 7: Drop the experiences entry from `PageTemplate.astro`

**Files:**
- Modify: `src/components/PageTemplate.astro`

The mapping is now unreachable (the page is filtered out in Task 6). Remove the import and the dictionary entry so dead code doesn't linger.

- [ ] **Step 1: Remove the import and the mapping entry**

In `src/components/PageTemplate.astro`:

- Delete line 7: `import PageExperiences from "../templates/PageExperiences.astro";`
- In `SLUG_TO_TEMPLATE`, delete the `"page-experiences.php": PageExperiences,` entry.

After the edit, the relevant block reads:

```astro
import PageDefault from "../templates/Page.astro";
import PageAboutBerghs from "../templates/PageAboutBerghs.astro";
import PageHome from "../templates/PageHome.astro";
```

```ts
const SLUG_TO_TEMPLATE = {
    "page-about-berghs.php": PageAboutBerghs,
};
```

- [ ] **Step 2: Verify**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 3: Commit**

```bash
git add src/components/PageTemplate.astro
git commit -m "Drop unreachable experiences template mapping"
```

---

## Task 8: Delete the legacy `PageExperiences.astro`

**Files:**
- Delete: `src/templates/PageExperiences.astro`

Nothing imports it now (Task 7 removed the last reference).

- [ ] **Step 1: Confirm there are no remaining references**

Run: `grep -r "PageExperiences" src/` (run from `apps/frontend/`)
Expected: no output.

- [ ] **Step 2: Delete the file**

Run: `rm src/templates/PageExperiences.astro`

- [ ] **Step 3: Check for orphaned CSS variables**

The deleted template was the only consumer of `--sticky-nav-height` and `--experiences-nav-height`. Search for them:

```sh
grep -rn "sticky-nav-height\|experiences-nav-height" src/
```

If either name is defined in `src/styles/global.css` (or anywhere else in `src/`), delete those declarations as part of this task. If `grep` produces no output, no action needed.

- [ ] **Step 4: Verify**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 5: Commit**

```bash
git add -u src/templates/PageExperiences.astro
# also stage any global.css changes from step 3, if any
git commit -m "Delete legacy long-page experiences template"
```

---

## Task 9: Add the chevron SVG asset

**Files:**
- Create: `src/svg/chevron.svg`

A simple down-pointing chevron, sized to inherit `font-size` and use `currentColor`. The header strokes the existing logo via `fill`, but a stroke-based chevron is more legible at small sizes — match that.

- [ ] **Step 1: Create the SVG**

```xml
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" width="1em" height="1em" aria-hidden="true">
    <path d="M3 6l5 5 5-5" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
</svg>
```

- [ ] **Step 2: Verify it loads via the `<Svg>` glob**

Run: `npm run build`
Expected: exits 0. (The `<Svg>` component globs `src/svg/**/*.svg` eagerly; the new file is picked up automatically.)

- [ ] **Step 3: Commit**

```bash
git add src/svg/chevron.svg
git commit -m "Add chevron icon for nav dropdown"
```

---

## Task 10: Render the experiences dropdown in the header

**Files:**
- Modify: `src/components/site/Header.astro`

The WP primary menu still has a single "Experiences" item pointing at `/experiences`. We detect it by URL pathname and replace the `<a>` with a `<button>` + `<ul class="submenu">` on desktop. Mobile gets the same data as a non-link label + indented links.

- [ ] **Step 1: Fetch experiences sub-items in the frontmatter**

Replace the frontmatter in `src/components/site/Header.astro` (currently lines 1–21) with:

```astro
---
import { getCollection, getEntry } from "astro:content";
import Svg from "../elements/Svg.astro";
import { resolvePageLink } from "../../lib/content";
import { getExperiencesPage } from "../../lib/experiences";

const menuLocation = await getEntry("menuLocations", "primary");
const menuItems = (
    await getCollection(
        "menuItems",
        (item) => item.data.menu === menuLocation?.data.menu,
    )
).sort((a, b) => a.data.order - b.data.order);

const pages = await getCollection("pages");
const aboutPage = pages.find(
    (p) => p.data.template === "page-about-berghs.php",
);
const aboutHref = aboutPage
    ? `/${await resolvePageLink(aboutPage)}`
    : "https://www.berghs.se/om-berghs/";

const experiencesData = await getExperiencesPage();
const experiencesSubItems: Array<{ label: string; href: string }> =
    experiencesData
        ? [
              { label: "Schedule", href: "/experiences/schedule" },
              ...experiencesData.groups.map((group) => ({
                  label: group.title,
                  href: `/experiences/${group.slug}`,
              })),
          ]
        : [];

function isExperiencesItem(url: string): boolean {
    try {
        return new URL(url).pathname === "/experiences";
    } catch {
        return false;
    }
}

const currentPath = Astro.url.pathname;
---
```

- [ ] **Step 2: Replace the desktop `.nav-links` rendering**

Replace the existing `<ul class="nav-links">…</ul>` block (currently lines 36–57) with:

```astro
<ul class="nav-links">
    {
        menuItems.map((menuItem) => {
            const isExperiences =
                isExperiencesItem(menuItem.data.url) &&
                experiencesSubItems.length > 0;

            if (isExperiences) {
                return (
                    <li class="has-submenu">
                        <button
                            type="button"
                            class="nav-link submenu-toggle"
                            aria-expanded="false"
                            aria-controls="experiences-submenu"
                        >
                            <span class="nav-link-text" data-scramble>
                                {menuItem.data.title}
                            </span>
                            <Svg
                                name="chevron"
                                alt=""
                                class="submenu-chevron"
                            />
                        </button>
                        <ul class="submenu" id="experiences-submenu">
                            {experiencesSubItems.map((sub) => (
                                <li>
                                    <a
                                        href={sub.href}
                                        aria-current={
                                            currentPath === sub.href
                                                ? "page"
                                                : undefined
                                        }
                                    >
                                        {sub.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </li>
                );
            }

            return (
                <li>
                    <a
                        href={menuItem.data.url}
                        aria-current={
                            currentPath ===
                            new URL(menuItem.data.url).pathname
                                ? "page"
                                : undefined
                        }
                        class="nav-link"
                    >
                        <span class="nav-link-text" data-scramble>
                            {menuItem.data.title}
                        </span>
                    </a>
                </li>
            );
        })
    }
</ul>
```

- [ ] **Step 3: Replace the mobile menu rendering**

Replace the existing `.mobile-menu` block (currently lines 72–91) with:

```astro
<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
    {
        menuItems.map((menuItem) => {
            const isExperiences =
                isExperiencesItem(menuItem.data.url) &&
                experiencesSubItems.length > 0;

            if (isExperiences) {
                return (
                    <div class="mobile-group">
                        <span class="mobile-group-label">
                            {menuItem.data.title}
                        </span>
                        <ul class="mobile-submenu">
                            {experiencesSubItems.map((sub) => (
                                <li>
                                    <a
                                        href={sub.href}
                                        aria-current={
                                            currentPath === sub.href
                                                ? "page"
                                                : undefined
                                        }
                                        class="nav-link"
                                    >
                                        <span
                                            class="nav-link-text"
                                            data-scramble
                                        >
                                            {sub.label}
                                        </span>
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>
                );
            }

            return (
                <a
                    href={menuItem.data.url}
                    aria-current={
                        currentPath ===
                        new URL(menuItem.data.url).pathname
                            ? "page"
                            : undefined
                    }
                    class="nav-link"
                >
                    <span class="nav-link-text" data-scramble>
                        {menuItem.data.title}
                    </span>
                </a>
            );
        })
    }
</div>
```

- [ ] **Step 4: Add CSS for the submenu and chevron**

Append the following inside the existing `<style>` block in `src/components/site/Header.astro`. Place it after the `.nav-link[aria-current="page"]` rule and before the `@media (min-width: 48rem)` block.

```css
/* ── Submenu (desktop) ── */
.has-submenu {
    position: relative;
}

.submenu-toggle {
    appearance: none;
    background: none;
    border: none;
    padding: 0;
    margin: 0;
    color: inherit;
    font: inherit;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: var(--space-xxs);
}

.submenu-chevron {
    width: 0.75em;
    height: 0.75em;
    transition: transform 0.15s ease;
}

.submenu-toggle[aria-expanded="true"] .submenu-chevron {
    transform: rotate(180deg);
}

@media (prefers-reduced-motion: reduce) {
    .submenu-chevron {
        transition: none;
    }
}

.submenu {
    position: absolute;
    inset-inline-start: 0;
    inset-block-start: 100%;
    list-style: none;
    margin: 0;
    padding: var(--space-xs) var(--space-sm);
    background: var(--color-bg);
    border: var(--border-default);
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    min-width: max-content;
    visibility: hidden;
    opacity: 0;
    transition:
        opacity 0.15s ease,
        visibility 0s 0.15s;
}

.has-submenu:hover .submenu,
.has-submenu:focus-within .submenu,
.submenu-toggle[aria-expanded="true"] + .submenu {
    visibility: visible;
    opacity: 1;
    transition:
        opacity 0.15s ease,
        visibility 0s 0s;
}

.submenu a[aria-current="page"] {
    font-weight: var(--font-weight-bold);
    text-transform: uppercase;
}

/* ── Mobile submenu ── */
.mobile-group {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.mobile-group-label {
    font-size: var(--font-size-lg);
    font-weight: var(--font-weight-bold);
    color: var(--color-text-muted);
    text-transform: lowercase;
}

.mobile-submenu {
    list-style: none;
    margin: 0;
    padding: 0;
    padding-inline-start: var(--space-md);
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
```

Note: the desktop submenu CSS is wrapped by the existing `display: none` on `.nav-links` for narrow viewports, so it's already hidden on mobile.

- [ ] **Step 5: Verify desktop**

Run dev. At a viewport ≥ `48rem` (768px), hover the "Experiences" item — the submenu should appear, the chevron rotates 180°, and clicking a sub-item navigates. Visit `/experiences/schedule`; expected: the corresponding submenu item shows `aria-current="page"` styling.

- [ ] **Step 6: Verify mobile**

Resize the viewport below `48rem` (or use device emulation). Open the hamburger; expected: "Experiences" appears as a non-link label with the sub-items indented below it. Tapping a sub-item closes the menu (existing `link.addEventListener("click", close)` in `header.ts` covers all `<a>` elements inside `.mobile-menu`).

- [ ] **Step 7: Verify the build**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 8: Commit**

```bash
git add src/components/site/Header.astro
git commit -m "Add experiences dropdown to site header"
```

---

## Task 11: Wire up the desktop click-toggle, outside-click, and Escape

**Files:**
- Modify: `src/scripts/header.ts`

Hover and `:focus-within` already work. Touch users need the click toggle, and an open dropdown should close on outside-click and Escape.

- [ ] **Step 1: Add the dropdown toggle logic**

Append the following block to `src/scripts/header.ts` (after the existing `.nav-link` hover loop, at the end of the file):

```ts
// ── Experiences submenu toggle ──

const submenuToggle =
    document.querySelector<HTMLButtonElement>(".submenu-toggle");
const submenu =
    document.querySelector<HTMLElement>("#experiences-submenu");

if (submenuToggle && submenu) {
    function openSubmenu() {
        submenuToggle!.setAttribute("aria-expanded", "true");
    }

    function closeSubmenu() {
        submenuToggle!.setAttribute("aria-expanded", "false");
    }

    submenuToggle.addEventListener("click", () => {
        const open =
            submenuToggle.getAttribute("aria-expanded") === "true";
        if (open) {
            closeSubmenu();
        } else {
            openSubmenu();
        }
    });

    // Close when a sub-item is clicked.
    submenu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", closeSubmenu);
    });

    // Close on outside click.
    document.addEventListener("click", (event) => {
        const target = event.target as Node;
        if (
            submenuToggle.getAttribute("aria-expanded") === "true" &&
            !submenuToggle.contains(target) &&
            !submenu.contains(target)
        ) {
            closeSubmenu();
        }
    });

    // Close on Escape.
    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            submenuToggle.getAttribute("aria-expanded") === "true"
        ) {
            closeSubmenu();
            submenuToggle.focus();
        }
    });
}
```

Note: there's already an Escape listener earlier in the file (for the mobile menu). Both listeners run independently and only act when their own state is `aria-expanded="true"`, so they don't conflict.

- [ ] **Step 2: Verify the click toggle**

Run dev at desktop width. Tap (or click) the "Experiences" button without moving the mouse over it first. Expected: submenu opens, chevron rotates. Tap again: closes. Open it, then click somewhere else on the page: closes. Open it, then press Escape: closes and focus returns to the button.

- [ ] **Step 3: Verify the build**

Run: `npm run build`
Expected: exits 0.

- [ ] **Step 4: Commit**

```bash
git add src/scripts/header.ts
git commit -m "Toggle experiences dropdown on click, outside click, and Escape"
```

---

## Task 12: End-to-end check + cleanup

This is a verification task — no new code. It catches anything missed during the per-task checks.

- [ ] **Step 1: Build the site clean**

Run: `npm run build`
Expected: exits 0. No warnings about duplicate routes, missing imports, or Zod parse failures.

- [ ] **Step 2: Walk through every experiences URL in dev**

Run dev. For each URL below, confirm the page renders correctly and the header "Experiences" item is the dropdown:

- `/experiences/` → redirects to `/experiences/schedule`
- `/experiences/schedule` → schedule renders, day toggle works, no sticky page-nav above the schedule
- `/experiences/<each group slug>` → the right group renders (description + the layout-specific list)

- [ ] **Step 3: Verify nav state on each page**

On every `/experiences/*` page, confirm:
- The submenu item matching the current URL shows `aria-current="page"` styling (bold + uppercase).
- The top-level "Experiences" button is **not** a link (right-click → "Open in new tab" is unavailable or has no effect).

- [ ] **Step 4: Confirm the legacy artifacts are gone**

Run from `apps/frontend/`:
```sh
grep -rn "PageExperiences\|sticky-nav-height\|experiences-nav-height\|page-nav" src/
```
Expected: no output (all references removed).

- [ ] **Step 5: No commit**

Nothing to commit unless step 4 finds stragglers — in which case fix and commit them with a follow-up message.

---

## Self-review

**Spec coverage:**
- Routing (3 spec items: dynamic route, index redirect, exclusion from `[...link]`) → Tasks 4, 5, 6.
- `PageTemplate.astro` mapping removal → Task 7.
- `ExperiencesSchedule.astro` → Task 2.
- `ExperiencesGroup.astro` → Task 3.
- `src/lib/experiences.ts` shared schemas → Task 1.
- Delete `PageExperiences.astro` → Task 8.
- Header derivation + button + chevron + submenu (desktop) → Tasks 9, 10.
- Header mobile rendering → Task 10 step 3.
- `header.ts` click/outside/escape logic → Task 11.
- Removed sticky page-nav, IntersectionObserver, ResizeObserver, `--sticky-nav-height`, `scroll-margin-top`, `sections` array → all gone with Task 8 (file deletion). Leftover var declarations checked in Task 8 step 3 and again in Task 12 step 4.
- Reduced motion on chevron → Task 10 step 4 CSS.
- `aria-current="page"` on sub-items → Tasks 10 (rendering) + spec covers via `Astro.url.pathname` matching.

All spec items have a task.

**Placeholder scan:** No "TBD", "TODO", "implement later", or "add appropriate X". All code blocks are complete.

**Type consistency:**
- `Schedule`, `Group` types defined in `src/lib/experiences.ts` (Task 1) and used by `ExperiencesSchedule.astro` (Task 2), `ExperiencesGroup.astro` (Task 3), and `[slug].astro` (Task 4). Names consistent.
- `getExperiencesPage()` return shape `{ page, schedule, groups }` defined in Task 1 and consumed identically in Task 4 and Task 10.
- `experiencesSubItems` array shape `{ label, href }` defined and consumed within Task 10 only.
- `submenu-toggle` class + `#experiences-submenu` id defined in Task 10 markup, queried with the same selectors in Task 11.

No mismatches.

---

## Execution handoff

Plan complete and saved to `docs/superpowers/plans/2026-04-28-experiences-page-split.md`. Two execution options:

**1. Subagent-Driven (recommended)** — fresh subagent per task, review between tasks, fast iteration.

**2. Inline Execution** — execute tasks in this session using executing-plans, batch execution with checkpoints.

Which approach?
