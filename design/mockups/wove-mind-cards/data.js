const ARROW = (s) => `<svg width="${s}" height="${s}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"></path><path d="M13 6l6 6-6 6"></path></svg>`;
const F = {
  case: { name: "Case study", dot: "#111111", tint: "#22221B", ink: "#D8D4C6" },
  spark: { name: "Spark", dot: "#C6841E", tint: "#F7ECD3", ink: "#7A4D0E" },
  thread: { name: "Thread", dot: "#2F7A57", tint: "#E1EEE7", ink: "#1A4D33" },
  whatif: { name: "What if", dot: "#2A50F3", tint: "#EBF0FE", ink: "#1535A8" },
  longread: { name: "Long read", dot: "#6B3EC2", tint: "#EDE6F8", ink: "#4A2A8A" }
};
// Wove Mind feed sample entries (from design/mockups/wove-mind-list.html)
const ENTRIES = [
  { k: "whatif", title: "What if brand was a verb, not a noun?", author: "Grace", ini: "GM", meta: "312 reads", tags: ["#Brand", "#Strategy"], cover: true },
  { k: "spark", title: "Spent an hour just watching how people cross the street here. It's a language.", author: "Zara", ini: "ZA", meta: "12 reads", tags: ["#Cities", "#Observation"], cover: false },
  { k: "longread", title: "The quiet economics of care work", author: "Scott", ini: "SC", meta: "9 min read", tags: ["#Care", "#Economics"], cover: true },
  { k: "thread", title: "Three things clients keep asking that they don't need", author: "Zara", ini: "ZA", meta: "540 words", tags: ["#Practice"], cover: true },
  { k: "spark", title: "Studio Friday. This whiteboard is now a landmark.", author: "Grace", ini: "GM", meta: "Photo", tags: [], cover: true, photo: true },
  { k: "whatif", title: "What if we designed for rest?", author: "Grace", ini: "GM", meta: "420 reads", tags: ["#Wellbeing"], cover: true },
  { k: "longread", title: "Why we stopped pitching against agencies", author: "Scott", ini: "SC", meta: "8 min read", tags: ["#Practice", "#Business"], cover: true },
  { k: "thread", title: "Notes from a week of no meetings", author: "Scott", ini: "SC", meta: "380 words", tags: ["#Focus", "#Team"], cover: false }
];
// Homepage feed: case studies and service-tagged entries (services on entries are assumed)
const HOME_MAIN = [
  { k: "case", title: "[Case study title]", who: "[Client A]", svc: ["Strategy", "Brand"] },
  { k: "longread", title: "The quiet economics of care work", who: "Scott", ini: "SC", meta: "9 min read", svc: ["Strategy"] },
  { k: "whatif", title: "What if brand was a verb, not a noun?", who: "Grace", ini: "GM", meta: "312 reads", svc: ["Brand"] },
  { k: "case", title: "[Case study title]", who: "[Client B]", svc: ["Digital"] },
  { k: "longread", title: "Why we stopped pitching against agencies", who: "Scott", ini: "SC", meta: "8 min read", svc: ["Strategy"] },
  { k: "case", title: "[Case study title]", who: "[Client C]", svc: ["Labs", "Strategy"] },
  { k: "whatif", title: "What if we designed for rest?", who: "Grace", ini: "GM", meta: "420 reads", svc: ["Labs"] }
];
const HOME_SHORT = [
  { k: "thread", title: "Three things clients keep asking that they don't need", who: "Zara", svc: ["Strategy"] },
  { k: "spark", title: "Spent an hour just watching how people cross the street here. It's a language.", who: "Zara", svc: ["Digital"] },
  { k: "thread", title: "Notes from a week of no meetings", who: "Scott", svc: ["Labs"] }
];
function bar(title, controls) {
  return `<div class="bar"><a href="./">All directions</a><span>${title}</span><span class="sp"></span>${controls || ""}</div>`;
}
function shortFormsControl(v) {
  return `<label>Sparks and threads <select data-short>
<option value="condense"${v === "condense" ? " selected" : ""}>Condense</option>
<option value="hide"${v === "hide" ? " selected" : ""}>Hide</option></select></label>`;
}
function homeHead() {
  return `<header class="head"><div><div class="eyebrow">Work and thinking</div><h2 class="h2">[Homepage feed heading]</h2></div><a class="seeall" href="#">See all work ${ARROW(16)}</a></header>`;
}
function mindHead(extra) {
  return `<header class="head"><div><div class="eyebrow">Wove Mind</div><h1 class="h1">Everything we've been writing.</h1></div>${extra || ""}</header>`;
}
