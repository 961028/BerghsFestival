import { reference, type DataEntryMap } from "astro:content";
import z from "astro/zod";

export function intId() {
    return z.int().positive().pipe(z.coerce.string());
}

export function nullableIntId() {
    return z.preprocess(
        (v) => (v === 0 || v === false ? null : v),
        z
            .int()
            .positive()
            .nullable()
            .pipe(z.coerce.string())
            .transform((v) => (v === "null" ? null : v)),
    );
}

export function intReference<C extends keyof DataEntryMap>(collection: C) {
    return intId().pipe(reference(collection));
}

export function nullableIntReference<C extends keyof DataEntryMap>(
    collection: C,
) {
    return nullableIntId().pipe(reference(collection).nullable());
}
