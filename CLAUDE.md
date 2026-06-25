# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Fibonacci Harmony Calculator is a standalone WordPress plugin that registers a shortcode. The shortcode renders an interactive calculator and graphic that explores the "Fibonacci 60 Repeating Pattern" (the Pisano-period-60 wheel).

The user enters a **seed** (a decimal from `0.0` to `2.1`) that drives the calculation of a Fibonacci-style sequence of N numbers (default 60). The sequence is presented as two columns of digit rows flanking a circular **wheel** graphic with N points, the points starting at top/centre and progressing clockwise.

- **Namespace:** `Fibonacci_Harmony_Calculator`
- **Text Domain:** `fibonacci-harmony-calculator` (matches the plugin slug)
- **Constant prefix:** `FHC_` (e.g. `FHC_DIR`, `FHC_URL`, `FHC_VERSION`)
- **Function / hook prefix:** `fhc_`
- **PHP:** 8.0+ (do NOT use `declare(strict_types=1)` — breaks WordPress interop)
- **WordPress:** 6.0+
- **No build system** — no npm, no Composer, no bundler. Assets are plain CSS/JS.
- **Standalone & self-hosted** — no external CDNs. If a third-party JS library is ever needed, vendor it into `assets/` and enqueue it locally.

> The detailed, evolving functional specification lives in
> [`dev-notes/01-requirements.md`](dev-notes/01-requirements.md). Treat that as the
> source of truth for behaviour; this file covers conventions and architecture.

## Commands

```bash
phpcs                  # Check WordPress coding standards compliance
phpcbf                 # Auto-fix coding standards violations
phpcs includes/        # Check a specific directory
```

Always run `phpcs` before committing. The config is in `phpcs.xml` — uses WordPress
standards with prefixes: `fhc`, `fibonacci_harmony`, `Fibonacci_Harmony_Calculator`.

## Architecture

### Entry Point & Bootstrap

`fibonacci-harmony-calculator.php` is the main plugin file. It guards with
`defined( 'ABSPATH' )`, defines globals (`FHC_DIR`, `FHC_URL`, `FHC_VERSION`),
requires `constants.php` and the class files, then instantiates the main `Plugin`
class and calls `run()`.

### Core Classes (planned)

- **`Plugin`** (`includes/class-plugin.php`) — Orchestrator. Registers all hooks in
  `run()`: the shortcode, asset enqueueing, and any other integration points.
- **`Shortcode`** (`includes/class-shortcode.php`) — Parses shortcode attributes
  (with defaults from `constants.php`), validates them, computes the sequence, and
  renders the public template. Conditionally enqueues front-end assets only on pages
  where the shortcode is present.
- **`Calculator`** (`includes/class-calculator.php`) — Pure computation, no WordPress
  or output concerns. Given a seed and count, returns the sequence plus per-ordinal
  derived values (index, value, arc lengths). Mirrored in JS for live recompute.

### Front-end Behaviour

- The calculation runs **client-side in JavaScript** so the display updates live as
  the user changes the seed — no AJAX round-trip for the core interaction. The PHP
  `Calculator` renders the initial (default-seed) state for no-JS / first paint, and
  the JS recomputes on input. Keep the PHP and JS algorithms identical.
- All JS lives in `assets/` and is enqueued via `wp_enqueue_script()`. **No inline
  JavaScript** in templates. Data is passed to JS via `wp_localize_script()` /
  `data-` attributes on the container.
- JS is **container-scoped** and uses **class selectors** (not IDs) so multiple
  shortcode instances can coexist on one page.

### Templates

- Public markup lives in `public-templates/` and is loaded by the shortcode with
  theme-override support (`locate_template()` then fall back to the plugin copy).
- Templates receive prepared variables and must **escape on output**
  (`esc_html`, `esc_attr`, etc.). Prefer `printf()`/`echo` over mixing PHP snippets
  into HTML.

### Layout

The numbers are never a single flat list — always **two tables + two call-outs** arranged
around the wheel as a clock face (see `dev-notes/01-requirements.md` §4 for the exact rule):

