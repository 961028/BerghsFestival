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
    scheduleTitle: string;
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
        scheduleTitle: z
            .string()
            .catch("Schedule")
            .parse(page.data.acf.schedule_title),
        schedule: ScheduleSchema.parse(page.data.acf.schedule),
        groups: GroupsSchema.parse(page.data.acf.groups),
    };
}
