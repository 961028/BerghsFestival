import {
    attachRgbSplit,
    attachRgbSplitFilter,
    buildDropShadow,
    rgbSplitConfig,
} from "./accents";

// ── Config ────────────────────────────────────────────────────────────────────

// Logo strobe on page load: how long it runs and how fast it flickers.
const LOGO_STROBE_DURATION_MS = 600;
const LOGO_STROBE_INTERVAL_MS = 50;

// ─────────────────────────────────────────────────────────────────────────────

const toggle = document.querySelector(".menu-toggle") as HTMLButtonElement;
const menu = document.getElementById("mobile-menu") as HTMLDialogElement;

let openedViaKeyboard = false;
let lastPointerType: string | null = null;

toggle.addEventListener("pointerdown", (e) => {
    lastPointerType = e.pointerType;
});

function openMenu(viaKeyboard: boolean) {
    openedViaKeyboard = viaKeyboard;
    toggle.setAttribute("aria-expanded", "true");
    toggle.setAttribute("aria-label", "Close menu");
    menu.showModal();
    if (viaKeyboard) {
        menu.querySelector<HTMLAnchorElement>("a")?.focus();
    } else {
        menu.focus();
    }
    document.body.style.overflow = "hidden";
}

function closeMenu() {
    if (menu.open) menu.close();
}

menu.addEventListener("close", () => {
    toggle.setAttribute("aria-expanded", "false");
    toggle.setAttribute("aria-label", "Open menu");
    document.body.style.overflow = "";
    if (openedViaKeyboard) toggle.focus();
    openedViaKeyboard = false;
});

toggle.addEventListener("click", () => {
    if (menu.open) {
        closeMenu();
    } else {
        const viaKeyboard = !lastPointerType;
        lastPointerType = null;
        openMenu(viaKeyboard);
    }
});

menu.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", closeMenu);
});

menu.addEventListener("click", (event) => {
    if (event.target === menu) closeMenu();
});

// ── Logo RGB strobe on load + hover ──

const reducedMotion = window.matchMedia(
    "(prefers-reduced-motion: reduce)",
).matches;

const logoLink = document.querySelector<HTMLElement>("nav > a");
const logoSvg = logoLink?.querySelector<SVGSVGElement>("svg");

if (logoLink && logoSvg) {
    if (!reducedMotion) {
        const c = rgbSplitConfig;
        const timer = setInterval(() => {
            const burst =
                Math.random() < c.burstChance
                    ? c.burstStrength * c.glitchiness
                    : 1;
            logoSvg.style.filter = buildDropShadow(
                c.amount,
                burst,
                c.amount * 0.3,
            );
        }, LOGO_STROBE_INTERVAL_MS);
        setTimeout(() => {
            clearInterval(timer);
            logoSvg.style.filter = "";
        }, LOGO_STROBE_DURATION_MS);
    }

    attachRgbSplitFilter(logoSvg);
}

document.querySelectorAll<HTMLElement>(".nav-link").forEach((link) => {
    const span = link.querySelector<HTMLElement>(".nav-link-text")!;
    attachRgbSplit(span);
});

// ── Submenu toggles (one per `.has-submenu` parent) ──

const submenuParents = document.querySelectorAll<HTMLElement>(".has-submenu");

submenuParents.forEach((parent) => {
    const submenuToggle =
        parent.querySelector<HTMLButtonElement>(".submenu-toggle");
    const submenu = parent.querySelector<HTMLElement>(".submenu");

    if (!submenuToggle || !submenu) return;

    const closeSubmenu = () => {
        submenuToggle.setAttribute("aria-expanded", "false");
    };
    const openSubmenu = () => {
        submenuToggle.setAttribute("aria-expanded", "true");
    };

    submenuToggle.addEventListener("click", () => {
        const open = submenuToggle.getAttribute("aria-expanded") === "true";
        if (open) {
            closeSubmenu();
        } else {
            openSubmenu();
        }
    });

    submenu.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", closeSubmenu);
    });

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

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            submenuToggle.getAttribute("aria-expanded") === "true"
        ) {
            closeSubmenu();
            submenuToggle.focus();
        }
    });
});
