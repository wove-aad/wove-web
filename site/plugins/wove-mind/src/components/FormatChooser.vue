<template>
  <k-dialog
    ref="dialog"
    :cancel-button="false"
    :submit-button="false"
    size="medium"
  >
    <div class="wove-mind wove-chooser">
      <h2 class="wove-chooser__title">Start something new</h2>
      <p class="wove-chooser__sub">Pick the shape it wants to take — you can change your mind later.</p>
      <div class="wove-chooser__list">
        <button
          v-for="fmt in formats"
          :key="fmt.key"
          class="wove-chooser__item"
          :data-type="fmt.key"
          @click="choose(fmt.key)"
        >
          <span class="wove-chooser__swatch" :style="{ background: `var(--wove-${fmt.key})` }" />
          <span>
            <div class="wove-chooser__name">{{ fmt.name }}</div>
            <div class="wove-chooser__desc">{{ fmt.desc }}</div>
            <div class="wove-chooser__perfect">{{ fmt.perfect }}</div>
          </span>
        </button>
      </div>
    </div>
  </k-dialog>
</template>

<script>
import { FORMATS } from "../formats.js";

export default {
  data() {
    return {
      formats: FORMATS,
    };
  },
  methods: {
    open() {
      this.$refs.dialog.open();
    },
    async choose(key) {
      this.$refs.dialog.close();
      this.$emit("choose", key);
    },
  },
};
</script>
