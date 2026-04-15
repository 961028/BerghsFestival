import { makeColorPicker } from "./accents";

// ── Config ────────────────────────────────────────────────────────────────────

// How fast each color swap happens during cycling (milliseconds).
const CYCLE_INTERVAL_MS = 50;

// Logo strobe on page load: how long it runs and how fast it flickers.
const LOGO_STROBE_DURATION_MS = 600;
const LOGO_STROBE_INTERVAL_MS = 50;

// ─────────────────────────────────────────────────────────────────────────────

// MOBILE MENU: uncomment to enable toggle logic
// const toggle = document.querySelector(".menu-toggle") as HTMLButtonElement;
// const menu = document.getElementById("mobile-menu")!;
//
// function open() {
//     toggle.setAttribute("aria-expanded", "true");
//     toggle.setAttribute("aria-label", "Close menu");
//     menu.setAttribute("aria-hidden", "false");
//     document.body.style.overflow = "hidden";
// }
//
// function close() {
//     toggle.setAttribute("aria-expanded", "false");
//     toggle.setAttribute("aria-label", "Open menu");
//     menu.setAttribute("aria-hidden", "true");
//     document.body.style.overflow = "";
// }
//
// toggle.addEventListener("click", () => {
//     toggle.getAttribute("aria-expanded") === "true" ? close() : open();
// });
//
// menu.querySelectorAll("a").forEach((link) => {
//     link.addEventListener("click", close);
// });

// ── Font cycling ──

const reducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
).matches;
const nextAccent = makeColorPicker(2);

function strobeAccentFill(
    el: SVGSVGElement,
    resetTo: string,
    duration = LOGO_STROBE_DURATION_MS,
    interval = LOGO_STROBE_INTERVAL_MS,
): ReturnType<typeof setInterval> {
    const timer = setInterval(() => {
        el.style.fill = nextAccent();
    }, interval);
    setTimeout(() => {
        clearInterval(timer);
        el.style.fill = resetTo;
    }, duration);
    return timer;
}

function startCycling(span: HTMLElement): ReturnType<typeof setInterval> {
    return setInterval(() => {
        span.style.color = nextAccent();
    }, CYCLE_INTERVAL_MS);
}

function stopCycling(span: HTMLElement, timer: ReturnType<typeof setInterval>) {
    clearInterval(timer);
    span.style.color = "";
}

// ── Logo fill cycling ──

const logoLink = document.querySelector<HTMLElement>("nav > a");
const logoSvg = logoLink?.querySelector<SVGSVGElement>("svg");

if (logoLink && logoSvg) {
    if (!reducedMotion) {
        strobeAccentFill(logoSvg, "white");
    }

    let logoTimer: ReturnType<typeof setInterval> | undefined;

    logoLink.addEventListener("mouseenter", () => {
        logoSvg.style.fill = nextAccent();
        if (!reducedMotion) {
            logoTimer = setInterval(() => {
                logoSvg.style.fill = nextAccent();
            }, CYCLE_INTERVAL_MS);
        }
    });

    logoLink.addEventListener("mouseleave", () => {
        clearInterval(logoTimer);
        logoSvg.style.fill = "white";
    });
}

document.querySelectorAll<HTMLElement>(".nav-link").forEach((link) => {
    const span = link.querySelector<HTMLElement>(".nav-link-text")!;
    let timer: ReturnType<typeof setInterval>;

    link.addEventListener("mouseenter", () => {
        span.style.color = nextAccent();
        if (!reducedMotion) {
            timer = startCycling(span);
        }
    });
    link.addEventListener("mouseleave", () => {
        stopCycling(span, timer);
    });
});
