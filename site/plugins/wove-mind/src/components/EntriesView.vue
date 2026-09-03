<template>
  <k-panel-inside class="k-panel-view wove-mind">
    <div class="wove-topbar">
      <div class="wove-topbar__left">
        <span class="wove-brand">
          Wove Mind<span class="wove-brand__dot">/</span
          ><span class="wove-brand__crumb">Entries</span>
        </span>
      </div>
      <div class="wove-topbar__right">
        <span class="wove-avatar" :title="$panel.user.name">
          {{ initials($panel.user.name) }}
        </span>
      </div>
    </div>

    <main class="wove-page">
      <header class="wove-page-head">
        <div>
          <div class="wove-eyebrow">The studio's thinking</div>
          <h1 class="wove-h1">Everything we've been writing.</h1>
          <div class="wove-page-sub">
            {{ counts.total }} {{ counts.total === 1 ? "entry" : "entries" }}
            <template v-if="counts.drafts">
              · {{ counts.drafts }}
              {{ counts.drafts === 1 ? "draft" : "drafts" }}
            </template>
          </div>
        </div>
        <div>
          <button class="wove-btn" @click="$refs.chooser.open()">
            <span style="font-size: 15px; line-height: 1">＋</span>
            Write something
          </button>
        </div>
      </header>

      <div class="wove-filters">
        <div class="wove-filters__group">
          <button
            v-for="f in filters"
            :key="f.key"
            class="wove-fchip"
            :data-type="f.type"
            :aria-pressed="activeFilter === f.key"
            @click="activeFilter = f.key"
          >
            <span v-if="f.type" class="wove-fchip__dot" />
            {{ f.label }}
            <span class="wove-fchip__count">{{ f.count }}</span>
          </button>
        </div>
        <div class="wove-filters__spacer" />
        <label class="wove-search">
          <span class="wove-search__glyph" aria-hidden="true">⌕</span>
          <input
            v-model="searchTerm"
            type="search"
            placeholder="Search titles, tags, authors…"
            aria-label="Search entries"
          />
        </label>
      </div>

      <template v-if="filteredEntries.length">
        <template v-for="group in grouped" :key="group.title">
          <div v-if="group.entries.length" class="wove-section-head">
            <span class="wove-section-head__title">{{ group.title }}</span>
            <span class="wove-section-head__count">{{
              group.entries.length
            }}</span>
            <span class="wove-section-head__rule" />
          </div>
          <div v-if="group.entries.length" class="wove-list">
            <a
              v-for="entry in group.entries"
              :key="entry.id"
              class="wove-entry"
              :data-type="entry.format"
              :href="entry.editUrl"
              @click.prevent="openEntry(entry)"
            >
              <div class="wove-entry__type">
                <span class="wove-entry__typelabel">
                  {{ formatName(entry.format) }}
                </span>
                <span class="wove-entry__date">{{ entry.dateLabel }}</span>
              </div>
              <div class="wove-entry__body">
                <h3
                  v-if="entry.format !== 'spark'"
                  class="wove-entry__title"
                >
                  {{ entry.title || "Untitled" }}
                </h3>
                <p v-else class="wove-entry__spark">
                  {{ entry.excerpt || "Untitled spark" }}
                </p>
                <div class="wove-entry__meta">
                  <span>{{ entry.author }}</span>
                  <span
                    v-if="entry.wordCount"
                    style="color: var(--wm-faint)"
                  >
                    · {{ entry.wordCount }} words
                  </span>
                </div>
              </div>
              <div class="wove-entry__aside">
                <span
                  class="wove-status"
                  :class="{ 'wove-status--draft': entry.status === 'draft' }"
                >
                  {{ entry.status === "draft" ? "Draft" : "Live" }}
                </span>
              </div>
            </a>
          </div>
        </template>
      </template>
      <div v-else class="wove-empty">
        Nothing here yet. Click "Write something" to get started.
      </div>
    </main>

    <k-mind-format-chooser ref="chooser" @choose="createEntry" />
  </k-panel-inside>
</template>

<script>
import { FORMAT_MAP } from "../formats.js";

export default {
  props: {
    entries: { type: Array, default: () => [] },
    parent: { type: String, required: true },
  },
  data() {
    return {
      activeFilter: "all",
      searchTerm: "",
    };
  },
  computed: {
    counts() {
      return {
        total: this.entries.length,
        drafts: this.entries.filter((e) => e.status === "draft").length,
        mine: this.entries.filter((e) => e.mine).length,
        spark: this.entries.filter((e) => e.format === "spark").length,
        thread: this.entries.filter((e) => e.format === "thread").length,
        whatif: this.entries.filter((e) => e.format === "whatif").length,
        longread: this.entries.filter((e) => e.format === "longread").length,
      };
    },
    filters() {
      return [
        { key: "all", label: "All", count: this.counts.total },
        { key: "mine", label: "My entries", count: this.counts.mine },
        { key: "drafts", label: "Drafts", count: this.counts.drafts },
        {
          key: "type-spark",
          type: "spark",
          label: "Sparks",
          count: this.counts.spark,
        },
        {
          key: "type-thread",
          type: "thread",
          label: "Threads",
          count: this.counts.thread,
        },
        {
          key: "type-whatif",
          type: "whatif",
          label: "What ifs",
          count: this.counts.whatif,
        },
        {
          key: "type-longread",
          type: "longread",
          label: "Long reads",
          count: this.counts.longread,
        },
      ];
    },
    filteredEntries() {
      const f = this.activeFilter;
      let list = this.entries;
      if (f === "mine") list = list.filter((e) => e.mine);
      else if (f === "drafts")
        list = list.filter((e) => e.status === "draft");
      else if (f.startsWith("type-"))
        list = list.filter((e) => e.format === f.slice(5));

      const q = this.searchTerm.trim().toLowerCase();
      if (q) {
        list = list.filter((e) => {
          const hay = [
            e.title,
            e.excerpt,
            e.author,
            (e.tags || []).join(" "),
          ]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();
          return hay.includes(q);
        });
      }
      return list;
    },
    grouped() {
      return [
        {
          title: "In progress",
          entries: this.filteredEntries.filter((e) => e.status === "draft"),
        },
        {
          title: "Published",
          entries: this.filteredEntries.filter((e) => e.status !== "draft"),
        },
      ];
    },
  },
  methods: {
    formatName(key) {
      return FORMAT_MAP[key]?.name || key;
    },
    initials(name) {
      if (!name) return "?";
      return name
        .split(" ")
        .map((w) => w[0])
        .join("")
        .slice(0, 2)
        .toUpperCase();
    },
    openEntry(entry) {
      this.$go(entry.editUrl);
    },
    async createEntry(format) {
      try {
        // Kirby Panel API encodes ids by swapping "/" for "+".
        const parentId = this.parent.replace(/\//g, "+");
        const response = await this.$api.post(
          `pages/${parentId}/children`,
          {
            template: "mind_entry",
            slug: this.generateSlug(format),
            content: {
              title: format === "spark" ? "Spark" : "Untitled",
              format: format,
            },
          }
        );
        // Response.id is the full page id, e.g. "mind/spark-2026-09-03-abcd"
        const slug = response.slug || response.id.split("/").pop();
        this.$go(`mind/entry/${slug}`);
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't create the entry: " +
            (error.message || error.details || "unknown error")
        );
      }
    },
    generateSlug(format) {
      const date = new Date().toISOString().slice(0, 10);
      const rand = Math.random().toString(36).slice(2, 6);
      return `${format}-${date}-${rand}`;
    },
  },
};
</script>
