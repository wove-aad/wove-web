<template>
  <k-panel-inside
    class="k-panel-view wove-mind"
    :data-fmt="currentFormat"
  >
    <div class="wove-topbar wove-topbar--editor">
      <div class="wove-topbar__left">
        <button
          class="wove-btn wove-btn--ghost wove-btn--back"
          title="Back to all entries"
          @click="backToList"
        >
          <k-icon type="angle-left" />
          <span>Back</span>
        </button>
        <span class="wove-brand">
          Wove Mind<span class="wove-brand__dot">/</span
          ><span class="wove-brand__crumb">{{
            isNew ? "New entry" : "Edit"
          }}</span>
        </span>
        <k-mind-format-chip
          :value="currentFormat"
          @input="onFormatChange"
        />
      </div>
      <div class="wove-topbar__right">
        <span class="wove-save-status" :class="saveStateClass">
          <span class="wove-save-status__dot" />
          <span>{{ saveLabel }}</span>
        </span>
        <button
          class="wove-btn wove-btn--ghost wove-btn--focus"
          :aria-pressed="focusMode ? 'true' : 'false'"
          :title="(focusMode ? 'Show the settings sidebar' : 'Hide the sidebar and focus on writing') + ' (' + focusShortcut + ')'"
          @click="toggleFocus"
        >
          <k-icon :type="focusMode ? 'collapse-horizontal' : 'expand-horizontal'" />
          <span>{{ focusMode ? "Exit focus" : "Focus" }}</span>
        </button>
        <a
          v-if="previewUrl"
          class="wove-btn wove-btn--ghost"
          :href="previewUrl"
          target="_blank"
          rel="noopener"
          :title="isDraft ? 'Preview draft on the site' : 'View live on the site'"
        >
          <k-icon type="open" />
          <span>View</span>
        </a>
        <button
          class="wove-btn wove-btn--danger"
          :disabled="isSaving || isDeleting"
          @click="confirmDelete"
          title="Delete this entry"
        >
          <k-icon type="trash" />
          <span>Delete</span>
        </button>
        <button
          v-if="isDraft"
          class="wove-btn"
          :disabled="isSaving"
          @click="publish"
        >
          <k-icon type="check" />
          <span>Publish</span>
        </button>
        <button
          v-else
          class="wove-btn wove-btn--ghost"
          :disabled="isSaving"
          @click="unpublish"
        >
          <k-icon type="undo" />
          <span>Unpublish</span>
        </button>
      </div>
    </div>

    <div v-if="deleteOpen" class="wove-confirm-overlay" @click.self="deleteOpen = false">
      <div class="wove-confirm" role="dialog" aria-modal="true">
        <h3 class="wove-confirm__title">Delete this entry?</h3>
        <p class="wove-confirm__body">
          This removes
          <strong>{{ values.title || "the entry" }}</strong>
          permanently. It can't be undone from here.
        </p>
        <div class="wove-confirm__actions">
          <button class="wove-btn wove-btn--ghost" @click="deleteOpen = false" :disabled="isDeleting">
            Cancel
          </button>
          <button class="wove-btn wove-btn--danger wove-btn--danger-solid" @click="doDelete" :disabled="isDeleting">
            {{ isDeleting ? "Deleting…" : "Delete" }}
          </button>
        </div>
      </div>
    </div>

    <div class="wove-editor" :class="{ 'is-focus': focusMode }">
      <main class="wove-editor__main">
        <div class="wove-editor__compose">
          <div class="wove-fmt-meta">
            <span class="wove-fmt-pill">{{ formatMeta.name }}</span>
            <div class="wove-fmt-desc">{{ formatMeta.desc }}</div>
            <div class="wove-fmt-perfect">{{ formatMeta.perfect }}</div>
          </div>

          <div class="wove-compose-form" :style="{ '--wm-body-h': bodyHeight + 'px' }">
            <k-form
              v-if="fields && Object.keys(mainFields).length"
              :fields="mainFields"
              :value="values"
              @input="onInput"
            />
            <!-- Body is always the last main field, so this sits
                 directly under it. Drag to resize, double-click to reset. -->
            <div
              v-if="mainFields.body"
              class="wove-body-resize"
              role="separator"
              aria-orientation="horizontal"
              :aria-valuenow="bodyHeight"
              :aria-valuemin="BODY_MIN_H"
              :aria-valuemax="BODY_MAX_H"
              aria-label="Resize the body field"
              tabindex="0"
              title="Drag to resize. Double-click to reset."
              @pointerdown="startBodyResize"
              @dblclick="setBodyHeight(BODY_DEFAULT_H)"
              @keydown.up.prevent="setBodyHeight(bodyHeight - 40)"
              @keydown.down.prevent="setBodyHeight(bodyHeight + 40)"
            >
              <span class="wove-body-resize__grip" />
            </div>
          </div>
        </div>
      </main>

      <aside class="wove-editor__rail">
        <span class="wove-rail__label">Settings</span>
        <k-form
          v-if="fields"
          :fields="railFields"
          :value="values"
          :endpoints="endpoints"
          @input="onInput"
        />

        <div v-if="hasSeoFields" class="wove-rail__preview">
          <span class="wove-rail__label wove-rail__label--muted">
            Search preview
          </span>
          <k-mind-serp-preview
            :title="values.title"
            :seo-title="values.seotitle"
            :seo-description="values.seodescription"
            :auto-description="autoDescription"
            :slug="slug"
          />
        </div>
      </aside>
    </div>

    <!-- Mounted once. Referenced globally so the writer-node image
         command can open() it and await a payload. -->
    <k-mind-image-picker ref="imagePicker" />
  </k-panel-inside>
