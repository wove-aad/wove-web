<template>
  <k-panel-inside
    class="k-panel-view wove-mind"
    :data-fmt="currentFormat"
  >
    <div class="wove-topbar">
      <div class="wove-topbar__left">
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
      <div class="wove-topbar__center">
        <span class="wove-save-status" :class="saveStateClass">
          <span class="wove-save-status__dot" />
          <span>{{ saveLabel }}</span>
        </span>
      </div>
      <div class="wove-topbar__right">
        <button class="wove-btn wove-btn--ghost" @click="backToList">
          Back
        </button>
        <a
          v-if="previewUrl"
          class="wove-btn wove-btn--ghost"
          :href="previewUrl"
          target="_blank"
          rel="noopener"
          :title="isDraft ? 'Preview draft on the site' : 'View live on the site'"
        >
          View
        </a>
        <button
          class="wove-btn wove-btn--danger"
          :disabled="isSaving || isDeleting"
          @click="confirmDelete"
          title="Delete this entry"
        >
          Delete
        </button>
        <button
          v-if="isDraft"
          class="wove-btn"
          :disabled="isSaving"
          @click="publish"
        >
          Publish
        </button>
        <button
          v-else
          class="wove-btn wove-btn--ghost"
          :disabled="isSaving"
          @click="unpublish"
        >
          Unpublish
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
          <button class="wove-btn wove-btn--danger" @click="doDelete" :disabled="isDeleting">
            {{ isDeleting ? "Deleting…" : "Delete" }}
          </button>
        </div>
      </div>
    </div>

    <div class="wove-editor">
      <main class="wove-editor__main">
        <div class="wove-editor__compose">
          <div class="wove-fmt-meta">
            <span class="wove-fmt-pill">{{ formatMeta.name }}</span>
            <div class="wove-fmt-desc">{{ formatMeta.desc }}</div>
            <div class="wove-fmt-perfect">{{ formatMeta.perfect }}</div>
          </div>

          <k-form
            v-if="fields && Object.keys(mainFields).length"
            :fields="mainFields"
            :value="values"
            @input="onInput"
          />
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
            :slug="slug"
          />
        </div>
      </aside>
    </div>
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
  whatif:   ["title", "image", "body"],
  longread: ["title", "image", "blocks"],
};
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
    return {
      values: { ...this.initialContent },
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
          if (this.fields[name]) out[name] = this.fields[name];
        }
        return out;
      }
      // Unrestricted: everything not in the rail, chip, or hidden set,
      // in blueprint order. `image` gets spliced in after `title`
      // (magazine-style cover under the title) since the blueprint
      // itself puts image in a sidebar section.
      const entries = Object.entries(this.fields).filter(
        ([name]) =>
          !RAIL_FIELDS.includes(name) &&
          !CHIP_FIELDS.includes(name) &&
          !HIDDEN_FIELDS.includes(name) &&
          name !== "image"
      );
      if (this.fields.image) {
        const titleIdx = entries.findIndex(([n]) => n === "title");
        const insertAt = titleIdx >= 0 ? titleIdx + 1 : 0;
        entries.splice(insertAt, 0, ["image", this.fields.image]);
      }
      return Object.fromEntries(entries);
    },
    railFields() {
      if (!this.fields) return {};
      const allow = RAIL_FIELDS_BY_FORMAT[this.currentFormat];
      const filterHidden = (name) => !HIDDEN_FIELDS.includes(name);
      if (allow) {
        const out = {};
        for (const name of allow) {
          if (this.fields[name] && filterHidden(name)) {
            out[name] = this.fields[name];
          }
        }
        return out;
      }
      return Object.fromEntries(
        Object.entries(this.fields).filter(
          ([name]) => RAIL_FIELDS.includes(name) && filterHidden(name)
        )
      );
    },
    hasSeoFields() {
      return this.railFields.seotitle || this.railFields.seodescription;
    },
    saveStateClass() {
      if (this.isSaving) return "is-saving";
      if (this.dirty) return "is-dirty";
      return "";
    },
    saveLabel() {
      if (this.isSaving) return "Saving…";
      if (this.dirty) return "Unsaved changes";
      if (this.lastSavedAt) return "Saved · autosaves as you type";
      return "Not saved yet";
    },
  },
  beforeDestroy() {
    if (this.autosaveTimer) clearTimeout(this.autosaveTimer);
  },
  methods: {
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
