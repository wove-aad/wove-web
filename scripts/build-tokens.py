#!/usr/bin/env python3
"""Build assets/design-tokens.json from the CSS the site serves.

The stylesheets are the source of truth. This script reads the custom
properties (and the button, pill and tag rules) out of them and writes
them to assets/design-tokens.json, so the JSON always matches what the
site renders. Keys in the JSON that this script doesn't generate
(fontFiles, breakpoints, a11yNotes) are kept as they are.

    python3 scripts/build-tokens.py          # rewrite the JSON
    python3 scripts/build-tokens.py --check  # exit 1 if the JSON is out of date
"""

import json
import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
OUT = ROOT / 'assets/design-tokens.json'

# Token scopes: (key, file, selector, media query or None, description)
SCOPES = [
    ('root', 'assets/css/site.css', ':root', None,
     'Site-wide tokens (site.css :root). Every page.'),
    ('home', 'assets/css/home.css', '.home', None,
     'Overrides for pages inside <div class="home">: the homepage and every redesigned page. '
     'Bold headings, larger radii, the blush hero, post format colours and the page grid.'),
    ('homeFeed', 'assets/css/home.css', '.home .feed-wrap', None,
     'Feed radii follow the .home radii on redesigned pages.'),
    ('feed', 'assets/css/feed.css', '.feed-wrap', None,
     'Feed tokens (--feed-*), dark palette and mobile-first sizes. Used by the post cards, '
     'filters and pinned bar.'),
    ('feedLight', 'assets/css/feed.css', '[data-theme="light"] .feed-wrap', None,
     'Feed light palette. The homepage and redesigned pages set data-theme="light".'),
    ('feedMin600', 'assets/css/feed.css', '.feed-wrap', '(min-width: 600px)', None),
    ('feedMin960', 'assets/css/feed.css', '.feed-wrap', '(min-width: 960px)', None),
    ('feedMin1280', 'assets/css/feed.css', '.feed-wrap', '(min-width: 1280px)', None),
    ('footer', 'assets/css/footer.css', '.ft', None, 'Footer colours (footer.css).'),
]

# Components: rules copied as written, so the JSON shows what each one
# actually uses. (key, file, selectors, description)
COMPONENTS = [
    ('button', 'assets/css/site.css',
     ['.btn', '.btn--sm', '.btn.btn--primary', '.btn.btn--primary:hover',
      '.btn.btn--ghost', '.btn.btn--ghost:hover', '.btn.btn--inverse', '.btn.btn--inverse:hover'],
     'The one button. .btn plus a colourway: --primary (black), --ghost (outlined) or '
     '--inverse (white, on dark grounds). Add .btn--sm for the header "Get in touch".'),
    ('filterPill', 'assets/css/feed.css',
     ['.tag-pill', '.tag-pill:hover', '.tag-pill.is-active'],
     'Topic filter pill. home.css sets 16px text and 12px 24px padding inside .topic-pills.'),
    ('tag', 'assets/css/site.css', ['.tag'], 'Non-interactive tag.'),
]

GENERATED = {'_meta', 'scopes', 'components'}


def strip_comments(css):
    return re.sub(r'/\*.*?\*/', '', css, flags=re.S)


def blocks(css):
    """Yield (media, selector, body) for every rule, one level of @media deep."""
    css = strip_comments(css)
    i, n = 0, len(css)
    stack = []  # media queries we're inside
    start = 0
    while i < n:
        ch = css[i]
        if ch == '{':
            head = css[start:i].strip()
            if head.startswith('@media'):
                stack.append(head[len('@media'):].strip())
                start = i + 1
            else:
                end = css.index('}', i)
                yield (stack[-1] if stack else None), head, css[i + 1:end]
                i = end
                start = i + 1
        elif ch == '}':
            if stack:
                stack.pop()
            start = i + 1
        i += 1


def declarations(body):
    out = {}
    for part in body.split(';'):
        if ':' not in part:
            continue
        name, value = part.split(':', 1)
        name, value = name.strip(), ' '.join(value.split())
        if name:
            out[name] = value
    return out


def find(css, selector, media, custom_only):
    """Merge every rule whose selector list contains `selector` exactly."""
    found = {}
    for m, head, body in blocks(css):
        if m != media:
            continue
        selectors = [s.strip() for s in head.split(',')]
        if selector not in selectors:
            continue
        for k, v in declarations(body).items():
            if custom_only == k.startswith('--'):
                found[k] = v
    return found


def build(existing):
    cache = {}

    def read(path):
        if path not in cache:
            cache[path] = (ROOT / path).read_text(encoding='utf-8')
        return cache[path]

    scopes = {}
    for key, path, selector, media, desc in SCOPES:
        entry = {'source': path, 'selector': selector}
        if media:
            entry['media'] = media
        if desc:
            entry['description'] = desc
        entry['tokens'] = find(read(path), selector, media, custom_only=True)
        if not entry['tokens']:
            sys.exit(f'No tokens found for {selector} {media or ""} in {path}')
        scopes[key] = entry

    components = {}
    for key, path, selectors, desc in COMPONENTS:
        rules = {}
        for sel in selectors:
            decls = find(read(path), sel, None, custom_only=False)
            if not decls:
                sys.exit(f'No rule found for {sel} in {path}')
            rules[sel] = decls
        components[key] = {'source': path, 'description': desc, 'rules': rules}

    data = {
        '_meta': {
            'project': 'Wove.group',
            'about': 'Generated from the site CSS by scripts/build-tokens.py. Do not edit the '
                     'scopes or components by hand: change the CSS, then run the script. '
                     'fontFiles, breakpoints and a11yNotes are written by hand and kept.',
            'howScopesApply': 'root applies everywhere. home overrides it inside .home (the '
                              'homepage and redesigned pages). The feed scopes apply inside '
                              '.feed-wrap; feedLight replaces the dark palette under '
                              'data-theme="light", and feedMin* apply at those widths.',
        },
        'scopes': scopes,
        'components': components,
    }
    for k, v in existing.items():
        if k not in GENERATED:
            data[k] = v
    return json.dumps(data, indent=2, ensure_ascii=False) + '\n'


def main():
    existing = json.loads(OUT.read_text(encoding='utf-8')) if OUT.exists() else {}
    text = build(existing)
    if '--check' in sys.argv:
        if OUT.read_text(encoding='utf-8') != text:
            print(f'{OUT.relative_to(ROOT)} is out of date: run python3 scripts/build-tokens.py')
            sys.exit(1)
        print(f'{OUT.relative_to(ROOT)} is up to date')
        return
    OUT.write_text(text, encoding='utf-8')
    print(f'Wrote {OUT.relative_to(ROOT)}')


if __name__ == '__main__':
    main()
