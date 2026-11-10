"""One colour grade for every work photo: colour and light only, no pixels of the objects are redrawn.

    python tools/grade_photos.py --preview OUT.png   # before/after sheet, files untouched
    python tools/grade_photos.py --apply             # grades theme photos in place (originals stay in git)

Grade: highlight-based white balance (60 %, clamped +-10 %), common exposure, S-curve,
-20 % saturation, cool shadows. Run --apply on ungraded originals only (not twice). Drawings (RGBA catalog webp) are skipped.
"""
import argparse
import glob
import io
import os
import sys

import numpy as np
from PIL import Image

ROOT = os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'theme', 'karkasvorot', 'assets')
FILES = sorted(glob.glob(os.path.join(ROOT, 'works', '*.jpg'))) + \
    [f for f in sorted(glob.glob(os.path.join(ROOT, 'catalog', '*.webp'))) if Image.open(f).mode == 'RGB'] + \
    [os.path.join(ROOT, 'hero.webp')]


def grade(im: Image.Image) -> Image.Image:
    a = np.asarray(im.convert('RGB')).astype(np.float32) / 255.0
    lum = a.mean(-1)
    # 1. white balance from the brightest 2 % only (sky, white walls), 40 %, gain clamped to +-6 %:
    #    removes a sunset/overcast cast without touching product colours (gray-world turned green fences magenta)
    hi = a[lum >= np.percentile(lum, 98)]
    ref = hi.mean(0) if len(hi) else np.ones(3)
    gain = np.clip(ref.mean() / np.maximum(ref, 1e-3), 0.90, 1.10)
    a = a * (1 + 0.6 * (gain - 1))
    # 2. exposure: pull mean brightness 50 % toward a common 0.46
    m = float(np.clip(a, 0, 1).mean())
    target = m + 0.7 * (0.46 - m)
    a = np.clip(a, 1e-4, 1) ** (np.log(target) / np.log(max(m, 1e-3)))
    # 3. gentle S-curve
    a = a + 0.14 * (a - 0.5) * (1 - np.abs(2 * a - 1))
    # 4. saturation -10 % overall (luma-preserving, no hue shift)
    l = (a * np.array([0.299, 0.587, 0.114])).sum(-1, keepdims=True)
    a = l + (a - l) * 0.80
    # 5. slightly cool shadows
    shadow = np.clip(1 - a.mean(-1, keepdims=True) * 2, 0, 1)
    a = a + shadow * np.array([-0.018, 0.0, 0.030])
    return Image.fromarray((np.clip(a, 0, 1) * 255 + 0.5).astype(np.uint8))


def save(im, path):
    if path.endswith('.webp'):
        im.save(path, 'WEBP', quality=82, method=6)
        return
    q = 84
    while True:  # keep the 350 KB budget of owner photos
        b = io.BytesIO()
        im.save(b, 'JPEG', quality=q, optimize=True)
        if b.tell() <= 350 * 1024 or q <= 60:
            break
        q -= 4
    open(path, 'wb').write(b.getvalue())


def main():
    ap = argparse.ArgumentParser()
    ap.add_argument('--preview')
    ap.add_argument('--apply', action='store_true')
    ap.add_argument('--only', nargs='*')
    args = ap.parse_args()
    files = [f for f in FILES if not args.only or any(o in f for o in args.only)]
    if args.preview:
        rows = []
        for f in files:
            src = Image.open(f).convert('RGB')
            src.thumbnail((640, 360))
            row = Image.new('RGB', (1290, src.height), 'white')
            row.paste(src, (0, 0))
            row.paste(grade(src), (650, 0))
            rows.append(row)
        sheet = Image.new('RGB', (1290, sum(r.height + 8 for r in rows)), 'white')
        y = 0
        for r in rows:
            sheet.paste(r, (0, y))
            y += r.height + 8
        sheet.save(args.preview)
    if args.apply:
        for f in files:
            save(grade(Image.open(f)), f)
            print('graded', os.path.relpath(f, ROOT))
    if not (args.preview or args.apply):
        ap.print_help()
        sys.exit(1)


if __name__ == '__main__':
    main()
