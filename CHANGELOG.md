# Changelog

All notable changes to Fibonacci Harmony Calculator will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

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
