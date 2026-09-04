import "../assets/tokens.css";
import "./styles.css";

import EntriesView from "./components/EntriesView.vue";
import EntryEditorView from "./components/EntryEditorView.vue";
import FormatChooser from "./components/FormatChooser.vue";
import FormatChip from "./components/FormatChip.vue";
import ImagePicker from "./components/ImagePicker.vue";
import SerpPreview from "./components/SerpPreview.vue";
import imageWriterNode from "./writerNodes/image.js";

panel.plugin("wove/mind", {
  components: {
    "k-mind-entries-view": EntriesView,
    "k-mind-editor-view": EntryEditorView,
    "k-mind-format-chooser": FormatChooser,
    "k-mind-format-chip": FormatChip,
    "k-mind-image-picker": ImagePicker,
    "k-mind-serp-preview": SerpPreview,
  },
  writerNodes: {
    image: imageWriterNode,
  },
});
