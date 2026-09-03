import "../assets/tokens.css";
import "./styles.css";

import EntriesView from "./components/EntriesView.vue";
import EntryEditorView from "./components/EntryEditorView.vue";
import FormatChooser from "./components/FormatChooser.vue";

panel.plugin("wove/mind", {
  components: {
    "k-mind-entries-view": EntriesView,
    "k-mind-editor-view": EntryEditorView,
    "k-mind-format-chooser": FormatChooser,
  },
});
