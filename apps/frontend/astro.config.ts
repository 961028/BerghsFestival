import sitemap from "@astrojs/sitemap";
import type { AstroIntegration } from "astro";
import { defineConfig, fontProviders } from "astro/config";
import { debounce } from "es-toolkit";
import { strict as assert } from "node:assert";
import { resolve } from "node:path";
import { loadEnv } from "vite";

const { NODE_ENV } = process.env;
assert(NODE_ENV);

const { APP_HOST, APP_HOME } = loadEnv(NODE_ENV, process.cwd(), "");
assert(APP_HOST);
assert(APP_HOME);

function cmsWatcher(): AstroIntegration {
    return {
        name: "cms-watcher",
        hooks: {
            "astro:server:setup": ({ server, logger, refreshContent }) => {
                const cmsLogger = logger.fork("cms");

                if (!refreshContent) {
                    console.error("Failed");
                    return;
                }

                if (server.watcher.getMaxListeners() < 50) {
                    server.watcher.setMaxListeners(50);
                }

                const sentinel = resolve("/tmp/cms-updated.flag");

                server.watcher.add(sentinel);

                const handleChange = () => {
                    cmsLogger.info("Content changed.");

                    refreshContent({});
                };

                const debouncedHandleChange = debounce(handleChange, 100);

                server.watcher.on("change", (file) => {
                    if (file !== sentinel) {
                        return;
                    }

                    debouncedHandleChange();
                });
            },
        },
    };
}

// https://astro.build/config
export default defineConfig({
    site: APP_HOME,
    server: {
        allowedHosts: [APP_HOST],
        host: "127.0.0.1", // Apache needs this
    },
    vite: {
        envPrefix: ["VITE_", "APP_", "WP_"],
        server: {
            strictPort: true,
        },
    },
    fonts: [
        {
            provider: fontProviders.fontsource(),
            name: "Inter",
            cssVariable: "--font-inter",
        },
    ],
    image: {
        domains: [APP_HOST],
        layout: "constrained",
    },
    integrations: [cmsWatcher(), sitemap()],
});
