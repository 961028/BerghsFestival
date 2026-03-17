// @ts-check
import eslint from "@eslint/js";
import { defineConfig } from "eslint/config";
import astro from "eslint-plugin-astro";
import prettier from "eslint-plugin-prettier/recommended";
import globals from "globals";
import tseslint from "typescript-eslint";

export default defineConfig([
    {
        ignores: ["**/.astro", "**/dist", "**/node_modules"],
    },
    {
        languageOptions: {
            globals: globals.node,
        },
    },
    eslint.configs.recommended,
    tseslint.configs.recommended,
    astro.configs.recommended,
    astro.configs["jsx-a11y-strict"],
    prettier,
]);
