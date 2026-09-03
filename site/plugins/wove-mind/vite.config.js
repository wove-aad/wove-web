import { defineConfig } from "vite";
import vue from "@vitejs/plugin-vue2";
import { resolve } from "path";

export default defineConfig({
  plugins: [vue()],
  build: {
    lib: {
      entry: resolve(__dirname, "src/index.js"),
      name: "WoveMind",
      formats: ["iife"],
      fileName: () => "index.js",
    },
    rollupOptions: {
      external: ["vue"],
      output: {
        globals: { vue: "Vue" },
        assetFileNames: (asset) =>
          asset.name === "style.css" ? "index.css" : asset.name,
      },
    },
    outDir: resolve(__dirname),
    emptyOutDir: false,
    cssCodeSplit: false,
  },
});
