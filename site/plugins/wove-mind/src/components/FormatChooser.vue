<template>
  <div v-if="isOpen" class="wove-mind wove-chooser-overlay" @click.self="close">
    <div class="wove-chooser">
      <button class="wove-chooser__close" @click="close" aria-label="Close">×</button>
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
          <span class="wove-chooser__swatch" :style="{ background: swatchColor(fmt.key) }"></span>
          <span>
            <div class="wove-chooser__name">{{ fmt.name }}</div>
            <div class="wove-chooser__desc">{{ fmt.desc }}</div>
            <div class="wove-chooser__perfect">{{ fmt.perfect }}</div>
          </span>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
import { FORMATS } from "../formats.js";

const SWATCHES = {
  spark: "#C6841E",
  thread: "#2F7A57",
  whatif: "#2A50F3",
  longread: "#6B3EC2",
};

export default {
  data() {
    return {
      isOpen: false,
      formats: FORMATS,
    };
  },
  methods: {
    open() {
      this.isOpen = true;
    },
    close() {
      this.isOpen = false;
    },
    swatchColor(key) {
      return SWATCHES[key] || "#000";
    },
    choose(key) {
      this.close();
      this.$emit("choose", key);
    },
  },
};
</script>
