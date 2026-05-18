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

// Attach hover-cycle accent effect to an element. `apply` receives the next
// color on enter. On leave, `reset` runs (defaults to `apply("")`) — override
// when the property needs `removeProperty` rather than an empty string.
// Respects prefers-reduced-motion: only applies a single color, no interval.
export function attachAccentCycle(
    el: HTMLElement,
    apply: (color: string) => void,
    options: {
        pick?: () => string;
        reset?: () => void;
    } = {},
): void {
    const pick = options.pick ?? makeColorPicker(2);
    const reset = options.reset ?? (() => apply(""));
    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    let timer: ReturnType<typeof setInterval> | undefined;
    el.addEventListener("mouseenter", () => {
        apply(pick());
        if (!reducedMotion) {
            timer = setInterval(() => apply(pick()), CYCLE_INTERVAL_MS);
        }
    });
    el.addEventListener("mouseleave", () => {
        if (timer) clearInterval(timer);
        reset();
    });
    window.addEventListener("pageshow", (e) => {
        if (e.persisted) {
            if (timer) clearInterval(timer);
            reset();
        }
    });
}

// ── RGB split (chromatic aberration) hover effect ─────────────────────────────
// Pure CSS text-shadow effect, GPU-composited, no dependencies.
// Mirrors the shader-based effectConfig.rgbSplit params but adapted for text:
// - amount: base channel separation (em units, applied via text-shadow offsets)
// - glitchiness: burst intensity multiplier
// - jitterSpeed: jitter rate in Hz (offsets re-randomize this often per second)
// - burstChance: burst probability per tick (0..1)
// - burstStrength: burst amplitude multiplier on top of `amount`
// - offsetR/G/B: base channel direction vectors, scaled by amount
// Tear effect intentionally dropped (clip-path per-element is costly and
// adds little value on single-line text).
export const rgbSplitConfig = {
    // ── Timing ────────────────────────────────────────────────────────────────
    // Jitter rate in Hz. Higher = more frequent random offset updates.
    jitterSpeed: 12,

    // ── Base separation ───────────────────────────────────────────────────────
    // Base R/B horizontal split distance in pixels (absolute, does not scale
    // with font-size). R sits at +amount on X, B sits at -amount on X.
    amount: 3,

    // ── Bursts (occasional spikes in separation) ──────────────────────────────
    // Probability per tick (0..1) that a burst fires.
    burstChance: 0.15,
    // Burst overall multiplier applied on top of amount.
    burstStrength: 8,
    // Extra burst intensity multiplier (chained: amount × burstStrength × glitchiness).
    glitchiness: 0.3,

    // ── Per-channel jitter scales ─────────────────────────────────────────────
    // Random per-tick noise added on top of base offsets in pixels. Each value
    // is a fraction of `amount × 0.3` (the global jitter magnitude in px).
    // 0 = locked, 1 = full jitter.
    // R/B jitter on X (horizontal). 1.0 = full jitter on the split axis.
    rJitterX: 2.0,
    bJitterX: 2.0,
    // R/B vertical wobble. Real CA is mostly horizontal — keep small.
    rJitterY: 0.6,
    bJitterY: 0.6,
    // G jitter (kept small — real CA: G shifts least).
    gJitterX: 1,
    gJitterY: 1,

    // ── G base offset ─────────────────────────────────────────────────────────
    // Static Y offset for G channel in pixels. Push G slightly off-axis so
    // it's visible above/below the base text glyph (otherwise it gets fully
    // occluded). Negative = upward, positive = downward.
    gBaseY: -1,

    // ── Colors ────────────────────────────────────────────────────────────────
    colorR: "#ff0000",
    colorG: "#00ff00",
    colorB: "#00ffff",
};

// R always +X, B always -X (opposite). G sits at gBaseY with tiny
// independent jitter (real CA: G shifts least). Each channel jitters
// independently — no shared offset → real channel separation, not blur.
function buildShadow(amount: number, burst: number, jitter: number): string {
    const c = rgbSplitConfig;
    const a = amount * burst;
    const j = (scale: number) => (Math.random() - 0.5) * jitter * scale;
    const rX = a + j(c.rJitterX);
    const bX = -a + j(c.bJitterX);
    const rY = j(c.rJitterY);
    const bY = j(c.bJitterY);
    const gX = j(c.gJitterX);
    const gY = c.gBaseY + j(c.gJitterY);
    const r = `${rX.toFixed(3)}px ${rY.toFixed(3)}px 0 ${c.colorR}`;
    const g = `${gX.toFixed(3)}px ${gY.toFixed(3)}px 0 ${c.colorG}`;
    const b = `${bX.toFixed(3)}px ${bY.toFixed(3)}px 0 ${c.colorB}`;
    return `${r}, ${g}, ${b}`;
}

