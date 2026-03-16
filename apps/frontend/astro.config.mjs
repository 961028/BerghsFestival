// @ts-check

import { strict as assert } from "node:assert";

import { defineConfig } from "astro/config";
import { loadEnv } from "vite";

const { NODE_ENV } = process.env;
assert(NODE_ENV);

const { APP_HOST, APP_HOME } = loadEnv(NODE_ENV, process.cwd(), "");
assert(APP_HOST);
assert(APP_HOME);

// https://astro.build/config
export default defineConfig({
    site: APP_HOME,
    server: {
        allowedHosts: [APP_HOST],
        host: "127.0.0.1", // Apache needs this
    },
    vite: {
        server: {
            strictPort: true,
        },
    },
    image: {
        domains: [APP_HOST],
        layout: "constrained",
    },
});
