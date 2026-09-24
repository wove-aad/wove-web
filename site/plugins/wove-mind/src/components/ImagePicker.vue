<template>
  <div v-if="isOpen" class="wove-mind wove-picker-overlay" @click.self="close">
    <div class="wove-picker" role="dialog" aria-modal="true">
      <button class="wove-picker__close" @click="close" aria-label="Close">×</button>
      <h2 class="wove-picker__title">Insert image</h2>

      <!-- Source ------------------------------------------------- -->
      <div class="wove-picker__field">
        <span class="wove-picker__label">Image</span>
        <div v-if="!file" class="wove-picker__actions">
          <button class="wove-btn wove-btn--ghost" @click="browse">
            Browse existing
          </button>
          <button class="wove-btn" @click="upload">
            Upload new
          </button>
        </div>
        <div v-else class="wove-picker__preview">
          <img
            v-if="isImage(file)"
            class="wove-picker__thumb"
            :src="file.url"
            :alt="file.filename"
          />
          <div class="wove-picker__filemeta">
            <div class="wove-picker__filename">{{ file.filename }}</div>
            <button class="wove-picker__change" @click="file = null">
              Change
            </button>
          </div>
        </div>
      </div>

      <!-- Alt ---------------------------------------------------- -->
      <div class="wove-picker__field">
        <label class="wove-picker__label" for="wove-img-alt">
          Alt text
        </label>
        <input
          id="wove-img-alt"
          class="wove-picker__input"
          type="text"
          v-model="alt"
          placeholder="What's in this image, for people who can't see it?"
        />
        <div class="wove-picker__help">
          Required for accessibility. Describe the image, not what it is
          ("Grace laughing at a whiteboard" beats "photo of Grace").
        </div>
      </div>

      <!-- Caption ------------------------------------------------ -->
      <div class="wove-picker__field">
        <label class="wove-picker__label" for="wove-img-caption">
          Caption
          <span class="wove-picker__labelhint">optional</span>
        </label>
        <input
          id="wove-img-caption"
          class="wove-picker__input"
          type="text"
          v-model="caption"
          placeholder="Shown below the image on the page"
        />
      </div>

      <!-- Actions ------------------------------------------------ -->
      <div class="wove-picker__foot">
        <button class="wove-btn wove-btn--ghost" @click="close">Cancel</button>
        <button
          class="wove-btn"
          :disabled="!file"
          @click="insert"
          title="Insert into the body"
        >
          Insert
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isOpen: false,
      endpoint: null,
      resolve: null,
      file: null,
      alt: "",
      caption: "",
    };
  },
  methods: {
    // Kirby's file model exposes url + filename + type.
    isImage(file) {
      if (!file) return false;
      const type = file.type || (file.mime || "").split("/")[0];
      return type === "image";
    },
    // Called from the writer node: open(endpoint) → Promise<{src, alt, caption, filename} | null>
    open(endpoint) {
      this.endpoint = endpoint;
      this.file = null;
      this.alt = "";
      this.caption = "";
      this.isOpen = true;
      return new Promise((res) => (this.resolve = res));
    },
    close() {
      if (this.resolve) this.resolve(null);
      this.resolve = null;
      this.isOpen = false;
    },
    browse() {
      this.$panel.dialog.open({
        component: "k-files-dialog",
        props: {
          endpoint: this.endpoint + "/files",
          multiple: false,
        },
        on: {
          cancel: () => this.$panel.dialog.close(),
          submit: (files) => {
            this.$panel.dialog.close();
            const f = Array.isArray(files) ? files[0] : files;
            if (f) this.setFile(f);
          },
        },
      });
    },
    upload() {
      this.$panel.upload.pick({
        url: this.$panel.urls.api + "/" + this.endpoint + "/files",
        accept: "image/*",
        multiple: false,
        on: {
          done: (files) => {
            const f = Array.isArray(files) ? files[0] : files;
            if (f) this.setFile(f);
          },
        },
      });
    },
    setFile(file) {
      this.file = file;
      // Seed alt text from filename if user hasn't typed anything.
      if (!this.alt) {
        this.alt = (file.filename || "")
          .replace(/\.[a-z0-9]+$/i, "")
          .replace(/[-_]+/g, " ");
      }
    },
    insert() {
      if (!this.file) return;
      const payload = {
        src: this.file.url || this.file.link || "",
        alt: this.alt.trim(),
        caption: this.caption.trim(),
        filename: this.file.filename || "",
      };
      if (this.resolve) this.resolve(payload);
      this.resolve = null;
      this.isOpen = false;
    },
  },
};
</script>
