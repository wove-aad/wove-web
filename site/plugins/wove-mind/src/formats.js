export const FORMATS = [
  {
    key: "spark",
    name: "Spark",
    desc: "One thought. Add an image if it needs one.",
    perfect: "Perfect for a sharp observation — or a photo with a caption.",
  },
  {
    key: "thread",
    name: "Thread",
    desc: "A short piece with room to breathe.",
    perfect: "Perfect for reflections and half-formed ideas.",
  },
  {
    key: "whatif",
    name: "What if",
    desc: "A structured provocation — premise, tension, question.",
    perfect: "Perfect for challenging an assumption.",
  },
  {
    key: "longread",
    name: "Long read",
    desc: "A full piece — essay, research, worked-up argument.",
    perfect: "Perfect for a considered piece you'd be happy to see cited.",
  },
];

export const FORMAT_MAP = Object.fromEntries(
  FORMATS.map((f) => [f.key, f])
);
