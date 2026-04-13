// The four signal colors from the design system. All flicker/strobe effects
// draw from this list. To add or remove colors, edit this array only.
export const ACCENTS = ["#00ff00", "#ff0000", "#1e00ff", "#eeff00"];

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
