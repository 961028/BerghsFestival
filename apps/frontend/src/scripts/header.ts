import { makeColorPicker, CYCLE_INTERVAL_MS } from "./accents";

// ── Config ────────────────────────────────────────────────────────────────────

// Logo strobe on page load: how long it runs and how fast it flickers.
const LOGO_STROBE_DURATION_MS = 600;
const LOGO_STROBE_INTERVAL_MS = 50;

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
    if (toggle.getAttribute("aria-expanded") === "true") {
        close();
    } else {
        open();
    }
});

menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", close);
});

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && toggle.getAttribute("aria-expanded") === "true") {
        close();
        toggle.focus();
    }
});

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

// ── Experiences submenu toggle ──

const submenuToggle =
    document.querySelector<HTMLButtonElement>(".submenu-toggle");
const submenu = document.querySelector<HTMLElement>("#experiences-submenu");

if (submenuToggle && submenu) {
    function openSubmenu() {
        submenuToggle!.setAttribute("aria-expanded", "true");
    }

    function closeSubmenu() {
        submenuToggle!.setAttribute("aria-expanded", "false");
    }

    submenuToggle.addEventListener("click", () => {
        const open =
            submenuToggle.getAttribute("aria-expanded") === "true";
        if (open) {
            closeSubmenu();
        } else {
            openSubmenu();
        }
    });

    // Close when a sub-item is clicked.
    submenu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", closeSubmenu);
    });

    // Close on outside click.
    document.addEventListener("click", (event) => {
        const target = event.target as Node;
        if (
            submenuToggle.getAttribute("aria-expanded") === "true" &&
            !submenuToggle.contains(target) &&
            !submenu.contains(target)
        ) {
            closeSubmenu();
        }
    });

    // Close on Escape.
    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            submenuToggle.getAttribute("aria-expanded") === "true"
        ) {
            closeSubmenu();
            submenuToggle.focus();
        }
    });
}
