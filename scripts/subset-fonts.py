#!/usr/bin/env python3
"""
Rebuild the self-hosted woff2 subsets from the files already in public/fonts.

The fonts on the critical path are the whole point of this rebuild, so they carry
only what this site draws: every printable ASCII character, the punctuation the
design uses, and the Greek block — not the accented Latin letters a Greek school
never types. The weight axis is cut to the range the stylesheet declares.

Needs fonttools and brotli, which are not part of the app:

    python3 -m venv .venv-fonts && .venv-fonts/bin/pip install fonttools brotli
    .venv-fonts/bin/python scripts/subset-fonts.py public/fonts-rebuilt

Then copy the result over public/fonts/ and commit both the fonts and the
regenerated resources/css/font-coverage.generated.json, whose ranges the
stylesheet must match.
"""
import os, sys, glob
from fontTools.ttLib import TTFont
from fontTools.varLib import instancer
from fontTools.subset import Subsetter, Options

# Every printable ASCII, plus the punctuation and symbols this design actually uses.
# Deliberately NOT the accented Latin-1 letters: a Greek school's Latin text is
# English words, brand names and numbers. The unicode-range is narrowed to match,
# so anything outside it falls back rather than rendering as tofu.
LATIN = set(range(0x20, 0x7F)) | {
    0x00A0, 0x00A9, 0x00AB, 0x00BB, 0x00B0, 0x00B7, 0x00D7,
    0x2013, 0x2014, 0x2018, 0x2019, 0x201C, 0x201D, 0x2026,
    0x2039, 0x203A, 0x20AC, 0x2122, 0x2192, 0x2197, 0x2193, 0x2191,
    0x2212, 0x2215, 0x25B6, 0xFEFF, 0xFFFD,
}

# The Greek files carry a few stray Latin glyphs from their original subsetting.
# Declaring those would make a browser fetch the Greek file to draw an "A".
GREEK = set(range(0x0370, 0x0400)) | {0x20, 0x00A0}

PLAN = {
    'inter-latin-400-900':            (400, 900, LATIN),
    'inter-greek-400-900':            (400, 900, GREEK),
    'roboto-condensed-latin-700-900': (700, 900, LATIN),
    'roboto-condensed-greek-700-900': (700, 900, GREEK),
    'jetbrains-mono-latin-400-700':   (400, 700, LATIN),
    'jetbrains-mono-greek-400-700':   (400, 700, GREEK),
}

def fmt_ranges(cps):
    cps = sorted(cps)
    if not cps:
        return ''
    out, start, prev = [], cps[0], cps[0]
    for c in cps[1:]:
        if c == prev + 1:
            prev = c
            continue
        out.append((start, prev))
        start = prev = c
    out.append((start, prev))
    return ', '.join(f"U+{a:04X}" if a == b else f"U+{a:04X}-{b:04X}" for a, b in out)


out_dir = sys.argv[1]
os.makedirs(out_dir, exist_ok=True)
rows = []
for path in sorted(glob.glob('public/fonts/*/*.woff2')):
    stem = os.path.basename(path)[:-6]
    lo, hi, keep = PLAN[stem]
    before = os.path.getsize(path)
    f = TTFont(path)
    have = set(f.getBestCmap().keys())
    unis = sorted(have & keep) if keep else sorted(have)
    opts = Options()
    opts.layout_features = ['*']
    opts.name_IDs = ['*']
    opts.notdef_outline = True
    opts.drop_tables = []
    sub = Subsetter(options=opts)
    sub.populate(unicodes=unis)
    sub.subset(f)
    f = instancer.instantiateVariableFont(f, {'wght': (lo, lo, hi)}, updateFontNames=False)
    f.flavor = 'woff2'
    dest = os.path.join(out_dir, os.path.basename(path))
    f.save(dest)
    after = os.path.getsize(dest)
    rows.append((stem, before, after, len(have), len(TTFont(dest).getBestCmap())))

total_b = sum(r[1] for r in rows); total_a = sum(r[2] for r in rows)
print(f"{'file':34s} {'before':>8s} {'after':>8s} {'saved':>8s}  {'cmap':>10s}")
for stem, b, a, cb, ca in rows:
    print(f"{stem:34s} {b:8d} {a:8d} {b-a:8d}  {cb:4d} -> {ca:3d}")
print(f"{'TOTAL':34s} {total_b:8d} {total_a:8d} {total_b-total_a:8d}   ({100*(total_b-total_a)/total_b:.1f}% off)")

# Record exactly what ended up in each file, so the stylesheet can be checked
# against the fonts rather than against a second hand-written declaration.
import hashlib, json
record = {}
for path in sorted(glob.glob(os.path.join(out_dir, '*.woff2'))):
    stem = os.path.basename(path)[:-6]
    have = sorted(set(TTFont(path).getBestCmap()) - {0x20, 0x00A0} if 'greek' in stem
                  else set(TTFont(path).getBestCmap()))
    record[stem] = {
        'unicodeRange': fmt_ranges(have),
        'sha256': hashlib.sha256(open(path, 'rb').read()).hexdigest(),
        'bytes': os.path.getsize(path),
    }
with open('resources/css/font-coverage.generated.json', 'w') as fh:
    json.dump(record, fh, indent=4, sort_keys=True)
    fh.write('\n')
print('\nwrote resources/css/font-coverage.generated.json')
