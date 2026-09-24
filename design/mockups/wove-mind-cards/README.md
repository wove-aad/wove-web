# Wove Mind cards: exploration

> **Temporary.** Delete this folder once the card direction has been resolved.

Card directions for the Wove Mind feed and the homepage feed, exported from the
design canvas: https://claude.ai/artifact/5nh5NajLLgeEWLMSATjUwU

Open `index.html` (on staging: `/design/mockups/wove-mind-cards/`) for
standalone versions of all six directions. They share `shared.css` and
`data.js`, and each page has a bar at the top for its tweaks.

The `.dc.html` files and `canvas.json` are the canvas sources. They need the
canvas runtime to render, so view those on the canvas.

## Wove Mind feed

| File | Direction |
| --- | --- |
| `Main.dc.html` | A: even grid with format filter |
| `Mosaic.dc.html` | B: mosaic sized by format |
| `Index.dc.html` | C: index list with hover preview |

## Homepage feed (case studies and service-tagged Mind entries)

| File | Direction |
| --- | --- |
| `HomeGrid.dc.html` | A: even grid with service filter |
| `HomeMosaic.dc.html` | B: mosaic led by case studies |
| `HomeIndex.dc.html` | C: index list with hover preview |

Each homepage board has a `shortForms` tweak (`condense` or `hide`) for sparks
and threads, which are still undecided.

## Notes

- Inter stands in for Ballinger, as the font files are not in the repo.
- Case study content, covers, decks and what-if structure are placeholders.
- Service tags on the sample Mind entries are assumed.
