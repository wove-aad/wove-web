<template>
  <k-panel-inside class="k-panel-view wove-mind">
    <div class="wove-topbar wove-topbar--split">
      <div class="wove-topbar__left">
        <span class="wove-brand">
          Wove Mind<span class="wove-brand__dot">/</span
          ><span class="wove-brand__crumb">Entries</span>
        </span>
      </div>
      <div class="wove-topbar__right">
        <span class="wove-avatar" :title="viewer.name">
          <img v-if="viewer.avatar" :src="viewer.avatar" alt="" />
          <template v-else>{{ initials(viewer.name) }}</template>
        </span>
      </div>
    </div>

    <main class="wove-page">
      <header class="wove-page-head">
        <div>
          <h1 class="wove-h1">Everything we've been writing</h1>
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
            <k-icon type="add" />
            <span>Write something</span>
          </button>
        </div>
      </header>

      <div class="wove-filters">
        <div class="wove-filters__group">
          <button
            v-for="f in filters"
            :key="f.key"
            class="wove-fchip"
            :aria-pressed="activeFilter === f.key ? 'true' : 'false'"
            @click="activeFilter = f.key"
          >
            {{ f.label }}
            <span class="wove-fchip__count">{{ f.count }}</span>
          </button>

          <div class="wove-dropdown" @click.stop>
            <button
              class="wove-fchip"
              :data-type="activeType"
              :aria-pressed="activeType ? 'true' : 'false'"
              :aria-expanded="menu === 'type' ? 'true' : 'false'"
              aria-haspopup="menu"
              @click="toggleMenu('type')"
            >
              <span v-if="activeType" class="wove-fchip__dot" />
              {{ activeType ? typePlural(activeType) : "All types" }}
              <k-icon type="angle-down" class="wove-fchip__caret" />
            </button>
            <div v-if="menu === 'type'" class="wove-menu" role="menu">
              <button
                class="wove-menu__item"
                role="menuitemradio"
                :aria-checked="!activeType ? 'true' : 'false'"
                @click="setType(null)"
              >
                <span class="wove-menu__dot" />
                All types
                <span class="wove-menu__count">{{ counts.total }}</span>
              </button>
              <button
                v-for="t in types"
                :key="t.key"
                class="wove-menu__item"
                role="menuitemradio"
                :data-type="t.key"
                :aria-checked="activeType === t.key ? 'true' : 'false'"
                @click="setType(t.key)"
              >
                <span class="wove-menu__dot" />
                {{ t.plural }}
                <span class="wove-menu__count">{{ counts[t.key] }}</span>
              </button>
            </div>
          </div>
        </div>
        <div class="wove-filters__spacer" />
        <label class="wove-search">
          <k-icon type="search" class="wove-search__icon" />
          <input
            ref="search"
            v-model="searchTerm"
            type="search"
            placeholder="Search titles, tags, authors"
            aria-label="Search entries"
            aria-keyshortcuts="Meta+K Control+K"
            @keydown.esc="searchTerm = ''"
          />
          <kbd class="wove-search__kbd" aria-hidden="true">{{ shortcutLabel }}</kbd>
        </label>
      </div>

      <template v-if="filteredEntries.length">
        <template v-for="group in grouped">
          <div :key="group.key + '-head'" class="wove-section-head">
            <span class="wove-section-head__title">{{ group.title }}</span>
            <span class="wove-section-head__count">{{
              group.entries.length
            }}</span>
            <span class="wove-section-head__rule" />
          </div>
          <div :key="group.key" class="wove-list">
            <div
              v-for="entry in group.entries"
              :key="entry.id"
              class="wove-entry"
              :class="{ 'is-menu-open': menu === 'status:' + entry.id }"
              :data-type="entry.format"
            >
              <a
                class="wove-entry__link"
                :href="entry.editUrl"
                :aria-label="'Edit ' + displayTitle(entry)"
                @click.prevent="openEntry(entry)"
              />
              <div class="wove-entry__type">
                <span class="wove-entry__typelabel">
                  {{ typeLabel(entry.format) }}
                </span>
                <span class="wove-entry__date">{{ entry.dateLabel }}</span>
              </div>
              <div class="wove-entry__thumb">
                <img v-if="entry.thumb" :src="entry.thumb" alt="" loading="lazy" />
              </div>
              <div class="wove-entry__body">
                <h3
                  v-if="entry.format !== 'spark'"
                  class="wove-entry__title"
                >
                  {{ displayTitle(entry) }}
                </h3>
                <p
                  v-else
                  class="wove-entry__spark"
                  :class="{ 'is-placeholder': !entry.excerpt }"
                >{{ displayTitle(entry) }}</p>
                <div class="wove-entry__meta">
                  <span class="wove-avatar wove-avatar--sm">
                    <img v-if="entry.avatar" :src="entry.avatar" alt="" />
                    <template v-else>{{ initials(entry.author) }}</template>
                  </span>
                  <span>{{ entry.author }}</span>
                  <span v-if="entry.wordCount" class="wove-entry__words">
                    · {{ entry.wordCount }}
                    {{ entry.wordCount === 1 ? "word" : "words" }}
                  </span>
                </div>
              </div>
              <div class="wove-entry__aside">
                <div class="wove-entry__actions">
                  <a
                    v-if="entry.viewUrl"
                    class="wove-btn wove-btn--ghost wove-btn--sm"
                    :href="entry.viewUrl"
                    target="_blank"
                    rel="noopener"
                    :title="entry.status === 'draft' ? 'Preview draft on the site' : 'View live on the site'"
                  >
                    <k-icon type="open" />
                    <span>View</span>
                  </a>
                  <button
                    class="wove-btn wove-btn--ghost wove-btn--sm"
                    @click="openEntry(entry)"
                  >
                    <k-icon type="edit" />
                    <span>Edit</span>
                  </button>
                </div>
                <div class="wove-dropdown" @click.stop>
                  <button
                    class="wove-status"
                    :class="{ 'wove-status--draft': entry.status === 'draft' }"
                    :disabled="busyId === entry.id"
                    :aria-expanded="menu === 'status:' + entry.id ? 'true' : 'false'"
                    aria-haspopup="menu"
                    :title="'Change status'"
                    @click="toggleMenu('status:' + entry.id)"
                  >
                    {{ entry.status === "draft" ? "Draft" : "Live" }}
                    <k-icon type="angle-down" class="wove-status__caret" />
                  </button>
                  <div
                    v-if="menu === 'status:' + entry.id"
                    class="wove-menu wove-menu--right"
                    role="menu"
                  >
                    <button
                      class="wove-menu__item"
                      role="menuitemradio"
                      data-status="live"
                      :aria-checked="entry.status !== 'draft' ? 'true' : 'false'"
                      @click="setStatus(entry, 'listed')"
                    >
                      <span class="wove-menu__dot" />
                      <span>
                        Live
                        <span class="wove-menu__hint">Published on the site</span>
                      </span>
                    </button>
                    <button
                      class="wove-menu__item"
                      role="menuitemradio"
                      data-status="draft"
                      :aria-checked="entry.status === 'draft' ? 'true' : 'false'"
                      @click="setStatus(entry, 'draft')"
                    >
                      <span class="wove-menu__dot" />
                      <span>
                        Draft
                        <span class="wove-menu__hint">Only visible in the Panel</span>
                      </span>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </template>
      <div v-else-if="error" class="wove-empty">
        {{ error }}
      </div>
      <div v-else class="wove-empty">
        <p class="wove-empty__text">{{ emptyText }}</p>
        <div class="wove-empty__actions">
          <button
            v-if="hasFilters"
            class="wove-btn wove-btn--ghost"
            @click="clearFilters"
          >
            Clear filters
          </button>
          <button class="wove-btn" @click="writeFromEmpty">
            <k-icon type="add" />
            <span>{{ activeType ? "Write a " + typeLabel(activeType).toLowerCase() : "Write something" }}</span>
          </button>
        </div>
      </div>
    </main>

    <div v-if="unpublishing" class="wove-confirm-overlay" @click.self="unpublishing = null">
      <div class="wove-confirm" role="dialog" aria-modal="true">
        <h3 class="wove-confirm__title">Move to draft?</h3>
        <p class="wove-confirm__body">
          <strong>{{ displayTitle(unpublishing) }}</strong>
          will be taken off the site until you publish it again.
        </p>
        <div class="wove-confirm__actions">
          <button class="wove-btn wove-btn--ghost" @click="unpublishing = null">
            Cancel
          </button>
          <button class="wove-btn" @click="changeStatus(unpublishing, 'draft')">
            Move to draft
          </button>
        </div>
      </div>
    </div>

    <k-mind-format-chooser ref="chooser" @choose="createEntry" />
  </k-panel-inside>
