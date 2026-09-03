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
          @input="onInput"
        />

        <div v-if="hasSeoFields" class="wove-rail__preview">
          <span class="wove-rail__label wove-rail__label--muted">
            Search preview
          </span>
          <k-mind-serp-preview
            :title="values.title"
            :meta-title="values.metaTitle"
            :description="values.metaDescription"
            :slug="slug"
          />
        </div>
      </aside>
    </div>
  </k-panel-inside>
</template>

<script>
import { FORMAT_MAP } from "../formats.js";

// Fields shown in the rail (rest go in the main compose column).
const RAIL_FIELDS = [
  "tags",
  "showByline",
  "service",
  "caseStudy",
  "metaTitle",
  "metaDescription",
];

// Never rendered by k-form — presented as the topbar chip.
const CHIP_FIELDS = ["format"];

const AUTOSAVE_DELAY_MS = 1500;

export default {
  props: {
    entryId: { type: String, required: true },
    isNew: { type: Boolean, default: false },
    initialContent: { type: Object, default: () => ({}) },
    fields: { type: Object, default: null },
    status: { type: String, default: "draft" },
  },
  data() {
    return {
      values: { ...this.initialContent },
      isSaving: false,
      dirty: false,
      lastSavedAt: this.isNew ? null : new Date(),
      autosaveTimer: null,
    };
  },
  computed: {
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
      return Object.fromEntries(
        Object.entries(this.fields).filter(
          ([name]) =>
            !RAIL_FIELDS.includes(name) && !CHIP_FIELDS.includes(name)
        )
      );
    },
    railFields() {
      if (!this.fields) return {};
      return Object.fromEntries(
        Object.entries(this.fields).filter(([name]) =>
          RAIL_FIELDS.includes(name)
        )
      );
    },
    hasSeoFields() {
      return (
        this.railFields.metaTitle || this.railFields.metaDescription
      );
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
      this.values = values;
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
        await this.$api.patch(`pages/${this.entryId}`, this.values);
        this.dirty = false;
        this.lastSavedAt = new Date();
        if (!silent) this.$panel.notification.success("Saved");
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't save: " + (error.message || "unknown error")
        );
      } finally {
        this.isSaving = false;
      }
    },
    async publish() {
      await this.save({ silent: true });
      try {
        await this.$api.post(`pages/${this.entryId}/status`, {
          status: "listed",
        });
        this.$panel.notification.success("Published");
        this.$emit("status-changed", "listed");
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't publish: " + (error.message || "unknown error")
        );
      }
    },
    async unpublish() {
      try {
        await this.$api.post(`pages/${this.entryId}/status`, {
          status: "draft",
        });
        this.$panel.notification.success("Moved back to draft");
        this.$emit("status-changed", "draft");
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't unpublish: " + (error.message || "unknown error")
        );
      }
    },
    backToList() {
      const proceed = () => this.$go("mind");
      if (!this.dirty) return proceed();
      // Flush pending autosave then leave.
      this.save({ silent: true }).finally(proceed);
    },
  },
};
</script>
