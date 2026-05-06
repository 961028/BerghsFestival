import { getCollection, type CollectionEntry } from "astro:content";
import { z } from "astro/zod";

import { nullableIntReference, repeater } from "./schema";

const ScheduleRepeater = repeater(
    z.object({
        day: z.string(),
        events: repeater(
            z
                .object({
                    music_item: z
                        .union([z.number(), z.string(), z.null(), z.literal(false)])
                        .transform((v) => (v === false ? null : v))
                        .optional(),
                    start_time: z.string().optional(),
                    title: z.string().optional(),
                })
                .transform((item) => ({
                    musicItemId:
                        item.music_item !== null &&
                        item.music_item !== undefined &&
                        item.music_item !== ""
                            ? String(item.music_item)
                            : null,
                    startTime: item.start_time ?? "",
                    title: item.title ?? "",
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

export type MusicItem = CollectionEntry<"musicItems">["data"];

async function findPageByTemplate(
    template: string,
): Promise<CollectionEntry<"pages"> | null> {
    const pages = await getCollection("pages");
    return pages.find((page) => page.data.template === template) ?? null;
}

async function findPageBySlug(
    slug: string,
): Promise<CollectionEntry<"pages"> | null> {
    const pages = await getCollection("pages");
    return pages.find((page) => page.data.slug === slug) ?? null;
}

async function getMusicItems(): Promise<CollectionEntry<"musicItems">[]> {
    const items = await getCollection("musicItems");
    return [...items].sort((a, b) => a.data.order - b.data.order);
}

export async function getSchedulePage() {
    const page = await findPageByTemplate("page-schedule.php");

    if (!page) {
        return null;
    }

    const raw = ScheduleRepeater.parse(page.data.acf.schedule);
    const musicItems = await getMusicItems();
    const musicById = new Map(musicItems.map((item) => [item.id, item]));

    const schedule: Schedule = raw.map((dayRow) => ({
        day: dayRow.day,
        events: dayRow.events.map((event) => {
            const linked = event.musicItemId
                ? musicById.get(event.musicItemId)
                : undefined;

            if (linked) {
                return {
                    startTime: linked.data.startTime ?? event.startTime,
                    title: linked.data.name,
                    href: `/music#${linked.data.slug}`,
                };
            }

            return {
                startTime: event.startTime,
                title: event.title,
                href: null,
            };
        }),
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

    const items = await getMusicItems();

    return {
        page,
        description: page.data.content,
        items: items.map((item) => item.data),
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
        items: SimpleItems.parse(page.data.acf.items),
    };
}

export async function getProjectsPage() {
    return findPageBySlug("projects");
}