</template>

<script>
import { FORMATS, FORMAT_MAP } from "../formats.js";

const PLURALS = {
  spark: "Sparks",
  thread: "Threads",
  whatif: "What ifs",
  longread: "Long reads",
};

export default {
  props: {
    entries: { type: Array, default: () => [] },
    parent: { type: String, required: true },
    viewer: { type: Object, default: () => ({ name: "", avatar: null }) },
    error: { type: String, default: null },
  },
  data() {
    return {
      activeFilter: "all",
      activeType: null,
      searchTerm: "",
      // Which dropdown is open: "type", "status:<id>" or null
      menu: null,
      busyId: null,
      unpublishing: null,
    };
  },
  computed: {
    types() {
      return FORMATS.map((f) => ({ key: f.key, plural: PLURALS[f.key] || f.name }));
    },
    counts() {
      const c = {
        total: this.entries.length,
        drafts: this.entries.filter((e) => e.status === "draft").length,
        mine: this.entries.filter((e) => e.mine).length,
      };
      for (const f of FORMATS) {
        c[f.key] = this.entries.filter((e) => e.format === f.key).length;
      }
      return c;
    },
    filters() {
      return [
        { key: "all", label: "All", count: this.counts.total },
        { key: "mine", label: "My entries", count: this.counts.mine },
        { key: "drafts", label: "Drafts", count: this.counts.drafts },
      ];
    },
    hasFilters() {
      return (
        this.activeFilter !== "all" ||
        this.activeType !== null ||
        this.searchTerm.trim() !== ""
      );
    },
    filteredEntries() {
      const f = this.activeFilter;
      let list = this.entries;
      if (f === "mine") list = list.filter((e) => e.mine);
      else if (f === "drafts") list = list.filter((e) => e.status === "draft");
      if (this.activeType) list = list.filter((e) => e.format === this.activeType);

      const q = this.searchTerm.trim().toLowerCase();
      if (q) {
        list = list.filter((e) => {
          const hay = [e.title, e.excerpt, e.author, (e.tags || []).join(" ")]
            .filter(Boolean)
            .join(" ")
            .toLowerCase();
          return hay.includes(q);
        });
      }
      return list;
    },
    // Drafts first, then published entries by month, newest first.
    grouped() {
      const byTime = (a, b) => (b.timestamp || 0) - (a.timestamp || 0);
      const groups = [];
      const drafts = this.filteredEntries
        .filter((e) => e.status === "draft")
        .sort(byTime);
      if (drafts.length) {
        groups.push({ key: "drafts", title: "In progress", entries: drafts });
      }
      const published = this.filteredEntries
        .filter((e) => e.status !== "draft")
        .sort(byTime);
      for (const entry of published) {
        const last = groups[groups.length - 1];
        if (last && last.key === "month:" + entry.monthLabel) {
          last.entries.push(entry);
        } else {
          groups.push({
            key: "month:" + entry.monthLabel,
            title: entry.monthLabel,
            entries: [entry],
          });
        }
      }
      return groups;
    },
    emptyText() {
      if (!this.entries.length) {
        return "Nothing here yet. Write the first entry to get started.";
      }
      const what = this.activeType ? PLURALS[this.activeType].toLowerCase() : "entries";
      if (this.searchTerm.trim()) {
        return `No ${what} match "${this.searchTerm.trim()}".`;
      }
      if (this.activeFilter === "mine") return `You haven't written any ${what} yet.`;
      if (this.activeFilter === "drafts") return `No ${what} in draft.`;
      return `No ${what} yet.`;
    },
    shortcutLabel() {
      const mac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
      return mac ? "⌘K" : "Ctrl K";
    },
  },
  mounted() {
    document.addEventListener("click", this.closeMenu);
    document.addEventListener("keydown", this.onKeydown);
  },
  beforeDestroy() {
    document.removeEventListener("click", this.closeMenu);
    document.removeEventListener("keydown", this.onKeydown);
  },
  methods: {
    typeLabel(key) {
      return FORMAT_MAP[key]?.name || key;
    },
    typePlural(key) {
      return PLURALS[key] || this.typeLabel(key);
    },
    displayTitle(entry) {
      if (entry.format === "spark") return entry.sparkText || "Empty spark";
      return entry.title || "Untitled";
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
    toggleMenu(name) {
      this.menu = this.menu === name ? null : name;
    },
    closeMenu() {
      this.menu = null;
    },
    onKeydown(event) {
      if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();
        this.$refs.search?.focus();
        this.$refs.search?.select();
      } else if (event.key === "Escape" && this.menu) {
        this.menu = null;
      }
    },
    setType(key) {
      this.activeType = key;
      this.menu = null;
    },
    clearFilters() {
      this.activeFilter = "all";
      this.activeType = null;
      this.searchTerm = "";
    },
    writeFromEmpty() {
      if (this.activeType) this.createEntry(this.activeType);
      else this.$refs.chooser.open();
    },
    openEntry(entry) {
      this.$go(entry.editUrl);
    },
    setStatus(entry, status) {
      this.menu = null;
      const isDraft = entry.status === "draft";
      if ((status === "draft") === isDraft) return;
      // Taking a post off the site gets a confirm step
      if (status === "draft") this.unpublishing = entry;
      else this.changeStatus(entry, status);
    },
    async changeStatus(entry, status) {
      this.unpublishing = null;
      this.busyId = entry.id;
      try {
        await this.$api.patch(`pages/${entry.id.replace(/\//g, "+")}/status`, { status });
        this.$panel.notification.success(status === "draft" ? "Moved to draft" : "Published");
        this.$reload();
      } catch (error) {
        this.$panel.notification.error(
          "Couldn't change the status: " + (error.message || "unknown error")
        );
      } finally {
        this.busyId = null;
      }
    },
    async createEntry(format) {
      try {
        // Kirby Panel API encodes ids by swapping "/" for "+".
        const parentId = this.parent.replace(/\//g, "+");
        // Auto-attribute the entry to the signed-in contributor so the
        // Author field in the blueprint doesn't need to be edited.
        const authorId = this.$panel?.user?.id;
        const response = await this.$api.post(
          `pages/${parentId}/children`,
          {
            template: "wove-mind-entry",
            slug: this.generateSlug(format),
            content: {
              title: format === "spark" ? "Spark" : "Untitled",
              format: format,
              // Stops the SEO tab's `{{ page.title }}` default filling
              // in the slug. Empty falls back to the title on the site.
              seotitle: "",
              ...(authorId ? { author: [authorId] } : {}),
            },
          }
        );
        // Response.id is the full page id, e.g. "wove-mind/spark-2026-09-03-abcd"
        const slug = response.slug || response.id.split("/").pop();
        this.$go(`wove-mind/entry/${slug}`);
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
