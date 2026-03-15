import { getEntry, type CollectionEntry, type CollectionKey, type DataEntryMap } from 'astro:content';

export async function resolvePageLink(page: CollectionEntry<"pages">) {
    const slugs = [page.data.slug];

    let parent = page.data.parent

    while (parent) {
        const entry = await getEntry(parent);

        if (!entry) {
            break;
        }

        slugs.unshift(entry.data.slug);

        parent = entry.data.parent;
    }

    return slugs.join('/');
}
