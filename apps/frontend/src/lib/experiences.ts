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
                z
                    .object({
                        name: z.string(),
                        day: z.string().nullable().optional(),
                        start_time: z.string().nullable().optional(),
                        location: z.string().nullable().optional(),
                        image: nullableIntReference("media"),
                        description: z
                            .string()
                            .transform((val) => ({ html: val })),
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
    scheduleTitle: string;
    scheduleDescription: { html: string } | null;
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

    const description = z
        .string()
        .nullable()
        .catch(null)
        .parse(page.data.acf.schedule_description);

    return {
        page,
        scheduleTitle: z
            .string()
            .catch("Schedule")
            .parse(page.data.acf.schedule_title),
        scheduleDescription: description ? { html: description } : null,
        schedule: ScheduleSchema.parse(page.data.acf.schedule),
        groups: GroupsSchema.parse(page.data.acf.groups),
    };
}
