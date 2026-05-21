import { getCollection, type CollectionEntry } from "astro:content";
import { z } from "astro/zod";

import { nullableIntReference, repeater } from "./schema";

const SlotsRepeater = repeater(
    z
        .object({
            day: z.string().nullable().optional(),
            start_time: z.string().optional(),
            end_time: z.string().optional(),
            location: z.string().nullable().optional(),
        })
        .transform((slot) => ({
            day: slot.day || null,
            startTime: slot.start_time ?? "",
            endTime: slot.end_time ?? "",
            location: slot.location || null,
        })),
);

const ArtistsRepeater = repeater(
    z
        .object({
            name: z.string(),
            slug: z.string().optional(),
            image: nullableIntReference("media"),
            description: z.string().optional(),
            url: z.string().optional(),
            social_url: z.string().optional(),
            slots: SlotsRepeater,
        })
        .transform((item) => ({
            slug: item.slug || z.string().slugify().parse(item.name),
            name: item.name,
            image: item.image,
            description: { html: item.description ?? "" },
            url: item.url || null,
            socialUrl: item.social_url || null,
            slots: item.slots,
        })),
);

export type Slot = z.infer<typeof SlotsRepeater>[number];
export type Artist = z.infer<typeof ArtistsRepeater>[number];

// Appends -2, -3, … to keep all slugs unique when two artists share a name.
function deduplicateSlugs(artists: Artist[]): Artist[] {
    const seen = new Map<string, number>();
    return artists.map((artist) => {
        const count = (seen.get(artist.slug) ?? 0) + 1;
        seen.set(artist.slug, count);
        if (count === 1) return artist;
        const slug = `${artist.slug}-${count}`;
        seen.set(slug, 0);
        return { ...artist, slug };
    });
}

const ScheduleRepeater = repeater(
    z.object({
        day: z.string(),
        events: repeater(
            z
                .object({
                    start_time: z.string().optional(),
                    title: z.string().optional(),
                    link_url: z.string().optional(),
                })
                .transform((item) => ({
                    startTime: item.start_time ?? "",
                    title: item.title ?? "",
                    linkUrl: item.link_url || null,
                })),
        ),
    }),
);

export type ScheduleEvent = {
    startTime: string;
    title: string;
    href: string | null;
};

export type ScheduleDay = {
    day: string;
    date: string | null;
    events: ScheduleEvent[];
};

export type Schedule = ScheduleDay[];

const SimpleItems = repeater(
    z
        .object({
            name: z.string(),
            location: z.string().nullable().optional(),
            image: nullableIntReference("media"),
            description: z.string().transform((val) => ({ html: val })),
            url: z.string().optional(),
            social_url: z.string().optional(),
        })
        .transform((item) => ({
            name: item.name,
            location: item.location || null,
            image: item.image,
            description: item.description,
            url: item.url || null,
            socialUrl: item.social_url || null,
        })),
);

export type SimpleItem = z.infer<typeof SimpleItems>[number];

const LocationsRepeater = repeater(
    z.object({
        location: z.string().nullable().optional(),
    }),
);

const FoodItems = repeater(
    z
        .object({
            name: z.string(),
            image: nullableIntReference("media"),
            description: z.string().transform((val) => ({ html: val })),
            locations: LocationsRepeater.optional(),
        })
        .transform((item) => ({
            name: item.name,
            image: item.image,
            description: item.description,
            locations: (item.locations ?? [])
                .map((row) => row.location)
                .filter((l): l is string => !!l),
        })),
);

export type FoodItem = z.infer<typeof FoodItems>[number];

const FestivalDaysSchema = z
    .array(
        z.object({
            abbr: z.string(),
            date: z.string(),
            hours: z.string(),
        }),
    )
    .catch([]);

async function getFestivalDays(): Promise<string[]> {
    const settings = (await getCollection("settings"))[0]?.data;
    if (!settings?.pageOnFront) return [];
    const pages = await getCollection("pages");
    const home = pages.find((p) => p.data.id === settings.pageOnFront!.id);
    if (!home) return [];
    return FestivalDaysSchema.parse(home.data.acf.festival_days).map(
        (d) => d.date,
    );
}

async function findPageByTemplate(
    template: string,
): Promise<CollectionEntry<"pages"> | null> {
    const pages = await getCollection("pages");
    return pages.find((page) => page.data.template === template) ?? null;
}

export async function getSchedulePage() {
    const page = await findPageByTemplate("page-schedule.php");

    if (!page) {
        return null;
    }

    const raw = ScheduleRepeater.parse(page.data.acf.schedule);
    const festivalDates = await getFestivalDays();

    const schedule: Schedule = raw.map((dayRow, i) => ({
        day: dayRow.day,
        date: festivalDates[i] ?? null,
        events: dayRow.events.map((event) => ({
            startTime: event.startTime,
            title: event.title,
            href: event.linkUrl,
        })),
    }));

    return {
        page,
        schedule,
    };
}

export async function getScheduleDays(): Promise<string[]> {
    const data = await getSchedulePage();
    return data ? data.schedule.map((row) => row.day) : [];
}

export async function getMusicPage() {
    const page = await findPageByTemplate("page-experience-music.php");

    if (!page) {
        return null;
    }

    const artists = deduplicateSlugs(
        ArtistsRepeater.parse(page.data.acf.artists),
    );

    return {
        page,
        description: page.data.content,
        artists,
    };
}

export async function getInstallationsPage() {
    const page = await findPageByTemplate("page-experience-installations.php");

    if (!page) {
        return null;
    }

    return {
        page,
        description: page.data.content,
        items: SimpleItems.parse(page.data.acf.items),
    };
}

export async function getFoodPage() {
    const page = await findPageByTemplate("page-experience-food.php");

    if (!page) {
        return null;
    }

    return {
        page,
        description: page.data.content,
        items: FoodItems.parse(page.data.acf.items),
    };
}