</template>

<script>
import { FORMAT_MAP } from "../formats.js";

// Kirby lowercases blueprint field keys, so every name in the lists
// below is lowercase — `navText` in YAML becomes `navtext` here.
//
// Rail (settings sidebar). Uses Grace's site/blueprints field names.
const RAIL_FIELDS = [
  "show_author",
  "case_study",
  "services",
  "tags",
  "date",
  "seotitle",
  "seodescription",
  "seokeywords",
];

// Never rendered by k-form — presented as the topbar chip.
const CHIP_FIELDS = ["format"];

// Never rendered anywhere. Kept out of both main and rail regardless
// of format.
//   robots / ogtype / ignorecache — from tabs/seo.yml, not part of
//     this authoring flow.
//   navtext — navigation label from tabs/seo.yml, not needed here.
//   author — hidden because entries are auto-attributed to the signed
//     -in contributor on create (see EntriesView.createEntry).
//   blocksheadline — decorative section heading in the blueprint;
//     nothing to author.
const HIDDEN_FIELDS = [
  "robots",
  "ogtype",
  "ignorecache",
  "navtext",
  "author",
  "blocksheadline",
];

// Per-format field visibility. Kirby's `when:` can't express these
// (single-value scalar match only), so the Panel view enforces it.
// A value of `null` means "no restriction — show whatever the
// blueprint defines". Restricted lists are matched against the
// blueprint fields, so extras we don't know about get skipped.
//
// Spark / Thread / What if use `body` (a simple writer field), so
// authoring feels like plain form boxes. Long read uses `blocks` for
// the embedded WYSIWYG block editor.
const MAIN_FIELDS_BY_FORMAT = {
  spark:    ["image", "body"],
  thread:   ["title", "image", "body"],
  whatif:   ["title", "image", "excerpt", "body"],
  longread: ["title", "image", "excerpt", "body"],
};

