import { defineCollection, type BaseSchema } from "astro:content";
import { z } from "astro/zod";
import { decodeHTML } from "entities";

import { wpGet, wpGetAll } from "./lib/wp-api";
import { stripHtml } from "./lib/html";
import {
    intId,
    intReference,
    nullableIntId,
    nullableIntReference,
    repeater,
} from "./lib/schema";

const definePaginatedWpCollection = <S extends BaseSchema>(
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

const defineSingletonWpCollection = <S extends BaseSchema>(
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

const settings = defineSingletonWpCollection(
    "wp/v2/settings",
    z
        .object({
            title: z.string(),
            description: z.string(),
            page_on_front: intReference("pages").nullable(),
        })
        .transform((item) => ({
            title: item.title,
            description: decodeHTML(item.description),
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

const menuItems = definePaginatedWpCollection(
    "wp/v2/menu-items",
    z
        .object({
            id: intId(),
            menus: nullableIntId(),
            menu_order: z.int(),
            parent: z.int(),
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
            parent: item.parent ? String(item.parent) : null,
            title:
                typeof item.title === "string"
                    ? item.title
                    : stripHtml(item.title.rendered ?? ""),
            url: item.url,
        })),
);

const media = definePaginatedWpCollection(
    "wp/v2/media",
    z
        .object({
            id: intId(),
            media_type: z.string(),
            mime_type: z.string().optional(),
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
            mimeType: item.mime_type,
            sourceUrl: item.source_url,
            altText: item.alt_text,
            width: item.media_details?.width,
            height: item.media_details?.height,
        })),
);

const pages = definePaginatedWpCollection(
    "wp/v2/pages",
    z
        .object({
            id: intId(),
            slug: z.string().nonempty(),
            parent: nullableIntReference("pages"),
            template: z.string().nullable(),
            title: z.object({ rendered: z.string() }),
            featured_media: nullableIntReference("media"),
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
            metaDescription: z.string().parse(item.acf.meta_description),
            image: item.featured_media,
            content: { html: item.content.rendered },
            acf: item.acf,
        })),
);

const PROJECTS_API_URL = "https://www.berghs.se/api/student-projects";

const projects = defineCollection({
    schema: z
        .object({
            id: z.int().positive(),
            project_type: z.enum(["regular", "solo"]),
            title: z.string(),
            company_name: z.string().nullable().optional(),
            image: z.string().nullable().optional(),
            team_members: z
                .array(
                    z.object({
                        name: z.string(),
                        class: z.string(),
                    }),
                )
                .catch([]),
            content_company: z.string().nullable(),
            content_background: z.string().nullable(),
            content_solution: z.string().nullable(),
            video: z.string().nullable(),
        })
        .transform((item) => ({
            id: String(item.id),
            slug: z.string().slugify().parse(item.title),
            type: item.project_type,
            title: item.title,
            metaDescription: "",
            company: item.company_name ?? "",
            image: item.image ? { url: item.image } : null,
            video: item.video ?? null,
            teamMembers: item.team_members.map((m) => ({
                name: m.name,
                class: m.class,
            })),
            contentSections: [
                {
                    title: "The Company",
                    content: { html: item.content_company ?? "" },
                },
                {
                    title: "Background",
                    content: { html: item.content_background ?? "" },
                },
                {
                    title: "Solution",
                    content: { html: item.content_solution ?? "" },
                },
            ],
        })),
    loader: {
        name: "berghs/student-projects",
        load: async ({ store, parseData }) => {
            store.clear();

            const response = await fetch(PROJECTS_API_URL);
            if (!response.ok) {
                throw new Error(
                    `GET ${PROJECTS_API_URL} failed — ${response.status} ${response.statusText}`,
                );
            }
            const items = z
                .array(z.record(z.string(), z.unknown()))
                .parse(await response.json());

            for (const item of items) {
                const id = String(item.id);

                const data = await parseData({ id, data: item });

                store.set({ id, data });
            }
        },
    },
});

const seo = defineSingletonWpCollection(
    "app/v1/seo",
    z
        .object({
            meta_description: z.string(),
            og_image: nullableIntReference("media"),
        })
        .transform((item) => ({
            metaDescription: item.meta_description,
            ogImage: item.og_image,
        })),
);

const sponsors = definePaginatedWpCollection(
    "app/v1/sponsors",
    z.object({
        id: intId(),
        name: z.string(),
        image: intReference("media"),
        url: z.string(),
    }),
);

const contact = defineSingletonWpCollection(
    "app/v1/contact",
    z
        .object({
            name: z.string(),
            phone: z.string(),
            email: z.string(),
            social_services: repeater(
                z.object({
                    icon: z.string(),
                    label: z.string(),
                    url: z.string(),
                }),
            ),
        })
        .transform((item) => ({
            name: item.name,
            phone: item.phone,
            email: item.email,
            socialServices: item.social_services,
        })),
);

const footerTextBlockSchema = z
    .object({
        title: z.string(),
        content: z.string(),
    })
    .transform((item) => ({
        title: item.title,
        content: { html: item.content.trim() },
    }));

const iq = defineSingletonWpCollection("app/v1/iq", footerTextBlockSchema);

const photoNotice = defineSingletonWpCollection(
    "app/v1/photo-notice",
    footerTextBlockSchema,
);

export const collections = {
    settings,
    menuLocations,
    menuItems,
    media,
    pages,
    projects,
    seo,
    sponsors,
    contact,
    iq,
    photoNotice,
};
