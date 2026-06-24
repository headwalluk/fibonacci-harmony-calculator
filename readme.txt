=== Fibonacci Harmony Calculator ===
Contributors: headwall
Tags: fibonacci, shortcode, calculator, sacred geometry, golden ratio
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

An interactive shortcode that explores the Fibonacci 60 repeating pattern on a circular wheel, recalculating live as you change the seed.

== Description ==

Fibonacci Harmony Calculator adds a single shortcode, `[fibonacci_harmony]`, that renders an interactive calculator and graphic exploring the **Fibonacci 60 Repeating Pattern** (the Pisano-period-60 wheel).

Visitors set a **seed** (a decimal from `0.0` to `2.0`) that scales a Fibonacci-style sequence of N numbers (default 60). The sequence is laid out as a clock face around a circular wheel graphic — index 60 at the top, running clockwise — and every value shows its arc angle in both the standard 360° and the "Ancient" 432° systems. The numbers recalculate live in the browser as the seed changes.

**Features**

* Live, in-browser recalculation — no page reloads, no AJAX round-trips.
* A coarse range slider for quick exploration plus a fine number field that accepts a fully-precise seed down to six decimal places.
* High-precision rows: the top quarter of the sequence shows up to six decimals so finely-tuned decimal seeds stay legible; the rest cap at two.
* Clock / cross layout that hugs the wheel, with North and South call-outs and left/right number tables.
* Compass colouring — cardinal points and quadrant thirds are highlighted, and the colours are overridable with CSS custom properties.
* Responsive: scales down on narrow screens and shows a landscape advisory note.
* Standalone and self-hosted — no build step, no Composer, no external CDNs.
* Developer-friendly: filters (`fhc_default_atts`, `fhc_wheel_image_url`, `fhc_template`, `fhc_output`) and full theme template overrides.
* Translation-ready (text domain `fibonacci-harmony-calculator`).

== Installation ==

1. Upload the `fibonacci-harmony-calculator` folder to `/wp-content/plugins/`, or install the ZIP via **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Add the shortcode `[fibonacci_harmony]` to any post or page.

== Frequently Asked Questions ==

= How do I add the calculator to a page? =

Insert the shortcode `[fibonacci_harmony]` into any post, page, or shortcode-enabled widget. You can pass attributes, e.g. `[fibonacci_harmony seed="0.5" count="60"]`.

= What attributes does the shortcode accept? =

`seed` (decimal `0.0`–`2.0`, default `1.0`), `count` (number of ordinals, default `60`), and `image` (URL of an alternative wheel graphic). See `docs/shortcode.md` for the full reference.

= Can I use my own wheel graphic? =

Yes — pass `image="https://example.com/your-wheel.png"`, or filter the URL globally with `fhc_wheel_image_url`.

= Can a theme override the layout? =

Yes. Copy the plugin's `public-templates/calculator.php` to `yourtheme/fibonacci_harmony/calculator.php` and edit it there, or hook `fhc_template` / `fhc_output`. See `docs/hooks.md`.

= Does it load assets on every page? =

No. The CSS and JavaScript are only enqueued on pages that actually contain the shortcode.

== Screenshots ==

1. The calculator on desktop: number tables flanking the wheel with North/South call-outs and compass colouring.
2. The seed slider and number field driving a live recalculation.

== Changelog ==

= 1.0.0 =
* First stable release.
* Added high-precision display for decimal seeds: the number field accepts a seed to six decimal places, and the top quarter of the sequence shows up to six decimals (others cap at two), with exact integer rounding mirrored in PHP and JavaScript.
* Renamed the 432° angle system from "Asian" to "Ancient" throughout the UI, code, and documentation.

= 0.2.0 =
* Interactive front-end with live recalculation, the desktop clock/cross layout, responsive scaling, and compass colouring.

= 0.1.0 =
* Initial plugin scaffold, the `[fibonacci_harmony]` shortcode, the pure `Calculator`, extension filters, and documentation.

== Upgrade Notice ==

= 1.0.0 =
First stable release. Note: the 432° "Asian" angle system has been renamed to "Ancient" in the display and template markup — update any custom template overrides accordingly.