// Blueprint labels we override for the Panel view. The shared
// wove-mind-entry.yml uses "Excerpt" as the label for the `body`
// writer field (a naming holdover); in this authoring flow it's the
// post body, so we relabel it. Also override the actual `excerpt`
// field's label so the two don't collide visually.
const LABEL_OVERRIDES = {
  body:    "Body",
  excerpt: "Excerpt (short summary)",
  seotitle: "Meta title",
  seodescription: "Meta description",
};

// Body field height (px). Stored per browser so each admin keeps
// their preferred size.
const BODY_MIN_H = 160;
const BODY_MAX_H = 1200;
const BODY_DEFAULT_H = 360;
const BODY_H_KEY = "wove-mind.body-height";
const FOCUS_KEY = "wove-mind.focus";

// Sparks have no title field, so their page title (browser tab, meta
// title fallback) is set from the start of the text on save.
const SPARK_TITLE_LENGTH = 60;

// Matches Kirby's excerpt(160) used as the meta description fallback
// in site/snippets/header.php.
const DESCRIPTION_LENGTH = 160;

function plainText(html) {
  if (!html) return "";
  const el = document.createElement("div");
  el.innerHTML = html;
  return (el.textContent || "").replace(/\s+/g, " ").trim();
}

function excerpt(text, length) {
  if (text.length <= length) return text;
  const cut = text.slice(0, length);
  const space = cut.lastIndexOf(" ");
  return (space > length * 0.6 ? cut.slice(0, space) : cut).trim() + "…";
}
const RAIL_FIELDS_BY_FORMAT = {
  spark:    ["tags", "case_study", "services", "seotitle", "seodescription"],
  thread:   ["tags", "case_study", "services", "seotitle", "seodescription"],
  whatif:   ["tags", "case_study", "services", "seotitle", "seodescription"],
  longread: null,
};

const AUTOSAVE_DELAY_MS = 1500;

