// ── Text scramble reveal effect ───────────────────────────────────────────────
// Layout is never touched. The real text stays in the DOM, invisible, holding
// its space. A ::before overlay (driven by a CSS custom property) shows the
// scrambled or revealed characters. No reflow, no layout shift.
//
// Re-triggers on Astro page transitions.
// Between reveals, random words glitch briefly as an idle effect.
// Instantly skips all animation for prefers-reduced-motion users.

const GLYPHS = "█░▓▒╳╬▪▫◆◇✕╱╲┼┤├┴┬│─☺";

// ── Reveal config ─────────────────────────────────────────────────────────────
const CHAR_STAGGER = 15; // ms between each character starting its scramble
const SCRAMBLE_FRAMES = 8; // frames before snapping to final character
const FRAME_INTERVAL = 40; // ms between scramble frames

// ── Idle glitch config ────────────────────────────────────────────────────────
const IDLE_BASE = 6000; // base ms between idle glitch bursts
const IDLE_JITTER = 1500; // ± random jitter on top of base
const IDLE_FRAMES = 5; // frames before snapping back
const IDLE_FRAME_INTERVAL = 50; // ms between idle scramble frames

const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

function randomGlyph(): string {
    return GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
}

type ElState = {
    el: HTMLElement;
    // Mutable character buffer — directly joined to produce the overlay string
    buf: string[];
    // Final characters, parallel to buf
    final: string[];
    // Pre-computed word boundary groups (indices into buf)
    words: number[][];
};

let elStates: ElState[] = [];

function setOverlay(el: HTMLElement, buf: string[]) {
    el.style.setProperty("--scramble", `"${buf.join("")}"`);
}

function scrambleReveal() {
    if (reducedMotion.matches) {
        for (const { el } of elStates) {
            el.style.removeProperty("--scramble");
            el.style.removeProperty("color");
        }
        elStates = [];
        return;
    }

    const allElements = Array.from(
        document.querySelectorAll<HTMLElement>("[data-scramble]"),
    );

    if (allElements.length === 0) return;

    // Reset any previously active elements
    for (const { el } of elStates) {
        el.style.removeProperty("--scramble");
        el.style.removeProperty("color");
    }
    elStates = [];

    // Exclude elements not currently visible (e.g. hidden mobile menu duplicates)
    const elements = allElements.filter((el) => el.offsetParent !== null);

    // Sort top → bottom
    elements.sort(
        (a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top,
    );

    for (const el of elements) {
        const text = el.getAttribute("aria-label") ?? el.textContent ?? "";
        el.setAttribute("aria-label", text);
        el.setAttribute("aria-live", "off");

        const chars = Array.from(text);
        const final = chars;
        // Start all non-space chars as a non-breaking space (invisible placeholder)
        const buf = chars.map((ch) => (ch.trim() === "" ? ch : "\u00A0"));

        // Pre-compute word groups
        const words: number[][] = [];
        let word: number[] = [];
        for (let i = 0; i < chars.length; i++) {
            if (chars[i].trim() !== "") {
                word.push(i);
            } else if (word.length > 0) {
                words.push(word);
                word = [];
            }
        }
        if (word.length > 0) words.push(word);

        el.style.color = "transparent";
        setOverlay(el, buf);
        elStates.push({ el, buf, final, words });
    }

    // Animate: stagger each non-space character, scramble then snap
    let globalIndex = 0;
    for (const { el, buf, final } of elStates) {
        let remaining = final.filter((ch) => ch.trim() !== "").length;

        for (let i = 0; i < final.length; i++) {
            if (final[i].trim() === "") continue;

            const delay = globalIndex++ * CHAR_STAGGER;
            const idx = i;

            setTimeout(() => {
                let frame = 0;
                buf[idx] = randomGlyph();
                setOverlay(el, buf);

                const ticker = setInterval(() => {
                    frame++;
                    if (frame >= SCRAMBLE_FRAMES) {
                        clearInterval(ticker);
                        buf[idx] = final[idx];
                        remaining--;
                        setOverlay(el, buf);
                        if (remaining === 0) {
                            el.style.removeProperty("--scramble");
                            el.style.removeProperty("color");
                        }
                    } else {
                        buf[idx] = randomGlyph();
                        setOverlay(el, buf);
                    }
                }, FRAME_INTERVAL);
            }, delay);
        }
    }
}

// ── Idle glitch ───────────────────────────────────────────────────────────────
// Each element runs its own independent timer. Only fires if the element is
// currently in the viewport and not mid-reveal.

function isInViewport(el: HTMLElement): boolean {
    const { top, bottom } = el.getBoundingClientRect();
    return bottom > 0 && top < window.innerHeight;
}

function glitchElement({ el, buf, final, words }: ElState) {
    if (el.style.getPropertyValue("--scramble") || !isInViewport(el)) return;
    if (words.length === 0) return;

    const indices = words[Math.floor(Math.random() * words.length)];

    el.style.color = "transparent";
    for (const i of indices) buf[i] = randomGlyph();
    setOverlay(el, buf);

    let frame = 0;
    const ticker = setInterval(() => {
        frame++;
        if (frame >= IDLE_FRAMES) {
            clearInterval(ticker);
            for (const i of indices) buf[i] = final[i];
            setOverlay(el, buf);
            el.style.removeProperty("--scramble");
            el.style.removeProperty("color");
        } else {
            for (const i of indices) buf[i] = randomGlyph();
            setOverlay(el, buf);
        }
    }, IDLE_FRAME_INTERVAL);
}

function scheduleElGlitch(state: ElState) {
    if (reducedMotion.matches) return;
    const delay = IDLE_BASE + (Math.random() * 2 - 1) * IDLE_JITTER;
    setTimeout(() => {
        glitchElement(state);
        scheduleElGlitch(state);
    }, delay);
}

function startIdleLoops() {
    for (const state of elStates) scheduleElGlitch(state);
}

// Run on initial load and page transitions
scrambleReveal();
startIdleLoops();

document.addEventListener("astro:page-load", () => {
    scrambleReveal();
    startIdleLoops();
});
