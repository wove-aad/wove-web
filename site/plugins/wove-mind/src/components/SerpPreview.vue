<template>
  <div class="wove-serp">
    <div class="wove-serp__label">Google preview</div>
    <div class="wove-serp__title">{{ displayTitle }}</div>
    <div class="wove-serp__url">{{ displayUrl }}</div>
    <div class="wove-serp__desc">{{ displayDesc }}</div>
  </div>
</template>

<script>
export default {
  props: {
    title: { type: String, default: "" },
    seoTitle: { type: String, default: "" },
    seoDescription: { type: String, default: "" },
    slug: { type: String, default: "" },
    // Old-name fallbacks in case the blueprint uses camelCase.
    metaTitle: { type: String, default: "" },
    metaDescription: { type: String, default: "" },
  },
  computed: {
    displayTitle() {
      return (
        (this.seoTitle || "").trim() ||
        (this.metaTitle || "").trim() ||
        (this.title || "").trim() ||
        "Untitled entry"
      );
    },
    displayUrl() {
      const s = (this.slug || "").trim() || "your-post";
      return `wove.group/wove-mind/${s}`;
    },
    displayDesc() {
      const d =
        (this.seoDescription || "").trim() ||
        (this.metaDescription || "").trim();
      if (d) return d;
      return "Add a meta description to preview how this post appears in search results.";
    },
  },
};
</script>
