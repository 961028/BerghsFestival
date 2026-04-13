import { ACCENTS } from "./accents";

// ── Config ────────────────────────────────────────────────────────────────────

// Fonts cycled on nav link hover/active. Add or remove fonts freely.
const FONTS = [
    "'Inria Sans', sans-serif",
    "Georgia, serif",
    "Impact, sans-serif",
    "'Courier New', monospace",
    "'Arial Black', sans-serif",
    "'Times New Roman', serif",
    "Verdana, sans-serif",
];

// How fast each font/color swap happens during cycling (milliseconds).
const CYCLE_INTERVAL_MS = 50;

// Logo strobe on page load: how long it runs and how fast it flickers.
const LOGO_STROBE_DURATION_MS = 600;
const LOGO_STROBE_INTERVAL_MS = 50;

// Extra pixel padding added to each nav link's reserved width.
const NAV_LINK_WIDTH_PADDING_PX = 4;

// ─────────────────────────────────────────────────────────────────────────────

const toggle = document.querySelector(".menu-toggle") as HTMLButtonElement;
const menu = document.getElementById("mobile-menu")!;

function open() {
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Close menu");
    menu.setAttribute("aria-hidden", "false");
    document.body.style.overflow = "hidden";
}

function close() {
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Open menu");
    menu.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
}

toggle.addEventListener("click", () => {
    toggle.getAttribute("aria-expanded") === "true" ? close() : open();
});

menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", close);
});

// ── Font cycling ──

function randomAccent() {
    return ACCENTS[Math.floor(Math.random() * ACCENTS.length)];
}

function strobeAccentFill(
    el: SVGSVGElement,
    resetTo: string,
    duration = LOGO_STROBE_DURATION_MS,
    interval = LOGO_STROBE_INTERVAL_MS,
): ReturnType<typeof setInterval> {
    const timer = setInterval(() => {
        el.style.fill = randomAccent();
    }, interval);
    setTimeout(() => {
        clearInterval(timer);
        el.style.fill = resetTo;
    }, duration);
    return timer;
}

function startCycling(span: HTMLElement): ReturnType<typeof setInterval> {
    return setInterval(() => {
        span.style.fontFamily = FONTS[Math.floor(Math.random() * FONTS.length)];
        span.style.color = randomAccent();
    }, CYCLE_INTERVAL_MS);
}

function stopCycling(span: HTMLElement, timer: ReturnType<typeof setInterval>) {
    clearInterval(timer);
    span.style.fontFamily = "";
    span.style.color = "";
}

// Lock each nav-link-text span to the width of its widest font rendering.
// Wait for fonts to load first — otherwise canvas measures with the fallback
// font, but the DOM renders in Inria Sans (which is wider), causing clipping.
await document.fonts.ready;

const navLinks = document.querySelector<HTMLElement>(".nav-links")!;
const wasHidden = getComputedStyle(navLinks).display === "none";
if (wasHidden) {
    navLinks.style.display = "flex";
}

document.querySelectorAll<HTMLElement>(".nav-link-text").forEach((span) => {
    const text = span.textContent ?? "";
    const cs = getComputedStyle(span);
    const fontSize = cs.fontSize;
    const fontWeight = cs.fontWeight;

    const canvas = document.createElement("canvas");
    const ctx = canvas.getContext("2d")!;

    let maxWidth = 0;
    for (const font of FONTS) {
        ctx.font = `${fontWeight} ${fontSize} ${font}`;
        maxWidth = Math.max(maxWidth, ctx.measureText(text).width);
    }

    span.style.display = "inline-block";
    span.style.width = `${Math.ceil(maxWidth) + NAV_LINK_WIDTH_PADDING_PX}px`;
    span.style.textAlign = "center";
    span.style.overflow = "hidden";
    span.style.whiteSpace = "nowrap";
});

if (wasHidden) {
    navLinks.style.display = "";
}

// ── Logo fill cycling ──

const logoLink = document.querySelector<HTMLElement>("nav > a");
const logoSvg = logoLink?.querySelector<SVGSVGElement>("svg");

if (logoLink && logoSvg) {
    strobeAccentFill(logoSvg, "white");

    let logoTimer: ReturnType<typeof setInterval> | undefined;

    logoLink.addEventListener("mouseenter", () => {
        logoTimer = setInterval(() => {
            logoSvg.style.fill = randomAccent();
        }, CYCLE_INTERVAL_MS);
    });

    logoLink.addEventListener("mouseleave", () => {
        clearInterval(logoTimer);
        logoSvg.style.fill = "white";
    });
}

document.querySelectorAll<HTMLElement>(".nav-link").forEach((link) => {
    const span = link.querySelector<HTMLElement>(".nav-link-text")!;
    const isActive = link.getAttribute("aria-current") === "page";
    let timer: ReturnType<typeof setInterval>;

    if (isActive) {
        timer = startCycling(span);
    } else {
        link.addEventListener("mouseenter", () => {
            timer = startCycling(span);
        });
        link.addEventListener("mouseleave", () => {
            stopCycling(span, timer);
        });
    }
});