// Attach RGB split (chromatic aberration) effect on hover. Animates via rAF
// at jitterSpeed Hz. Respects prefers-reduced-motion (static split, no jitter).
export function attachRgbSplit(el: HTMLElement): void {
    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    const c = rgbSplitConfig;
    let raf = 0;
    let lastTick = 0;

    function tick(now: number) {
        const tickInterval = 1000 / c.jitterSpeed;
        if (now - lastTick >= tickInterval) {
            lastTick = now;
            const burst =
                Math.random() < c.burstChance
                    ? c.burstStrength * c.glitchiness
                    : 1;
            const jitter = c.amount * 0.3;
            el.style.textShadow = buildShadow(c.amount, burst, jitter);
        }
        raf = requestAnimationFrame(tick);
    }

    el.addEventListener("mouseenter", () => {
        if (reducedMotion) {
            el.style.textShadow = buildShadow(c.amount, 1, 0);
            return;
        }
        lastTick = 0;
        raf = requestAnimationFrame(tick);
    });

    el.addEventListener("mouseleave", () => {
        if (raf) cancelAnimationFrame(raf);
        raf = 0;
        el.style.textShadow = "";
    });
}

export function buildDropShadow(
    amount: number,
    burst: number,
    jitter: number,
): string {
    const c = rgbSplitConfig;
    const a = amount * burst;
    const j = (scale: number) => (Math.random() - 0.5) * jitter * scale;
    const rX = a + j(c.rJitterX);
    const bX = -a + j(c.bJitterX);
    const rY = j(c.rJitterY);
    const bY = j(c.bJitterY);
    const gX = j(c.gJitterX);
    const gY = c.gBaseY + j(c.gJitterY);
    const r = `drop-shadow(${rX.toFixed(3)}px ${rY.toFixed(3)}px 0 ${c.colorR})`;
    const g = `drop-shadow(${gX.toFixed(3)}px ${gY.toFixed(3)}px 0 ${c.colorG})`;
    const b = `drop-shadow(${bX.toFixed(3)}px ${bY.toFixed(3)}px 0 ${c.colorB})`;
    return `${r} ${g} ${b}`;
}

// Always-on intense RGB split via `filter: drop-shadow(...)`. No hover gating.
// Intensity scales amount, burst chance, and burst strength on top of the
// base `rgbSplitConfig`. Respects prefers-reduced-motion (static split).
export function runRgbSplitIntense(
    el: HTMLElement | SVGElement,
    intensity = 1,
): () => void {
    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    const c = rgbSplitConfig;
    const amount = c.amount * 2 * intensity;
    const burstChance = Math.min(1, c.burstChance * 3 * intensity);
    const burstStrength = c.burstStrength * 2 * intensity;

    if (reducedMotion) {
        (el as HTMLElement).style.filter = buildDropShadow(amount, 1, 0);
        return () => {
            (el as HTMLElement).style.filter = "";
        };
    }

    let raf = 0;
    let lastTick = 0;

    function tick(now: number) {
        const tickInterval = 1000 / c.jitterSpeed;
        if (now - lastTick >= tickInterval) {
            lastTick = now;
            const burst =
                Math.random() < burstChance ? burstStrength * c.glitchiness : 1;
            const jitter = amount * 0.3;
            (el as HTMLElement).style.filter = buildDropShadow(
                amount,
                burst,
                jitter,
            );
        }
        raf = requestAnimationFrame(tick);
    }

    raf = requestAnimationFrame(tick);

    return () => {
        if (raf) cancelAnimationFrame(raf);
        (el as HTMLElement).style.filter = "";
    };
}

// RGB split via `filter: drop-shadow(...)` — works for SVG/img where
// `text-shadow` does not. Same jitter logic as `attachRgbSplit`.
// Optional `trigger` element fires hover events instead of `el` itself
// (useful when the visual is a small icon inside a larger hit area).
export function attachRgbSplitFilter(
    el: HTMLElement | SVGElement,
    trigger: HTMLElement | SVGElement = el,
): void {
    const reducedMotion = window.matchMedia(
        "(prefers-reduced-motion: reduce)",
    ).matches;
    const c = rgbSplitConfig;
    let raf = 0;
    let lastTick = 0;

    function tick(now: number) {
        const tickInterval = 1000 / c.jitterSpeed;
        if (now - lastTick >= tickInterval) {
            lastTick = now;
            const burst =
                Math.random() < c.burstChance
                    ? c.burstStrength * c.glitchiness
                    : 1;
            const jitter = c.amount * 0.3;
            (el as HTMLElement).style.filter = buildDropShadow(
                c.amount,
                burst,
                jitter,
            );
        }
        raf = requestAnimationFrame(tick);
    }

    trigger.addEventListener("mouseenter", () => {
        if (reducedMotion) {
            (el as HTMLElement).style.filter = buildDropShadow(c.amount, 1, 0);
            return;
        }
        lastTick = 0;
        raf = requestAnimationFrame(tick);
    });

    trigger.addEventListener("mouseleave", () => {
        if (raf) cancelAnimationFrame(raf);
        raf = 0;
        (el as HTMLElement).style.filter = "";
    });

    window.addEventListener("pageshow", (e) => {
        if (e.persisted) {
            if (raf) cancelAnimationFrame(raf);
            raf = 0;
            (el as HTMLElement).style.filter = "";
        }
    });
}
