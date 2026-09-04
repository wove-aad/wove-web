/**
 * Wove Mind — inline image node for Kirby's writer field.
 *
 * Registered via `panel.plugin('wove/mind', { writerNodes: { image } })`.
 * Kirby wraps this plain object into a Node instance (mixing our
 * definition with the base writer-node prototype), so `this.editor`
 * is populated by Kirby with the current tiptap editor.
 *
 * Data shape:
 *   - Stored in the .txt file as HTML: `<img src="…" alt="…" data-filename="…">`
 *   - Loaded back via parseDOM into the same tiptap node
 *   - Frontend templates read the raw HTML and can pass it through
 *     kirbytags() or render as-is
 */
export default {
  // Prosemirror/tiptap schema for the node.
  get schema() {
    return {
      inline: false,
      group: "block",
      atom: true,
      draggable: true,
      attrs: {
        src:      { default: "" },
        alt:      { default: "" },
        filename: { default: "" },
      },
      parseDOM: [
        {
          tag: "img[src]",
          getAttrs: (dom) => ({
            src:      dom.getAttribute("src") || "",
            alt:      dom.getAttribute("alt") || "",
            filename: dom.getAttribute("data-filename") || "",
          }),
        },
      ],
      toDOM: (node) => [
        "img",
        {
          src:             node.attrs.src,
          alt:             node.attrs.alt,
          "data-filename": node.attrs.filename,
        },
      ],
    };
  },

  // Toolbar button. Kirby wires click → editor.command('image').
  get button() {
    return {
      id:    "image",
      icon:  "image",
      label: "Insert image",
      // "when" tells the toolbar which surrounding nodes make this
      // button applicable. `paragraph` covers the normal writing surface;
      // `heading` lets people insert images between headings.
      when:  ["paragraph", "heading"],
    };
  },

  // Kirby calls this once with prosemirror plumbing. We return a
  // function that, when invoked (by the button click), opens Kirby's
  // file picker and, on submit, inserts the node via the editor Kirby
  // bound to us via bindEditor().
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

      window.panel.dialog.open({
        component: "k-files-dialog",
        props: {
          endpoint: api + "/files",
          multiple: false,
        },
        on: {
          cancel: () => window.panel.dialog.close(),
          submit: (files) => {
            window.panel.dialog.close();
            const file = Array.isArray(files) ? files[0] : files;
            if (!file || !editor) return;
            const attrs = {
              src:      file.url      || file.link     || "",
              alt:      file.alt      || file.filename || "",
              filename: file.filename || "",
            };
            // Kirby's editor exposes `.command()` which routes to
            // registered node commands. Since we're the image node,
            // we need a lower-level insert. Fall back to dispatching
            // a transaction directly.
            const { state, dispatch } = editor;
            const nodeType = state.schema.nodes[type?.name || "image"];
            if (!nodeType) return;
            const node = nodeType.create(attrs);
            const tr = state.tr.replaceSelectionWith(node).scrollIntoView();
            dispatch(tr);
          },
        },
      });

      // We're firing asynchronously; return true so Kirby's toolbar
      // treats the click as handled.
      return true;
    };
  },
};
