"""Bundle a prototype page into one self-contained HTML file.

Inlines the stylesheets, scripts and Ballinger fonts so the page opens
correctly on its own (file viewers, artifacts, email).
Usage: python3 build.py            -> writes dist/index.html
"""
import base64, pathlib, re

ROOT = pathlib.Path(__file__).parent
FONTS = ROOT.parent.parent / 'assets' / 'fonts'


def inline_fonts(css):
    def repl(m):
        name = m.group(1)
        data = base64.b64encode((FONTS / name).read_bytes()).decode()
        return f"url('data:font/woff2;base64,{data}') format('woff2')"
    # Keep the woff2 source only; drop the woff fallback.
    css = re.sub(r"url\('[^']*/fonts/([^']+\.woff2)'\) format\('woff2'\)", repl, css)
    return re.sub(r",\s*url\('[^']*/fonts/[^']+\.woff'\) format\('woff'\)", '', css)


def build(page):
    html = (ROOT / page).read_text()
    html = re.sub(r'<link rel="stylesheet" href="([^"]+)">',
                  lambda m: '<style>\n' + inline_fonts((ROOT / m.group(1)).read_text()) + '\n</style>', html)
    html = re.sub(r'<script src="([^"]+)"></script>',
                  lambda m: '<script>\n' + (ROOT / m.group(1)).read_text() + '\n</script>', html)
    out = ROOT / 'dist' / page
    out.parent.mkdir(exist_ok=True)
    out.write_text(html)
    print(f'{out.relative_to(ROOT)}  {out.stat().st_size // 1024} KB')

    # Artifact variant: the host supplies the document shell, so drop it and
    # carry the theme on a wrapper. The prototype is light only for now.
    art = re.sub(r'<!DOCTYPE html>\s*|</?html[^>]*>|</?head>|<meta [^>]*>|</?body[^>]*>', '', html)
    art = art.replace('<a href="#main"', '<div data-theme="light" class="tpl-home">\n<a href="#main"', 1)
    art = art.replace('<script>', '</div>\n<script>', 1)
    art = '<style>:root { color-scheme: light; }</style>\n' + art.strip()
    # <title> must sit in the first 8KB
    title = re.search(r'<title>.*?</title>', art).group(0)
    art = title + '\n' + art.replace(title, '', 1)
    a = ROOT / 'dist' / page.replace('.html', '.artifact.html')
    a.write_text(art)
    print(f'{a.relative_to(ROOT)}  {a.stat().st_size // 1024} KB')


if __name__ == '__main__':
    build('index.html')
