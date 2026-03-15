import { defineCollection, reference, type BaseSchema } from 'astro:content';
import { z } from 'astro/zod';
import type { WP_REST_API_Settings } from 'wp-types';

import { type Media, wpGet, wpGetAll, type Post } from './lib/wp-api';
import { stripHtml } from './lib/html';

type WithId = {
    id: number,
};

type WithoutId<T> = Omit<T, 'id'>;

type PaginatedCollectionConfig<I, E extends WithId, S extends BaseSchema> = {
    path: string,
    schema: S,
    mapItemToEntry(post: I): E,
};

const definePaginatedCollection = <I, E extends WithId, S extends BaseSchema>({ path, schema, mapItemToEntry }: PaginatedCollectionConfig<I, E, S>) => defineCollection({
    schema,
    loader: {
        name: path,
        load: async ({ store, parseData }) => {
            const items = await wpGetAll<I>(path);

            for (const item of items) {
                const rawData = mapItemToEntry(item);

                const id = String(rawData.id);

                const data = await parseData({
                    id: id,
                    data: rawData,
                });

                store.set({ id, data, });
            }
        },
    },
});

type SingletonCollectionConfig<I, E extends WithoutId<E>, S extends BaseSchema> = {
    path: string,
    schema: S,
    mapItemToEntry(item: I): E,
};

const defineSingletonCollection = <I, E extends WithoutId<E>, S extends BaseSchema>({ path, schema, mapItemToEntry }: SingletonCollectionConfig<I, E, S>) => defineCollection({
    schema,
    loader: {
        name: path,
        load: async ({ store, parseData }) => {
            const item = await wpGet<I>(path);

            const rawData = mapItemToEntry(item);

            const id = '0';

            const data = await parseData({
                id: id,
                data: rawData,
            });

            store.set({ id, data, });
        }
    }
});

const settings = defineSingletonCollection({
    path: 'wp/v2/settings',
    schema: z.object({
        title: z.string(),
    }),
    mapItemToEntry: (item: WP_REST_API_Settings) => ({
        title: item.title,
    }),
})

const media = definePaginatedCollection({
    path: 'wp/v2/media',
    schema: z.object({
        id: z.number(),
        type: z.string(),
        sourceUrl: z.string(),
        altText: z.string(),
    }),
    mapItemToEntry: (post: Media) => ({
        id: post.id,
        type: post.media_type,
        sourceUrl: post.source_url,
        altText: post.alt_text,
    }),
});

const pages = definePaginatedCollection({
    path: 'wp/v2/pages',
    schema: z.object({
        id: z.number(),
        slug: z.string(),
        parent: reference('pages').nullable(),
        title: z.string(),
    }),
    mapItemToEntry: (post: Post) => ({
        id: post.id,
        slug: post.slug,
        parent: post.parent ? String(post.parent) : null,
        title: stripHtml(post.title.rendered),
    }),
});

const projects = definePaginatedCollection({
    path: 'wp/v2/projects',
    schema: z.object({
        id: z.number(),
        slug: z.string(),
        title: z.string(),
        company: z.string(),
        image: reference('media').nullable(),
        video: z.string().nullable(),
        teamMembers: z.array(z.object({
            name: z.string(),
            class: z.string(),
        })),
        contentSections: z.array(z.object({
            title: z.string(),
            content: z.object({
                html: z.string(),
            }),
        }))
    }),
    mapItemToEntry: (post: Post) => ({
        id: post.id,
        slug: post.slug,
        title: stripHtml(post.title.rendered),
        company: post.acf.company,
        image: post.acf.image ? String(post.acf.image) : null,
        video: post.acf.video,
        teamMembers: post.acf.team_members,
        contentSections: [
            {
                title: 'The Company',
                content: {
                    html: post.acf['content-company'],
                },
            },
            {
                title: 'Background',
                content: {
                    html: post.acf['content-background'],
                },
            },
            {
                title: 'Solution',
                content: {
                    html: post.acf['content-solution'],
                },
            },
        ]
    }),
});

const sponsors = definePaginatedCollection({
    path: 'app/v1/sponsors',
    schema: z.object({
        id: z.number(),
        name: z.string(),
        image: reference('media'),
        url: z.string(),
    }),
    mapItemToEntry: (item: Record<string, unknown>) => ({
        id: item.id as number,
        name: item.name,
        image: String(item.image),
        url: item.url,
    }),
});

export const collections = {
    settings,
    media,
    pages,
    projects,
    sponsors,
};
