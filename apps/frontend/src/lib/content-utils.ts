import { getEntry, type CollectionEntry, type DataEntryMap } from 'astro:content';

export async function resolveHierarchicalPostLink(collection: keyof DataEntryMap, postId: number) {
    const slugs = [];

    let parentId = postId;

    while (parentId) {
        const entry = await getEntry(collection, String(parentId));

        slugs.unshift(entry.data.slug);

        parentId = entry.data.parent;
    }

    return slugs.join('/');
}
