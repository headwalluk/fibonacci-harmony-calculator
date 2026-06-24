# Changelog

All notable changes to Fibonacci Harmony Calculator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.0.0] - 2026-06-24

First stable release.

### Added
- **High-precision display for decimal seeds.** The number field now accepts a fully-precise seed down to 6 decimal places (the slider stays at coarse `0.01` steps). The first quarter of the sequence — rows `1 … N/4`, the top of the right-hand table — shows up to **6 decimal places** so a finely-tuned seed stays legible while the values are small; every other row and both call-outs cap at **2 decimal places**, rounded half-up. The rounding is exact integer maths, mirrored byte-for-byte between PHP and the live JavaScript.
- **Translations** for British English (`en_GB`), French (`fr_FR`), German (`de_DE`), and Spanish (`es_ES`) in `languages/`, plus the source `.pot`.
- `readme.txt` in WordPress.org format.

### Changed
- **Renamed the 432° angle system from "Asian" to "Ancient"** throughout the UI, code (`CIRCLE_DEGREES_ANCIENT`, `arc_ancient`), and documentation.
- Raised the internal seed scale to `1,000,000` (`SEED_SCALE`, `SEED_DECIMALS = 6`) to back the new precision while keeping the maths free of floating-point noise.
- The `count` attribute is now clamped to `COUNT_MIN`–`COUNT_MAX` (`1`–`360`) so a stray value can't render a runaway DOM.

## [0.2.0] - 2026-06-24

### Added
- **Interactive front-end** — `assets/fhc-public.css` and `assets/fhc-public.js` bring the calculator to life. The JavaScript mirrors the PHP `Calculator` exactly (BigInt maths, integer-scaled seed) and live-updates every value as the seed changes; the slider and number box stay in sync.
- **Desktop clock/cross layout** — left and right number tables flank a centre column that stacks the North call-out, the wheel, and the South call-out, so the call-outs hug the wheel and the side tables centre against them.
- **Responsive scaling** — a container query (responding to the widget's own width, not just the viewport) scales the layout down on narrow screens and reveals an advisory note.
- **Compass colouring** — rows and call-outs are colour-coded by their standard angle: cardinal points (N/E/S/W) in green, quadrant-third divisions in red. Driven by `fhc-azimuth-{deg}` classes and overridable via the `--fhc-compass-green` / `--fhc-compass-red` custom properties. Only colours angles that an exact ordinal lands on.

### Changed
- The wheel `Calculator` now also returns each ordinal's `azimuth` (the standard-angle compass division it lands on, or none).

## [0.1.0] - 2026-06-24

### Added
- Initial plugin scaffold: bootstrap, namespaced constants, `Plugin` orchestrator, the pure `Calculator` (seed × classic Fibonacci, integer-scaled precision), the `Shortcode` handler with conditional asset enqueue, and the clock-layout public template.
- The `[fibonacci_harmony]` shortcode with `seed`, `count`, and `image` attributes.
- Extension filters: `fhc_default_atts`, `fhc_wheel_image_url`, `fhc_template`, `fhc_output`, plus theme template override support.
- Project documentation: GPLv2 `LICENSE`, `README.md`, `docs/shortcode.md`, `docs/hooks.md`, and the `dev-notes/` requirements and tracker.
