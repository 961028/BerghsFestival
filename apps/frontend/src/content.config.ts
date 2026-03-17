import { defineCollection, reference, type BaseSchema } from "astro:content";
import { z } from "astro/zod";
import {
    type WP_REST_API_Menu_Item,
    type WP_REST_API_Menu_Locations,
    type WP_REST_API_Settings,
} from "wp-types";

import { type Media, wpGet, wpGetAll, type Post } from "./lib/wp-api";
import { stripHtml } from "./lib/html";

type WithId = {
    id: string;
};

type WithoutId<T> = Omit<T, "id">;

type PaginatedCollectionConfig<I, E extends WithId, S extends BaseSchema> = {
    path: string;
    schema: S;
    mapItemToEntry(item: I): E;
};

const definePaginatedCollection = <I, E extends WithId, S extends BaseSchema>({
    path,
    schema,
    mapItemToEntry,
}: PaginatedCollectionConfig<I, E, S>) =>
    defineCollection({
        schema,
        loader: {
            name: path,
            load: async ({ store, parseData }) => {
                store.clear();

                const items = await wpGetAll<I>(path);

                for (const item of items) {
                    const rawData = mapItemToEntry(item);

                    const id = String(rawData.id);

                    const data = await parseData({
                        id: id,
                        data: rawData,
                    });

                    store.set({ id, data });
                }
            },
        },
    });

type SingletonCollectionConfig<
    I,
    E extends WithoutId<E>,
    S extends BaseSchema,
> = {
    path: string;
    schema: S;
    mapItemToEntry(item: I): E;
};

const defineSingletonCollection = <
    I,
    E extends WithoutId<E>,
    S extends BaseSchema,
>({
    path,
    schema,
    mapItemToEntry,
}: SingletonCollectionConfig<I, E, S>) =>
    defineCollection({
        schema,
        loader: {
            name: path,
            load: async ({ store, parseData }) => {
                const item = await wpGet<I>(path);

                const rawData = mapItemToEntry(item);

                const id = "0";

                const data = await parseData({
                    id: id,
                    data: rawData,
                });

                store.set({ id, data });
            },
        },
    });

const settings = defineSingletonCollection({
    path: "wp/v2/settings",
    schema: z.object({
        title: z.string(),
        homePage: reference("pages"),
    }),
    mapItemToEntry: (item: WP_REST_API_Settings) => ({
        title: item.title,
        homePage: item.page_on_front ? String(item.page_on_front) : null,
    }),
});

const menuLocations = defineCollection({
    schema: z.object({
        id: z.string(),
        menu: z.number(),
    }),
    loader: {
        name: "wp/v2/menu-locations",
        load: async ({ store, parseData }) => {
            store.clear();

            const items = await wpGet<WP_REST_API_Menu_Locations>(
                "wp/v2/menu-locations",
            );

            for (const item of Object.values(items)) {
                const rawData = {
                    id: item.name,
                    menu: item.menu,
                };

                const data = await parseData({ id: rawData.id, data: rawData });

                store.set({ id: data.id, data });
            }
        },
    },
});

const menuItems = definePaginatedCollection({
    path: "wp/v2/menu-items",
    schema: z.object({
        id: z.string(),
        menu: z.number(),
        order: z.number(),
        title: z.string(),
        url: z.string(),
    }),
    mapItemToEntry: (item: WP_REST_API_Menu_Item) => ({
        id: String(item.id),
        menu: item.menus,
        order: item.menu_order,
        title:
            typeof item.title === "string"
                ? item.title
                : stripHtml(item.title.rendered ?? ""),
        url: item.url,
    }),
});

const media = definePaginatedCollection({
    path: "wp/v2/media",
    schema: z.object({
        id: z.string(),
        type: z.string(),
        sourceUrl: z.string(),
        altText: z.string(),
    }),
    mapItemToEntry: (item: Media) => ({
        id: String(item.id),
        type: item.media_type,
        sourceUrl: item.source_url,
        altText: item.alt_text,
    }),
});

const pages = definePaginatedCollection({
    path: "wp/v2/pages",
    schema: z.object({
        id: z.coerce.string(),
        slug: z.string(),
        parent: reference("pages").nullable(),
        template: z.string().nullable().default(null),
        title: z.string(),
        content: z.object({
            html: z.string(),
        }),
        acf: z.preprocess(
            (val) => (Array.isArray(val) ? {} : val),
            z.record(z.string(), z.unknown()),
        ),
    }),
    mapItemToEntry: (item: Post) => ({
        id: String(item.id),
        slug: item.slug,
        parent: item.parent ? String(item.parent) : null,
        template: item.template || undefined,
        title: stripHtml(item.title.rendered),
        content: {
            html: item.content.rendered,
        },
        acf: item.acf,
    }),
});

const projects = definePaginatedCollection({
    path: "wp/v2/projects",
    schema: z.object({
        id: z.string(),
        slug: z.string(),
        title: z.string(),
        company: z.string(),
        image: reference("media").nullable(),
        video: z.string().nullable(),
        teamMembers: z.array(
            z.object({
                name: z.string(),
                class: z.string(),
            }),
        ),
        contentSections: z.array(
            z.object({
                title: z.string(),
                content: z.object({
                    html: z.string(),
                }),
            }),
        ),
    }),
    mapItemToEntry: (item: Post) => ({
        id: String(item.id),
        slug: item.slug,
        title: stripHtml(item.title.rendered),
        company: item.acf.company,
        image: item.acf.image ? String(item.acf.image) : null,
        video: item.acf.video,
        teamMembers: item.acf.team_members,
        contentSections: [
            {
                title: "The Company",
                content: {
                    html: item.acf["content-company"],
                },
            },
            {
                title: "Background",
                content: {
                    html: item.acf["content-background"],
                },
            },
            {
                title: "Solution",
                content: {
                    html: item.acf["content-solution"],
                },
            },
        ],
    }),
});

const sponsors = definePaginatedCollection({
    path: "app/v1/sponsors",
    schema: z.object({
        id: z.string(),
        name: z.string(),
        image: reference("media"),
        url: z.string(),
    }),
    mapItemToEntry: (item: Record<string, unknown>) => ({
        id: String(item.id),
        name: item.name,
        image: String(item.image),
        url: item.url,
    }),
});

const contact = defineSingletonCollection({
    path: "app/v1/contact",
    schema: z.object({
        address: z.string(),
        phone: z.string(),
        socialServices: z.array(
            z.object({
                icon: z.string(),
                label: z.string(),
                url: z.string(),
            }),
        ),
    }),
    mapItemToEntry: (item: Record<string, unknown>) => ({
        address: item.address,
        phone: item.phone,
        socialServices: item.social_services,
    }),
});

const iq = defineSingletonCollection({
    path: "app/v1/iq",
    schema: z.object({
        title: z.string(),
        content: z.object({
            html: z.string(),
        }),
    }),
    mapItemToEntry: (item: Record<string, unknown>) => ({
        title: item.title,
        content: {
            html: item.content,
        },
    }),
});

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
};
