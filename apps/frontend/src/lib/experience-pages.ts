import { getCollection, type CollectionEntry } from "astro:content";
import { z } from "astro/zod";

import { nullableIntReference, repeater } from "./schema";

const wysiwyg = z
    .string()
    .nullable()
    .catch(null)
    .transform((val) => (val ? { html: val } : null));

const ScheduleRepeater = repeater(
    z.object({
        day: z.string(),
        events: repeater(
            z
                .object({
                    start_time: z.string(),
                    title: z.string(),
                    url: z.string().optional(),
                })
                .transform((item) => ({
                    startTime: item.start_time,
                    title: item.title,
                    url: item.url,
                })),
        ),
    }),
);

export type Schedule = z.infer<typeof ScheduleRepeater>;

const MusicItems = repeater(
    z
        .object({
            name: z.string(),
            day: z.string().nullable().optional(),
            start_time: z.string().nullable().optional(),
            location: z.string().nullable().optional(),
            image: nullableIntReference("media"),
            description: z.string().transform((val) => ({ html: val })),
            url: z.string().optional(),
        })
        .transform((item) => ({
            name: item.name,
            day: item.day || null,
            startTime: item.start_time || null,
            location: item.location || null,
            image: item.image,
            description: item.description,
            url: item.url,
        })),
);

const SimpleItems = repeater(
    z
        .object({
            name: z.string(),
            location: z.string().nullable().optional(),
            image: nullableIntReference("media"),
            description: z.string().transform((val) => ({ html: val })),
            url: z.string().optional(),
        })
        .transform((item) => ({
            name: item.name,
            location: item.location || null,
            image: item.image,
            description: item.description,
            url: item.url,
        })),
);

export type MusicItem = z.infer<typeof MusicItems>[number];
export type SimpleItem = z.infer<typeof SimpleItems>[number];

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

    return {
        page,
        description: wysiwyg.parse(page.data.acf.description),
        schedule: ScheduleRepeater.parse(page.data.acf.schedule),
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

    return {
        page,
        description: wysiwyg.parse(page.data.acf.description),
        items: MusicItems.parse(page.data.acf.items),
    };
}

export async function getInstallationsPage() {
    const page = await findPageByTemplate("page-experience-installations.php");

    if (!page) {
        return null;
    }

    return {
        page,
        description: wysiwyg.parse(page.data.acf.description),
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
        description: wysiwyg.parse(page.data.acf.description),
        items: SimpleItems.parse(page.data.acf.items),
    };
}
