/**
 * Wove Mind — inline image node for Kirby's writer field.
 *
 * Registered via `panel.plugin('wove/mind', { writerNodes: { image } })`.
 * Kirby wraps this plain object into a Node instance mixing our
 * definition with the base writer-node prototype, so `this.editor`
 * is populated by Kirby with the current tiptap editor.
 *
 * Data shape:
 *   - Stored in the .txt file as HTML: either a bare
 *     `<img src="…" alt="…" data-filename="…">` or, when there's a
 *     caption, `<figure><img …><figcaption>…</figcaption></figure>`
 *   - parseDOM handles both shapes on load
 *   - Frontend can read the HTML directly; kirbytags() isn't needed
 *     since we emit fully-qualified <img> tags with real URLs
 */
export default {
  get schema() {
    return {
      inline: false,
      group: "block",
      atom: true,
      draggable: true,
      attrs: {
        src:      { default: "" },
        alt:      { default: "" },
        caption:  { default: "" },
        filename: { default: "" },
      },
      parseDOM: [
        // figure + img + optional figcaption
        {
          tag: "figure",
          getAttrs: (dom) => {
            const img = dom.querySelector("img");
            if (!img) return false;
            const cap = dom.querySelector("figcaption");
            return {
              src:      img.getAttribute("src") || "",
              alt:      img.getAttribute("alt") || "",
              caption:  cap ? cap.textContent.trim() : "",
              filename: img.getAttribute("data-filename") || "",
            };
          },
        },
        // bare <img> (older content or no-caption images)
        {
          tag: "img[src]",
          getAttrs: (dom) => ({
            src:      dom.getAttribute("src") || "",
            alt:      dom.getAttribute("alt") || "",
            caption:  "",
            filename: dom.getAttribute("data-filename") || "",
          }),
        },
      ],
      toDOM: (node) => {
        const imgAttrs = {
          src:             node.attrs.src,
          alt:             node.attrs.alt,
          "data-filename": node.attrs.filename,
        };
        if (node.attrs.caption) {
          return [
            "figure",
            ["img", imgAttrs],
            ["figcaption", node.attrs.caption],
          ];
        }
        return ["img", imgAttrs];
      },
    };
  },

  get button() {
    return {
      id:    "image",
      icon:  "image",
      label: "Insert image",
      when:  ["paragraph", "heading"],
    };
  },

  commands({ type }) {
    return () => {
      const view = window?.panel?.view;
      const api  = view?.props?.api;
      if (!api) {
        window?.panel?.notification?.error?.(
          "Can't attach image — the current view has no API endpoint."
        );
        return true;
      }

      const editor = this.editor;

      // The k-mind-image-picker component (Vue instance is mounted
      // once and controlled via events). We access it through
      // window.__woveMindImagePicker (set by EntryEditorView on mount).
      const picker = window.__woveMindImagePicker;
      if (!picker) {
        window?.panel?.notification?.error?.(
          "Image picker isn't ready yet — reload the page and try again."
        );
        return true;
      }

      picker.open(api).then((payload) => {
        if (!payload || !editor?.view) return;
        // Kirby's writer editor uses prosemirror's EditorView under
        // the hood — dispatch lives on `editor.view.dispatch`, not
        // `editor.dispatch`.
        const { state } = editor;
        const nodeType = state.schema.nodes.image;
        if (!nodeType) return;
        const node = nodeType.create(payload);
        editor.view.dispatch(
          state.tr.replaceSelectionWith(node).scrollIntoView()
        );
      });

      return true;
    };
  },
};
