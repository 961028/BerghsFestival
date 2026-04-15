import { defineCollection, type BaseSchema } from "astro:content";
import { z } from "astro/zod";

import { wpGet, wpGetAll } from "./lib/wp-api";
import { stripHtml } from "./lib/html";
import {
    intId,
    intReference,
    nullableIntId,
    nullableIntReference,
} from "./lib/schema";

const definePaginatedCollection = <S extends BaseSchema>(
    path: string,
    schema: S,
) =>
    defineCollection({
        schema,
        loader: {
            name: path,
            load: async ({ store, parseData }) => {
                store.clear();

                const items = await wpGetAll<Record<string, unknown>>(path, {
                    acf_format: "standard",
                });

                for (const item of items) {
                    const id = String(item.id);

                    const data = await parseData({ id, data: item });

                    store.set({ id, data });
                }
            },
        },
    });

const defineSingletonCollection = <S extends BaseSchema>(
    path: string,
    schema: S,
) =>
    defineCollection({
        schema,
        loader: {
            name: path,
            load: async ({ store, parseData }) => {
                const item = await wpGet<Record<string, unknown>>(path, {
                    acf_format: "standard",
                });

                const id = "0";

                const data = await parseData({ id, data: item });

                store.set({ id, data });
            },
        },
    });

const settings = defineSingletonCollection(
    "wp/v2/settings",
    z
        .object({
            title: z.string(),
            page_on_front: intReference("pages").nullable(),
        })
        .transform((item) => ({
            title: item.title,
            pageOnFront: item.page_on_front,
        })),
);

const menuLocations = defineCollection({
    schema: z
        .object({
            name: z.string().nonempty(),
            menu: nullableIntId(),
        })
        .transform((val) => ({
            id: val.name,
            menu: val.menu,
        })),
    loader: {
        name: "wp/v2/menu-locations",
        load: async ({ store, parseData }) => {
            store.clear();

            const items = await wpGet<Record<string, unknown>[]>(
                "wp/v2/menu-locations",
            );

            for (const item of Object.values(items)) {
                const id = String(item.name);

                const data = await parseData({ id, data: item });

                store.set({ id, data });
            }
        },
    },
});

const menuItems = definePaginatedCollection(
    "wp/v2/menu-items",
    z
        .object({
            id: intId(),
            menus: nullableIntId(),
            menu_order: z.int(),
            title: z.union([
                z.string(),
                z.object({ rendered: z.string().optional() }),
            ]),
            url: z.string(),
        })
        .transform((item) => ({
            id: item.id,
            menu: item.menus,
            order: item.menu_order,
            title:
                typeof item.title === "string"
                    ? item.title
                    : stripHtml(item.title.rendered ?? ""),
            url: item.url,
        })),
);

const media = definePaginatedCollection(
    "wp/v2/media",
    z
        .object({
            id: intId(),
            media_type: z.string(),
            source_url: z.string(),
            alt_text: z.string(),
            media_details: z
                .object({
                    width: z.number().optional(),
                    height: z.number().optional(),
                })
                .optional(),
        })
        .transform((item) => ({
            id: item.id,
            type: item.media_type,
            sourceUrl: item.source_url,
            altText: item.alt_text,
            width: item.media_details?.width,
            height: item.media_details?.height,
        })),
);

const pages = definePaginatedCollection(
    "wp/v2/pages",
    z
        .object({
            id: intId(),
            slug: z.string().nonempty(),
            parent: nullableIntReference("pages"),
            template: z.string().nullable(),
            title: z.object({ rendered: z.string() }),
            content: z.object({ rendered: z.string() }),
            acf: z.preprocess(
                (val) => (Array.isArray(val) ? {} : val),
                z.record(z.string(), z.unknown()),
            ),
        })
        .transform((item) => ({
            id: item.id,
            slug: item.slug,
            parent: item.parent,
            template: item.template,
            title: stripHtml(item.title.rendered),
            content: { html: item.content.rendered },
            acf: item.acf,
        })),
);

const projects = definePaginatedCollection(
    "wp/v2/projects",
    z
        .object({
            id: intId(),
            slug: z.string().nonempty(),
            title: z.object({ rendered: z.string() }),
            acf: z.object({
                company: z.string(),
                image: nullableIntReference("media"),
                video: z.string().nullable(),
                team_members: z.array(
                    z.object({
                        name: z.string(),
                        class: z.string(),
                    }),
                ),
                "content-company": z.string(),
                "content-background": z.string(),
                "content-solution": z.string(),
            }),
        })
        .transform((item) => ({
            id: item.id,
            slug: item.slug,
            title: stripHtml(item.title.rendered),
            company: item.acf.company,
            image: item.acf.image,
            video: item.acf.video ?? null,
            teamMembers: item.acf.team_members,
            contentSections: [
                {
                    title: "The Company",
                    content: { html: item.acf["content-company"] },
                },
                {
                    title: "Background",
                    content: { html: item.acf["content-background"] },
                },
                {
                    title: "Solution",
                    content: { html: item.acf["content-solution"] },
                },
            ],
        })),
);

const sponsors = definePaginatedCollection(
    "app/v1/sponsors",
    z.object({
        id: intId(),
        name: z.string(),
        image: intReference("media"),
        url: z.string(),
    }),
);

const contact = defineSingletonCollection(
    "app/v1/contact",
    z
        .object({
            address: z.string(),
            phone: z.string(),
            social_services: z.array(
                z.object({
                    icon: z.string(),
                    label: z.string(),
                    url: z.string(),
                }),
            ),
        })
        .transform((item) => ({
            address: item.address,
            phone: item.phone,
            socialServices: item.social_services,
        })),
);

const iq = defineSingletonCollection(
    "app/v1/iq",
    z
        .object({
            title: z.string(),
            content: z.string(),
        })
        .transform((item) => ({
            title: item.title,
            content: { html: item.content.trim() },
        })),
);

const home = defineSingletonCollection(
    "app/v1/home",
    z
        .object({
            meta_title: z.string(),
            manifest: z.string(),
            about: z.string(),
        })
        .transform((item) => ({
            metaTitle: item.meta_title,
            manifest: { html: item.manifest },
            about: { html: item.about },
        })),
);

export const collections = {
    settings,
    menuLocations,
    menuItems,
    media,
    pages,
    projects,
    sponsors,
    contact,
    iq,
    home,
};
