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
      </div>
      <div style="flex: 1; text-align: center">
        <span class="wove-save-status" :class="saveStateClass">
          <span class="wove-save-status__dot" />
          <span>{{ saveLabel }}</span>
        </span>
      </div>
      <div class="wove-topbar__right">
        <button class="wove-btn wove-btn--ghost" @click="backToList">
          Back
        </button>
        <button class="wove-btn" :disabled="isSaving" @click="save">
          {{ isDraft ? "Save draft" : "Update" }}
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
            v-if="fields"
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
      </aside>
    </div>
  </k-panel-inside>
</template>

<script>
import { FORMAT_MAP } from "../formats.js";

const RAIL_FIELDS = ["tags", "showByline", "service", "caseStudy", "metaTitle", "metaDescription"];

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
    mainFields() {
      if (!this.fields) return {};
      return Object.fromEntries(
        Object.entries(this.fields).filter(
          ([name]) => !RAIL_FIELDS.includes(name)
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
    saveStateClass() {
      if (this.isSaving) return "is-saving";
      if (this.dirty) return "is-dirty";
      return "";
    },
    saveLabel() {
      if (this.isSaving) return "Saving…";
      if (this.dirty) return "Unsaved changes";
      if (this.lastSavedAt) return "Saved just now";
      return "Not saved yet";
    },
  },
  methods: {
    onInput(values) {
      this.values = values;
      this.dirty = true;
    },
    async save() {
      if (this.isSaving) return;
      this.isSaving = true;
      try {
        await this.$api.patch(`pages/${this.entryId}`, this.values);
        this.dirty = false;
        this.lastSavedAt = new Date();
        this.$panel.notification.success("Saved");
      } catch (error) {
        this.$panel.notification.error({
          message: "Couldn't save: " + (error.message || "unknown error"),
        });
      } finally {
        this.isSaving = false;
      }
    },
    async publish() {
      await this.save();
      try {
        await this.$api.post(`pages/${this.entryId}/changeStatus`, {
          status: "listed",
        });
        this.$panel.notification.success("Published");
        this.$emit("status-changed", "listed");
      } catch (error) {
        this.$panel.notification.error({
          message: "Couldn't publish: " + (error.message || "unknown error"),
        });
      }
    },
    async unpublish() {
      try {
        await this.$api.post(`pages/${this.entryId}/changeStatus`, {
          status: "draft",
        });
        this.$panel.notification.success("Moved back to draft");
        this.$emit("status-changed", "draft");
      } catch (error) {
        this.$panel.notification.error({
          message: "Couldn't unpublish: " + (error.message || "unknown error"),
        });
      }
    },
    backToList() {
      if (this.dirty) {
        if (!confirm("You have unsaved changes. Leave anyway?")) return;
      }
      this.$go("mind");
    },
  },
};
</script>
