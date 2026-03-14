// @ts-check

import { strict as assert } from 'node:assert';

import { defineConfig } from 'astro/config';
import { loadEnv } from "vite";

const { NODE_ENV } = process.env;
assert(NODE_ENV);

const { APP_HOST } = loadEnv(NODE_ENV, process.cwd(), "");

// https://astro.build/config
export default defineConfig({
    image: {
        domains: [APP_HOST],
        layout: 'constrained',
    },
});
