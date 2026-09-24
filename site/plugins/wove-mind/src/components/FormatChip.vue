<template>
  <div class="wove-fmt-chip-wrap">
    <button
      class="wove-fmt-chip"
      :aria-expanded="isOpen"
      aria-haspopup="listbox"
      @click.stop="toggle"
    >
      <span class="wove-fmt-chip__swatch" :style="{ background: swatch(value) }" />
      <span class="wove-fmt-chip__name">{{ nameOf(value) }}</span>
      <span class="wove-fmt-chip__caret">▾</span>
    </button>
    <div v-if="isOpen" class="wove-fmt-menu" role="listbox">
      <button
        v-for="fmt in formats"
        :key="fmt.key"
        class="wove-fmt-item"
        :aria-current="fmt.key === value ? 'true' : 'false'"
        :data-type="fmt.key"
        @click.stop="pick(fmt.key)"
      >
        <span class="wove-fmt-item__swatch" :style="{ background: swatch(fmt.key) }" />
        <span>
          <div class="wove-fmt-item__name">{{ fmt.name }}</div>
          <div class="wove-fmt-item__desc">{{ fmt.desc }}</div>
          <div class="wove-fmt-item__perfect">{{ fmt.perfect }}</div>
        </span>
      </button>
    </div>
  </div>
</template>

<script>
import { FORMATS, FORMAT_MAP } from "../formats.js";

const SWATCHES = {
  spark: "#C6841E",
  thread: "#2F7A57",
  whatif: "#2A50F3",
  longread: "#6B3EC2",
};

export default {
  props: {
    value: { type: String, default: "whatif" },
  },
  data() {
    return {
      isOpen: false,
      formats: FORMATS,
    };
  },
  mounted() {
    document.addEventListener("click", this.closeOnOutside);
  },
  beforeDestroy() {
    document.removeEventListener("click", this.closeOnOutside);
  },
  methods: {
    toggle() { this.isOpen = !this.isOpen; },
    close() { this.isOpen = false; },
    closeOnOutside() { this.isOpen = false; },
    swatch(key) { return SWATCHES[key] || "#000"; },
    nameOf(key) { return FORMAT_MAP[key]?.name || key; },
    pick(key) {
      this.close();
      if (key !== this.value) this.$emit("input", key);
    },
  },
};
</script>
