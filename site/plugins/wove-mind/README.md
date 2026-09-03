# Wove Mind

Custom Kirby Panel plugin — the authoring UI for Wove Mind entries.

Registers:

- A **`mind` Panel area** with two views:
  - `/panel/mind` — the entries list
  - `/panel/mind/entry/{slug}` — the entry editor
- **Blueprints**: `pages/mind`, `pages/mind_entry`, `users/admin`, `users/contributor`
- **Vue components** (Kirby Panel Vue 3): `EntriesView`, `EntryEditorView`, `FormatChooser`

## First-time setup

1. Install and build the panel assets:
   ```bash
   cd site/plugins/wove-mind
   npm install
   npm run build
   ```
2. Create the Mind container page. In the content root:
   ```
   content/mind/mind.txt
   ```
   with contents:
   ```
   Title: Wove Mind
   ----
   ```
   (Kirby maps the folder to a page via the `.txt` file whose name matches the
   blueprint template — here `mind.yml` / `mind.txt`.)
3. Create at least one user with the `contributor` role (or use an existing
   admin). Contributors land on `/panel/mind` on login; admins land on the
   full Panel dashboard.

## Development

```bash
cd site/plugins/wove-mind
npm run dev
```

This runs Vite in watch mode. Reload the Panel to see changes.

## Build output

`index.js` and `index.css` at the plugin root are committed. Kirby auto-loads
them. Re-run `npm run build` after editing anything under `src/`.
