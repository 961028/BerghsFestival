// ── Config ────────────────────────────────────────────────────────────────────
// Signal colors used in all flicker/strobe effects. Add, remove, or change
// colors here — no other file needs to be touched.
export const ACCENTS = [
    "#00ff00", // green
    "#ff0000", // red
    "#1e00ff", // blue
    "#eeff00", // yellow
];

export type Triple = { primary: string; secondary: string; tertiary: string };

function pickDifferentFrom(...excluded: string[]): string {
    const choices = ACCENTS.filter((c) => !excluded.includes(c));
    return choices[Math.floor(Math.random() * choices.length)];
}

export function pickTriple(excludePrimary = ""): Triple {
    const primary = pickDifferentFrom(excludePrimary);
    const secondary = pickDifferentFrom(primary);
    const tertiary = pickDifferentFrom(primary, secondary);
    return { primary, secondary, tertiary };
}
