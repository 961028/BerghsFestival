// ── Text scramble reveal effect ───────────────────────────────────────────────
// Real text stays in the DOM (color: transparent during animation) holding
// layout. An injected sibling <span aria-hidden="true"> renders the scrambled
// glyphs absolutely positioned on top. Assistive tech reads the real text;
// the glyph overlay is hidden from the accessibility tree across all browsers.
//
// Re-triggers on Astro page transitions.
// Between reveals, random words glitch briefly as an idle effect.
// Instantly skips all animation for prefers-reduced-motion users.

const GLYPHS = "█░▓▒╳╬▪▫◆◇✕╱╲┼┤├┴┬│─☺";
const OVERLAY_CLASS = "scramble-overlay";

// ── Reveal config ─────────────────────────────────────────────────────────────
const CHAR_STAGGER = 15;
const SCRAMBLE_FRAMES = 8;
const FRAME_INTERVAL = 40;

// ── Idle glitch config ────────────────────────────────────────────────────────
const IDLE_BASE = 8000;
const IDLE_JITTER = 1500;
const IDLE_FRAMES = 5;
const IDLE_FRAME_INTERVAL = 50;

const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");

function randomGlyph(): string {
    return GLYPHS[Math.floor(Math.random() * GLYPHS.length)];
}

type ElState = {
    el: HTMLElement;
    overlay: HTMLElement;
    buf: string[];
    final: string[];
    words: number[][];
};

let elStates: ElState[] = [];

function renderOverlay(state: ElState) {
    state.overlay.textContent = state.buf.join("");
}

function ensureOverlay(el: HTMLElement): HTMLElement {
    let overlay = el.querySelector<HTMLElement>(`:scope > .${OVERLAY_CLASS}`);
    if (!overlay) {
        overlay = document.createElement("span");
        overlay.className = OVERLAY_CLASS;
        overlay.setAttribute("aria-hidden", "true");
        el.appendChild(overlay);
    }
    overlay.textContent = "";
    return overlay;
}

function clearOverlay(state: ElState) {
    state.overlay.textContent = "";
    state.el.style.removeProperty("color");
}

function scrambleReveal() {
    if (reducedMotion.matches) {
        for (const state of elStates) clearOverlay(state);
        elStates = [];
        return;
    }

    const allElements = Array.from(
        document.querySelectorAll<HTMLElement>("[data-scramble]"),
    );

    if (allElements.length === 0) return;

    for (const state of elStates) clearOverlay(state);
    elStates = [];

    const elements = allElements.filter((el) => el.offsetParent !== null);

    elements.sort(
        (a, b) => a.getBoundingClientRect().top - b.getBoundingClientRect().top,
    );

    for (const el of elements) {
        // Strip overlay from text source so previous runs don't pollute
        const existing = el.querySelector<HTMLElement>(
            `:scope > .${OVERLAY_CLASS}`,
        );
        if (existing) existing.remove();

        const text = el.textContent ?? "";
        const chars = Array.from(text);
        const final = chars;
        const buf = chars.map((ch) => (ch.trim() === "" ? ch : " "));

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

        const overlay = ensureOverlay(el);
        el.style.color = "transparent";

        const state: ElState = { el, overlay, buf, final, words };
        renderOverlay(state);
        elStates.push(state);
    }

    let globalIndex = 0;
    let currentGroup: string | null = null;
    for (const state of elStates) {
        const { el, buf, final } = state;
        const group = el.dataset.scrambleGroup ?? null;
        if (group !== null && group !== currentGroup) {
            globalIndex = 0;
            currentGroup = group;
        }
        let remaining = final.filter((ch) => ch.trim() !== "").length;

        for (let i = 0; i < final.length; i++) {
            if (final[i].trim() === "") continue;

            const delay = globalIndex++ * CHAR_STAGGER;
            const idx = i;

            setTimeout(() => {
                let frame = 0;
                buf[idx] = randomGlyph();
                renderOverlay(state);

                const ticker = setInterval(() => {
                    frame++;
                    if (frame >= SCRAMBLE_FRAMES) {
                        clearInterval(ticker);
                        buf[idx] = final[idx];
                        remaining--;
                        renderOverlay(state);
                        if (remaining === 0) clearOverlay(state);
                    } else {
                        buf[idx] = randomGlyph();
                        renderOverlay(state);
                    }
                }, FRAME_INTERVAL);
            }, delay);
        }
    }
}

// ── Idle glitch ───────────────────────────────────────────────────────────────

function isInViewport(el: HTMLElement): boolean {
    const { top, bottom } = el.getBoundingClientRect();
    return bottom > 0 && top < window.innerHeight;
}

function glitchElement(state: ElState) {
    if (state.overlay.textContent || !isInViewport(state.el)) return;
    if (state.words.length === 0) return;

    const indices = state.words[Math.floor(Math.random() * state.words.length)];

    state.el.style.color = "transparent";
    for (const i of indices) state.buf[i] = randomGlyph();
    renderOverlay(state);

    let frame = 0;
    const ticker = setInterval(() => {
        frame++;
        if (frame >= IDLE_FRAMES) {
            clearInterval(ticker);
            for (const i of indices) state.buf[i] = state.final[i];
            clearOverlay(state);
        } else {
            for (const i of indices) state.buf[i] = randomGlyph();
            renderOverlay(state);
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

scrambleReveal();
startIdleLoops();

document.addEventListener("astro:page-load", () => {
    scrambleReveal();
    startIdleLoops();
});
