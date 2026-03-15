import { defineCollection, type BaseSchema } from 'astro:content';
import { z } from 'astro/zod';

import { Media, wpGet, wpGetAll, type Post } from './lib/wp-api';
import { stripHtml } from './lib/html';
import type { WP_REST_API_Attachment } from 'wp-types';

type PostCollectionConfig = {
    restBase: string,
    schema: BaseSchema,
    mapPostToEntry?(post: Post): Record<string, unknown>,
};

const definePostCollection = ({ restBase, schema, mapPostToEntry }: PostCollectionConfig) => defineCollection({
    schema,
    loader: {
        name: `posts/${restBase}`,
        load: async ({ store, parseData }) => {
            const items = await wpGetAll<Post>(`wp/v2/${restBase}`);

            for (const item of items) {
                const id = String(item.id);

                const rawData = mapPostToEntry ? mapPostToEntry(item) : item;

                const data = await parseData({
                    id: id,
                    data: rawData,
                });

                store.set({ id, data, });
            }
        },
    },
});

const media = defineCollection({
    schema: z.object({
        id: z.number(),
        type: z.string(),
        sourceUrl: z.string(),
        altText: z.string(),
    }),
    loader: {
        name: 'media',
        load: async ({store, parseData}) => {
            const items = await wpGetAll<Media>('wp/v2/media')

            for (const item of items) {
                const id = String(item.id);

                const rawData = {
                    id: item.id,
                    type: item.media_type,
                    sourceUrl: item.source_url,
                    altText: item.alt_text,
                };

                const data = await parseData({
                    id: id,
                    data: rawData,
                });

                store.set({ id, data, });
            }
        },
    },
});

const pages = definePostCollection({
    restBase: 'pages',
    schema: z.object({
        id: z.number(),
        slug: z.string(),
        parent: z.number().nullable(),
        title: z.string(),
    }),
    mapPostToEntry: (post) => ({
        id: post.id,
        slug: post.slug,
        parent: post.parent,
        title: stripHtml(post.title.rendered),
    }),
});

const projects = definePostCollection({
    restBase: 'projects',
    schema: z.object({
        id: z.number(),
        slug: z.string(),
        title: z.string(),
        company: z.string(),
        image: z.number().nullable(),
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
    mapPostToEntry: (post) => ({
        id: post.id,
        slug: post.slug,
        title: stripHtml(post.title.rendered),
        company: post.acf.company,
        image: post.acf.image,
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

export const collections = {
    media,
    pages,
    projects,
};
