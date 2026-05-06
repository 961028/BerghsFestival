// ── Config ────────────────────────────────────────────────────────────────────
// Signal colors used in all flicker/strobe effects. Add, remove, or change
// colors here — no other file needs to be touched.
export const ACCENTS = ["#00ff00", "#ff0000", "#00FFFF", "#eeff00", "#ff00d9"];

// Milliseconds between color swaps during hover cycling and strobe effects.
// All interactive accent effects share this interval for visual consistency.
export const CYCLE_INTERVAL_MS = 120;

export type Triple = { primary: string; secondary: string; tertiary: string };

export function pickDifferentFrom(...excluded: string[]): string {
    const choices = ACCENTS.filter((c) => !excluded.includes(c));
    return choices[Math.floor(Math.random() * choices.length)];
}

export function pickTriple(excludePrimary = ""): Triple {
    const primary = pickDifferentFrom(excludePrimary);
    const secondary = pickDifferentFrom(primary);
    const tertiary = pickDifferentFrom(primary, secondary);
    return { primary, secondary, tertiary };
}

// Returns a stateful picker that enforces a minimum gap between repeats.
// `gap = 2` means at least 2 different colors must appear before a color
// can be chosen again, making sequences feel random rather than repetitive.
export function makeColorPicker(gap = 2): () => string {
    const history: string[] = [];
    return function pick(): string {
        const color = pickDifferentFrom(...history.slice(-gap));
        history.push(color);
        return color;
    };
}