- **North** (above wheel): call-out for index `N` (12 o'clock).
- **Right** of wheel: table for indices `1 … N/2−1`, index 1 at top, increasing downward.
- **South** (below wheel): call-out for index `N/2` (6 o'clock).
- **Left** of wheel: table for indices `N/2+1 … N−1`, smallest at the **bottom**, largest
  at the top (ascending upward, matching clockwise travel up the left side).
- **Mobile:** same clock layout, scaled down to fit, plus an advisory note recommending
  landscape / a larger screen (requirements §4.4).
- Each row / call-out shows: index (1-based), the Fibonacci value, the standard arc
  angle, and the "Ancient" (432°-circle) arc angle.

### Constants

All magic strings, attribute names, and defaults live in `constants.php` under the
`Fibonacci_Harmony_Calculator` namespace. **No magic numbers in code** — the sequence
length (default `60`), the seed range (`0.0`–`2.1`), the full-circle degrees
(`360` standard / `432` Ancient), and the shortcode tag are all named constants.
Convention: `DEF_` for defaults, `ATT_` for shortcode attribute keys.

## Key Conventions

- Register all hooks in `Plugin::run()`; implement behaviour in the relevant class.
- Use constants from `constants.php` — never hardcode the count, degree bases, or
  attribute names.
- Validate and clamp the seed to its allowed range; never trust shortcode/JS input.
- Escape all output; sanitize all input.
- Keep `Calculator` free of WordPress functions so the maths is unit-testable and
  trivially portable to JS.
- Conditionally enqueue assets — only load CSS/JS on pages that actually use the
  shortcode.

## Commit Messages

```
type: brief description

- Detail 1
- Detail 2
```

Types: `feat:` `fix:` `refactor:` `chore:` `docs:` `style:` `test:`

## Extensibility

The shortcode exposes filters for site integrators (all prefixed `fhc_`):
`fhc_default_atts`, `fhc_wheel_image_url`, `fhc_template`, `fhc_output`. Themes can also
override the template at `yourtheme/fibonacci_harmony/calculator.php`. Document any new
hook in `docs/hooks.md`.

## Reference Files

- `docs/shortcode.md` — site-owner usage guide (attributes, examples, layout)
- `docs/hooks.md` — developer hooks/filters and template override
- `dev-notes/01-requirements.md` — full functional specification (source of truth for behaviour)
- `dev-notes/00-project-tracker.md` — current milestones, roadmap, open questions
- `dev-notes/patterns/` — implementation pattern examples (JS, templates, settings, etc.)
- `dev-notes/workflows/` — code-standards and git-commit workflows

<!-- wp-translate:begin v=1.1.0 hash=5250ed5113a25654b49a0bbe58f78d5459f572ac7d67767b6887957b4bc3bc20 -->
## Translating this plugin (wp-translate conventions)

This plugin's `.po`/`.mo` files are generated from source by
[wp-translate](https://github.com/headwalluk/wp-translate-tool), which
machine-translates strings with DeepL. Machine translation is only as good as
the strings you give it — follow these conventions when adding or editing
user-facing text.

### 1. Disambiguate short or ambiguous strings with `_x()`

DeepL handles full sentences well but guesses badly on short, context-free
labels. Give it context with `_x()` (or `esc_html_x()`, `_ex()`):

```php
// Ambiguous out of context — DeepL may read "Sent" as "late", "Folder" as "leaflet"
__( 'Sent', 'fibonacci-harmony-calculator' );

// Disambiguated — the context is passed to the translator and to DeepL
_x( 'Sent', 'email delivery status', 'fibonacci-harmony-calculator' );
_x( 'Folder', 'IMAP mailbox', 'fibonacci-harmony-calculator' );
_x( 'Open', 'verb; button label', 'fibonacci-harmony-calculator' );
```

The context (2nd argument) is never shown to users. Use it whenever a string is a
single word, a short label, or has more than one plausible meaning.

### 2. Use placeholders, never concatenation

Build dynamic text with `printf`/`sprintf` so the whole sentence translates as a
unit, and add a `translators:` comment to explain each placeholder:

```php
/* translators: %s is the user's display name */
printf( esc_html__( 'Welcome back, %s', 'fibonacci-harmony-calculator' ), $name );
```

Never split a sentence across multiple translation calls — word order differs
between languages.

### 3. Acronyms and technical tokens

wp-translate keeps common acronyms (`TLS`, `API`, `SMTP`, `URL`, `ID`, `UTC`, …)
verbatim automatically. If you introduce an unusual acronym or product name that
must not be translated, keep it as its own standalone string so it is recognised,
or ask the maintainer to add it to the tool's acronym list.

### 4. Don't translate dates — let WordPress localise them

Never add month or day-of-week names (full or abbreviated) as translatable
strings. DeepL frequently mistranslates short forms like `Mon`, `Tue`, `Jan`,
`Feb` even with context hints. WordPress already ships locale-aware names — use
`$wp_locale`:

```php
global $wp_locale;
$wp_locale->get_month( $month_number );        // "January" (1-based)
$wp_locale->get_month_abbrev( $month_name );   // "Jan"
$wp_locale->get_weekday( $weekday_number );     // "Monday" (0 = Sunday)
$wp_locale->get_weekday_abbrev( $weekday_name ); // "Mon"
```

For formatted dates, prefer `wp_date()` / `date_i18n()`, which localise month and
day names automatically.

### 5. English source dialect

Write source strings in standard English. wp-translate handles English targets
locally (no DeepL): `en`/`en_US` use the source as-is, and `en_GB`/`en_AU`/… get
American spellings converted to British automatically (`color` → `colour`).

### Running wp-translate

After changing strings, regenerate translations:

```bash
wp-translate /path/to/this-plugin              # auto-detect locales from languages/
wp-translate /path/to/this-plugin en_GB,fr_FR  # explicit locales
wp-translate /path/to/this-plugin --dry-run    # preview; no API calls, no writes
```

Requires WP-CLI (`wp`) and a DeepL API key at `~/.config/deepl.env`. The tool
regenerates the `.pot` from source, translates new/changed strings for each
locale, and compiles the `.mo` files.
<!-- wp-translate:end -->
