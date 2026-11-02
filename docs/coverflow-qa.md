# Athletes coverflow — verification, 2026-11-02

The owner subsequently requested **vertical scrolling on phones too**. This
supersedes the original handheld horizontal-swipe specification. Desktop pins
below the 80px header; handheld follows native vertical page scrolling, with
no pin and no snap. Both support buttons, side-card selection and arrow keys.
Reduced motion retains a static grid with no coverflow listeners or transforms.

## Viewports

Checked in isolated headless Chrome using the production build, including
screenshots and DOM geometry at the middle athlete:

| Viewport | Mode | scrollWidth === innerWidth | Names fit | Active card inside viewport, below header | Symmetric side cards |
| --- | --- | --- | --- | --- | --- |
| 1920 × 1080 | Pinned | Pass | Pass | Pass | Pass |
| 1440 × 900 | Pinned | Pass | Pass | Pass | Pass |
| 1366 × 768 | Pinned | Pass | Pass | Pass | Pass |
| 1024 × 768 | Pinned | Pass | Pass | Pass | Pass |
| 768 × 1024 | Native vertical scroll | Pass | Pass | Pass | Pass |
| 390 × 844 | Native vertical scroll | Pass | Pass | Pass | Pass |
| 320 × 640 | Native vertical scroll | Pass | Pass | Pass | Pass |

The roster is finite: at the first/last athlete, neighbours exist on only one
side. Overlapping side cards partially cover their names; each complete name
fits its own card and is fully readable when active.

## Interaction and lifecycle

- Real browser wheel input in both directions through both desktop pin
  boundaries: all 28 sampled deltas followed the input direction, with no
  reversal from snap. Snap settles on the nearest card, without velocity
  extrapolation that can skip cards.
- Rapid repeated Next, Previous, left arrow and a visible side-card click passed.
- Resizing mid-pin from 1440 × 900 to 1366 × 768 retained one pin and the middle
  athlete; the scroll range recalculated to 1306px.
- Closing-image trigger started at 4208px versus the measured expected
  4207.9375px, including the preceding pin spacing.
- Navbar navigation to Schedule mid-pin removed the athlete trigger and pin,
  restored detached card/overlay/progress styles, and returned to the first
  athlete with one fresh pin when navigating back. No browser exceptions.
- CDP touch gestures on the mobile card moved the page 2728 → 2873 → 3018px
  and changed the counter 01 → 02 → 03, with no pin. Previous buttons also passed.
- Reduced motion at 390px and 1440px: one/three grid columns respectively,
  zero page triggers, no card transforms and no interactive coverflow controls.
  Switching to reduced motion during the desktop pin also removed it and
  restored styles; switching back rebuilt one pin.
- Test-only browser roster substitutions at 2 and 10 cards passed on desktop
  and handheld. Production athlete names, images and data were not changed.

## Performance

At 4× CPU throttling, a desktop wheel run recorded 195 animation frames:
95th percentile interval 16.7ms, maximum 16.8ms, and **zero long tasks**.
The DevTools trace recorded three layout events (0.676ms total), rather than
layout on every frame. Transform/opacity updates drive card movement; filter
classes, stacking and counter text change only when the rounded index changes.
Mobile native-touch scrolling and button navigation at 4× CPU also recorded
zero long tasks.

These are automated Chrome measurements, not a guarantee for every device.
No physical iPhone/Android device, Safari, or physical trackpad was tested.
Wheel/touch input was delivered through Playwright/CDP. Temporary screenshots,
measurements and the raw trace are in `/private/tmp/coverflow-qa/`.

## Commands

- `php artisan test`: **203 passed, 2428 assertions**, 18.29s, MariaDB.
- `./vendor/bin/pint --test`: `{"tool":"pint","result":"passed"}`.
- `node --test tests/Frontend/coverflowLayout.test.mjs`: **3 passed**.
- `npm run build`: **passed**, 667 modules, 866ms.

Vite reported six unresolved-at-build-time font URLs. Their files exist under
`public/fonts`, and the browser successfully loaded the local fonts. No font
loading is awaited by animation setup.

No deployment was performed. No dependencies were added and no `.env` changes
are required. No git commands were used.