export default {
  props: {
    entryId: { type: String, required: true },
    isNew: { type: Boolean, default: false },
    initialContent: { type: Object, default: () => ({}) },
    fields: { type: Object, default: null },
    status: { type: String, default: "draft" },
    // Standard k-page-view scaffolding — Kirby's content/changes
    // system reads these when fields do uploads / autosave.
    api: { type: String, default: null },
    id: { type: String, default: null },
    lock: { type: [Object, null], default: null },
    permissions: { type: [Object, null], default: null },
    versions: { type: [Object, null], default: null },
    previewUrl: { type: String, default: null },
  },
  data() {
    const values = { ...this.initialContent };
    // New entries used to be created with the SEO tab's
    // `{{ page.title }}` default resolved against the slug, so the
    // stored meta title is the slug. Treat that as empty.
    const slug = this.entryId.split("/").pop();
    if (values.seotitle && values.seotitle === slug) values.seotitle = "";
    let bodyHeight = BODY_DEFAULT_H;
    let focusMode = false;
    try {
      const stored = parseInt(localStorage.getItem(BODY_H_KEY), 10);
      if (stored) bodyHeight = stored;
      focusMode = localStorage.getItem(FOCUS_KEY) === "1";
    } catch (_) {}
    return {
      BODY_MIN_H,
      BODY_MAX_H,
      BODY_DEFAULT_H,
      bodyHeight: Math.min(BODY_MAX_H, Math.max(BODY_MIN_H, bodyHeight)),
      focusMode,
      // Whether Kirby's own menu was open before focus mode closed it
      menuWasOpen: null,
      values,
      isSaving: false,
      isDeleting: false,
      deleteOpen: false,
      dirty: false,
      lastSavedAt: this.isNew ? null : new Date(),
      autosaveTimer: null,
    };
  },
  computed: {
    // Kirby Panel API encodes page ids by swapping "/" for "+".
    apiId() {
      return this.entryId.replace(/\//g, "+");
    },
    currentFormat() {
      return this.values.format || "whatif";
    },
    formatMeta() {
      return FORMAT_MAP[this.currentFormat] || FORMAT_MAP.whatif;
    },
    isDraft() {
      return this.status === "draft";
    },
    slug() {
      // entryId is the full page id like "mind/spark-2026-09-03-abcd"
      return this.entryId.split("/").pop();
    },
    mainFields() {
      if (!this.fields) return {};
      const allow = MAIN_FIELDS_BY_FORMAT[this.currentFormat];
      if (allow) {
        // Preserve the allowlist's order; skip anything the blueprint
        // doesn't define.
        const out = {};
        for (const name of allow) {
          if (this.fields[name]) out[name] = this.decorate(name, this.fields[name]);
        }
        return out;
      }
      // Unrestricted: everything not in the rail, chip, or hidden set,
      // in blueprint order. `image` gets spliced in after `title`
      // (magazine-style cover under the title) since the blueprint
      // itself puts image in a sidebar section.
      const entries = Object.entries(this.fields)
        .filter(
          ([name]) =>
            !RAIL_FIELDS.includes(name) &&
            !CHIP_FIELDS.includes(name) &&
            !HIDDEN_FIELDS.includes(name) &&
            name !== "image"
        )
        .map(([name, field]) => [name, this.decorate(name, field)]);
      if (this.fields.image) {
        const titleIdx = entries.findIndex(([n]) => n === "title");
        const insertAt = titleIdx >= 0 ? titleIdx + 1 : 0;
        entries.splice(insertAt, 0, ["image", this.decorate("image", this.fields.image)]);
      }
      return Object.fromEntries(entries);
    },
    railFields() {
      if (!this.fields) return {};
      const allow = RAIL_FIELDS_BY_FORMAT[this.currentFormat];
      const filterHidden = (name) => !HIDDEN_FIELDS.includes(name);
      // The rail is too narrow for side-by-side fields, so stack them
      // all full width (the SEO tab sets some to 1/2).
      const stack = (field) =>
        field.width && field.width !== "1/1" ? { ...field, width: "1/1" } : field;
      if (allow) {
        const out = {};
        for (const name of allow) {
          if (this.fields[name] && filterHidden(name)) {
            out[name] = stack(this.decorate(name, this.fields[name]));
          }
        }
        return out;
      }
      return Object.fromEntries(
        Object.entries(this.fields)
          .filter(([name]) => RAIL_FIELDS.includes(name) && filterHidden(name))
          .map(([name, field]) => [name, stack(this.decorate(name, field))])
      );
    },
    // Meta description fallback from the post content: the excerpt
    // field if there is one, else the body, else the long read blocks.
    autoDescription() {
      const v = this.values;
      let text = plainText(v.excerpt) || plainText(v.body);
      if (!text && Array.isArray(v.blocks)) {
        text = v.blocks
          .map((b) => plainText(b.content && b.content.text))
          .filter(Boolean)
          .join(" ");
      }
      return excerpt(text, DESCRIPTION_LENGTH);
    },
    focusShortcut() {
      const mac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
      return mac ? "⌘." : "Ctrl+.";
    },
    hasSeoFields() {
      return this.railFields.seotitle || this.railFields.seodescription;
    },
    saveStateClass() {
      if (this.isSaving) return "is-saving";
      if (this.dirty) return "is-dirty";
      if (!this.lastSavedAt) return "is-new";
      return "";
    },
    saveLabel() {
      if (this.isSaving) return "Saving…";
      if (this.dirty) return "Unsaved changes";
      if (this.lastSavedAt) return "Saved · autosaves as you type";
      return "Not saved yet";
    },
  },
  mounted() {
    // Expose the image picker to the custom writer image node, which
    // needs an async open()→Promise handshake but doesn't otherwise
    // have a way to reach a mounted Vue component. Cleared on
    // destroy so we don't leak across view transitions.
    window.__woveMindImagePicker = this.$refs.imagePicker;
    document.addEventListener("keydown", this.onKeydown);
    if (this.focusMode) this.collapsePanelMenu();
  },
  beforeDestroy() {
    if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
    document.removeEventListener("keydown", this.onKeydown);
    this.restorePanelMenu();
    if (window.__woveMindImagePicker === this.$refs.imagePicker) {
      window.__woveMindImagePicker = null;
    }
  },
  methods: {
    // Apply any per-field Panel-side tweaks without mutating the
    // original field object:
    //   - override the visible label when we want one different from
    //     the shared blueprint's;
    //   - drop the blueprint's `when` condition since we control
    //     field visibility per-format via MAIN/RAIL_FIELDS_BY_FORMAT
    //     ourselves. Kirby's k-fieldset otherwise silently hides
    //     fields whose `when` clause doesn't match the current values
    //     (e.g. `excerpt` gated to project-highlight in the blueprint
    //     wouldn't render for whatif/longread).
    //   - the `body` writer field gets a permanent top toolbar
    //     (writer's default is `toolbar: {inline: true}`, which
    //     floats on selection — Grace wants it always visible).
    decorate(name, field) {
      const label = LABEL_OVERRIDES[name];
      const patch = {};
      if (label && field.label !== label) patch.label = label;
      if (field.when) patch.when = null;
      // Show what the site will use when these are left empty.
      if (name === "seotitle") {
        patch.placeholder = (this.values.title || "").trim() || "Uses the post title";
        patch.default = null;
        patch.help = "Leave empty to use the post title. Aim for 50 to 60 characters.";
      }
      if (name === "seodescription") {
        patch.placeholder = this.autoDescription || "Uses the start of the post";
        patch.help = "Leave empty to use the start of the post. Up to 160 characters.";
      }
      if (name === "body" && field.type === "writer") {
        // Force the writer toolbar to be permanent at the top rather
        // than the default floating-on-selection behaviour, and make
        // sure the custom `image` writer node (registered in
        // src/index.js) is in the field's active node list so its
        // toolbar button appears.
        patch.toolbar = { inline: false };
        const nodes = Array.isArray(field.nodes)
          ? [...field.nodes]
          : ["heading", "bulletList", "orderedList", "quote"];
        if (!nodes.includes("image")) nodes.push("image");
        patch.nodes = nodes;
      }
      return Object.keys(patch).length ? { ...field, ...patch } : field;
    },
    onKeydown(event) {
      if ((event.metaKey || event.ctrlKey) && event.key === ".") {
        event.preventDefault();
        this.toggleFocus();
      }
    },
    toggleFocus() {
      this.focusMode = !this.focusMode;
      try {
        localStorage.setItem(FOCUS_KEY, this.focusMode ? "1" : "0");
      } catch (_) {}
      if (this.focusMode) this.collapsePanelMenu();
      else this.restorePanelMenu();
    },
    // Focus mode also folds away Kirby's own left-hand menu, and puts
    // it back as it was on leaving.
    collapsePanelMenu() {
      const menu = this.$panel?.menu;
      if (!menu || typeof menu.toggle !== "function") return;
      // Below 960px the sidebar stacks and focus mode does nothing
      if (!window.matchMedia("(min-width: 960px)").matches) return;
      this.menuWasOpen = menu.isOpen;
      if (menu.isOpen) menu.toggle();
    },
    restorePanelMenu() {
      const menu = this.$panel?.menu;
      if (!menu || this.menuWasOpen === null) return;
      if (this.menuWasOpen && !menu.isOpen) menu.toggle();
      this.menuWasOpen = null;
    },
    sparkTitle() {
      const text = plainText(this.values.body);
      if (text) return excerpt(text, SPARK_TITLE_LENGTH);
      const hasImage = Array.isArray(this.values.image) ? this.values.image.length : !!this.values.image;
      return hasImage ? "Image spark" : "Spark";
    },
    setBodyHeight(h) {
      this.bodyHeight = Math.round(Math.min(BODY_MAX_H, Math.max(BODY_MIN_H, h)));
      try {
        localStorage.setItem(BODY_H_KEY, String(this.bodyHeight));
      } catch (_) {}
    },
    startBodyResize(event) {
      const startY = event.clientY;
      const startH = this.bodyHeight;
      const handle = event.currentTarget;
      handle.setPointerCapture(event.pointerId);
      const move = (e) => this.setBodyHeight(startH + e.clientY - startY);
      const up = () => {
        handle.removeEventListener("pointermove", move);
        handle.removeEventListener("pointerup", up);
        handle.removeEventListener("pointercancel", up);
      };
      handle.addEventListener("pointermove", move);
      handle.addEventListener("pointerup", up);
      handle.addEventListener("pointercancel", up);
    },
    onInput(values) {
      // Merge — k-form's emitted payload only carries fields it knows about,
      // so we preserve `format` (topbar chip) and any other fields not
      // currently rendered by this k-form instance.
      this.values = { ...this.values, ...values };
      this.dirty = true;
      this.scheduleAutosave();
    },
    onFormatChange(newFormat) {
      this.values = { ...this.values, format: newFormat };
      this.dirty = true;
      this.scheduleAutosave();
    },
    scheduleAutosave() {
      if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
      this.autosaveTimer = setTimeout(() => {
        if (this.dirty && !this.isSaving) {
          this.save({ silent: true });
        }
      }, AUTOSAVE_DELAY_MS);
    },
    async save({ silent = false } = {}) {
      if (this.isSaving) return;
      this.isSaving = true;
      if (this.currentFormat === "spark") {
        this.values = { ...this.values, title: this.sparkTitle() };
      }
      try {
        await this.$api.patch(`pages/${this.apiId}`, this.values);
        this.dirty = false;
        this.lastSavedAt = new Date();
        if (!silent) this.$panel.notification.success("Saved");
      } catch (error) {
        console.error("[wove-mind] save failed:", error);
        this.$panel.notification.error(
          "Couldn't save: " + this.errorText(error)
        );
      } finally {
        this.isSaving = false;
      }
    },
    errorText(error) {
      if (!error) return "unknown error";
      if (typeof error === "string") return error;
      const parts = [];
      if (error.message) parts.push(error.message);
      if (error.key) parts.push(`[${error.key}]`);
      if (error.details) {
        try {
          parts.push(JSON.stringify(error.details));
        } catch (_) {}
      }
      return parts.length ? parts.join(" ") : "unknown error";
    },
    async publish() {
      await this.save({ silent: true });
      try {
        await this.$api.patch(`pages/${this.apiId}/status`, {
          status: "listed",
        });
        this.$panel.notification.success("Published");
        // Refresh view props so file URLs, status pill, etc. reflect the new state.
        this.$reload();
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't publish: " + (error.message || "unknown error")
        );
      }
    },
    async unpublish() {
      try {
        await this.$api.patch(`pages/${this.apiId}/status`, {
          status: "draft",
        });
        this.$panel.notification.success("Moved back to draft");
        this.$reload();
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't unpublish: " + (error.message || "unknown error")
        );
      }
    },
    backToList() {
      const proceed = () => this.$go("wove-mind");
      if (!this.dirty) return proceed();
      // Flush pending autosave then leave.
      this.save({ silent: true }).finally(proceed);
    },
    confirmDelete() {
      this.deleteOpen = true;
    },
    async doDelete() {
      if (this.isDeleting) return;
      this.isDeleting = true;
      // Cancel any pending autosave — the page is about to be gone.
      if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
      try {
        await this.$api.delete(`pages/${this.apiId}`);
        this.$panel.notification.success("Entry deleted");
        this.deleteOpen = false;
        this.$go("wove-mind");
      } catch (error) {
        this.isDeleting = false;
        this.$panel.notification.error(
          "Couldn't delete: " + this.errorText(error)
        );
      }
    },
  },
};
</script>
