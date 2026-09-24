# Team page: exploration

> **Temporary.** Delete this folder once a direction for `/our-people` is chosen.

Four directions for the Our People page. Open `index.html` (on staging:
`/design/mockups/team/`). Each page has a bar at the top with **Dark** and
**No photos** switches, since not everyone will have uploaded a profile image.

| File | Direction |
| --- | --- |
| `a-grid-panel.html` | A: square photo cards, 4 per row, full-width panel below the row (the current build) |
| `b-portrait-drawer.html` | B: portrait wall with a side drawer, next/previous and arrow keys |
| `c-index.html` | C: index list with a cursor-following photo preview and expanding rows |
| `d-flip.html` | D: portrait cards that flip to show bio and posts |

## Notes

- Styles use the values of the `--feed-*` tokens (`assets/css/feed.css`) and load Ballinger from `assets/fonts/`.
- People, bios and posts are placeholders (`data.js`); photos are gradients.
