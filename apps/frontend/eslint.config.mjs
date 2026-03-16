import { defineConfig } from "eslint/config";
import eslintPluginAstro from "eslint-plugin-astro";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";

export default defineConfig([
    eslintPluginAstro.configs.recommended,
    eslintPluginAstro.configs["jsx-a11y-recommended"],
    eslintPluginPrettierRecommended,
]);
