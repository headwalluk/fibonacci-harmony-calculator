# Requirements — Fibonacci Harmony Calculator

**Status:** Confirmed with client (2026-06-24)
**Last Updated:** 2026-06-24

This is the source of truth for the plugin's behaviour. `CLAUDE.md` covers conventions
and architecture; this document covers *what it does*.

---

## 1. Summary

A standalone WordPress plugin that registers a shortcode. The shortcode renders an
interactive calculator + graphic exploring the "Fibonacci 60 Repeating Pattern". The
user enters a **seed** that drives a Fibonacci-style sequence of N numbers (default 60),
displayed as two columns of rows flanking a circular **wheel** graphic.

The interaction is **live and client-side**: changing the seed recomputes and re-renders
the number columns instantly, with no page reload and no AJAX round-trip.

---

## 2. The calculation

### 2.1 Sequence definition

Given a seed `s` and a count `N`:

```
F(1) = s
F(2) = s
F(n) = F(n-1) + F(n-2)   for n >= 3
```

This is equivalent to `s × classic_Fibonacci(n)`, where classic Fibonacci is
`1, 1, 2, 3, 5, 8, …`. Therefore:

- `s = 1.0` → `1, 1, 2, 3, 5, 8, 13, …` (the classic sequence)
- `s = 2.0` → `2, 2, 4, 6, 10, 16, 26, …`
- `s = 0.5` → `0.5, 0.5, 1, 1.5, 2.5, 4, 6.5, …`
- `s = 0.0` → `0, 0, 0, …`

The sequence is **1-indexed** in all display (index 1 … N), never 0-indexed.

### 2.2 Seed

- Range: `0.0` to `2.0` inclusive.
- Step: the **range slider** drags in coarse `0.01` increments; the **number field**
  accepts a fully-precise decimal down to `0.000001` (6 places) so a client can type
  an exact seed such as `1.234567`.
- Default: `1.0`.
- Always validated and clamped to range on both the PHP and JS sides; never trusted.

### 2.3 Precision strategy (avoid float artifacts)

Because the seed has at most 6 decimal places (`SEED_DECIMALS = 6`), every value is
exactly `classic_Fib(n) × k / 1,000,000`, where `k` is an integer 0–2,000,000
(`k = round(s × 1,000,000)`, i.e. `s × SEED_SCALE`).

To keep results exact and free of floating-point noise:

- Compute **classic Fibonacci as integers** (PHP native `int`; JS `BigInt`).
- Multiply the integer Fibonacci by `k` to get the value's numerator (in units of
  `1/SEED_SCALE`), then round that numerator to the row's decimal precision (§2.4)
  using **integer maths only** — drop the surplus low-order digits, rounding half up.
- Formatting then operates on that exact integer/fraction split.

**Practical range note:** PHP 64-bit `int` holds classic Fibonacci up to ≈ F(92); JS
`BigInt` is unbounded. The default `count = 60` (F(60) ≈ 1.5 × 10¹²) is comfortably
within range. If very large counts are ever required, the integer/BigInt approach above
already covers it on the JS side; document any PHP-side limit if `count` is pushed past
~92.

### 2.4 Number formatting (display) — "smart formatting"

- Thousands separators on the integer part (locale-aware comma grouping).
- Show decimals **only when the fractional part is non-zero**; trim trailing zeros
  (`4181.50` → `4,181.5`, `2584.00` → `2,584`).
- Whole numbers show no decimal point.
- **Per-region precision.** Rows in the first quarter of the sequence
  (`index ≤ count / PRECISE_ROWS_FRACTION`, i.e. the top of the right-hand table —
  `1 … 15` at `N = 60`) display up to **`VALUE_DECIMALS_MAX = 6`** decimal places so a
  finely-tuned decimal seed stays visible while the values are still small. Every
  other row (and both call-outs) caps at **`VALUE_DECIMALS_DEFAULT = 2`**, rounded
  half up. Trailing zeros are still trimmed, so the rule only adds digits when the
  seed actually carries that precision.

Examples (seed `0.5`): `0.5, 0.5, 1, 1.5, 2.5, 4, … 4,181.5, … 1,548,008,755,920`.
Example (seed `1.234567`, index 5 — a 6-dp row): `5 × 1.234567 = 6.172835`.

---

## 3. Per-ordinal derived values (table columns)

Each row of the number columns shows **four** fields:

| Field | Definition |
|-------|------------|
| **Index** | 1-based ordinal (1 … N) |
| **Value** | `F(index)`, smart-formatted (§2.4) |
| **Standard arc** | Cumulative angle = `index × (360 / N)` degrees |
| **Ancient arc** | Cumulative angle = `index × (432 / N)` degrees |

### 3.1 Arc lengths are cumulative (position around the wheel)

The arc value on each row is the **angular position** of that ordinal around the
circle, not a constant per-step value. At `N = 60`:

- Standard step = `360 / 60 = 6°` → row 1 = 6°, row 2 = 12°, … row 60 = 360°.
- Ancient step = `432 / 60 = 7.2°` → row 1 = 7.2°, row 2 = 14.4°, … row 60 = 432°.

The two number systems differ only in their **full-circle measure**: `360°` (standard)
vs `432°` (Ancient). The Fibonacci values themselves are identical in both. The full-circle
degrees are named constants (`360` / `432`); the per-step and per-row angles are derived
from `count`, so there are **no magic numbers**.

Arc angles are displayed rounded to a small fixed precision (e.g. 1–2 dp) and trimmed.

---

## 4. Layout & display

The numbers are **never** shown as a single flat list of N rows. They are always split
into **two tables plus two single-value call-outs**, arranged around the wheel as a clock
face. This reflects the wheel's geometry: index `N` sits at the top (12 o'clock), the
sequence runs **clockwise** down the right side, index `N/2` sits at the bottom
(6 o'clock), and the remainder runs clockwise up the left side back to the top.

### 4.1 Desktop layout (clock / cross)

Five regions around the central wheel (values shown for the default `N = 60`):

| Region | Content | Ordering |
|--------|---------|----------|
| **North** (above wheel) | Call-out box: index **N** (60) | single value |
| **Right** (right of wheel) | Table: indices **1 … N/2−1** (1–29) | index 1 at **top**, increasing **downward** |
| **South** (below wheel) | Call-out box: index **N/2** (30) | single value |
| **Left** (left of wheel) | Table: indices **N/2+1 … N−1** (31–59) | smallest at **bottom**, largest at **top** (ascending **upward**) |
| **Centre** | Wheel graphic (static image) | — |

Read in wheel-clockwise order, the values flow continuously:
**North (60) → right table top→bottom (1…29) → South (30) → left table bottom→top
(31…59) → back to North (60)**. The left table's bottom-to-top ordering is deliberate so
that following it upward matches the clockwise travel up the left side of the wheel.

Each table row still carries all four fields from §3 (index, value, standard arc°,
Ancient arc°). The call-outs likewise show their index, value and both arc angles, just
styled as a prominent single box rather than a table row.

**Even `count` assumed.** The clock layout is defined for even `N` (the natural case,
incl. the default 60). For odd `N`, fall back gracefully: South = `floor(N/2)` (or
`ceil`), right table = `1 … South−1`, left table = `South+1 … N−1`, with the same
ordering rules. Recommend even counts in usage docs.

### 4.2 Responsive behaviour

- **Desktop (≥ breakpoint):** the full clock/cross layout above.
- **Mobile (< breakpoint):** same clock/cross arrangement, scaled down — see §4.4.
- Breakpoint is a single named value (default `768px`), defined once (CSS custom
  property / constant), not scattered.

### 4.5 Compass colouring

Rows and call-outs are colour-coded by their **standard** angle (the wheel's
physical position), independent of the seed:

- **Green = cardinal points** (N/E/S/W, i.e. 90° multiples). North and South are the
  call-outs and are always green by construction. East/West are table rows and are
  green **only when an exact ordinal lands on 90° / 270°** — i.e. when `count` is
  divisible by 4. Otherwise no left/right row is greened.
- **Red = quadrant-third divisions.** Each 90° quadrant is split into thirds (every
  30°): rows at 30/60/120/150/210/240/300/330 are red, when an exact ordinal lands
  there.

Implementation is CSS-driven: the `Calculator` tags each qualifying row with its angle
(`azimuth`), the template emits `class="fhc-azimuth-{deg}"`, and the stylesheet maps
those classes to green/red (via the `--fhc-compass-green` / `--fhc-compass-red` custom
properties). Rows that don't fall on a division get no class and no colour. The number
of cardinals and quadrant subdivisions are named constants (`COMPASS_POINT_COUNT`,
`QUADRANT_SUBDIVISIONS`), so there are no magic numbers.

### 4.4 Mobile strategy

The clock/cross layout is **preserved** on mobile rather than re-flowed — the spatial
arrangement is the point of the visualisation.

- Scale the **entire widget** down proportionally to fit the available width (the wheel
  becomes small; the side tables shrink with it). Driven by CSS (container/media query
  and/or a scaling root font-size or `transform: scale`), no layout change.
- When the rendered width is below a named threshold, show an **advisory note** (e.g.
  "This works best in landscape or on a larger screen / laptop."). Prefer a pure-CSS
  reveal at small widths; JS measuring container width is acceptable if needed for
  accuracy with multiple instances.
- The note text is translatable; the threshold is a single named value.
- Accept that the tables are small at phone widths — this is the client's chosen
  trade-off in favour of keeping the spatial clock metaphor intact.

### 4.3 The wheel graphic

- A **static** image (PNG or SVG) supplied by the client, drawn for a 60-point circle,
  points starting at top/centre and progressing clockwise.
- The wheel **does not change** in response to the seed — only the number columns react.
- Bundled in `assets/` as the default. An optional shortcode `image` attribute can point
  to an alternative wheel graphic (e.g. a matching wheel for a non-default `count`).
- Until the client supplies the asset, use a placeholder so the layout can be built and
  tested.

---

## 5. Shortcode

**Tag:** `[fibonacci_harmony]`

| Attribute | Default | Notes |
|-----------|---------|-------|
| `seed` | `1.0` | Decimal 0.0–2.0; clamped. Sets the initial value; users can change it live. |
| `count` | `60` | Number of ordinals (named constant default). Drives rows, columns, and arc steps. |
| `image` | *(bundled wheel)* | Optional URL/path to an alternative wheel graphic. |

- Multiple instances per page must coexist — JS is container-scoped, uses class
  selectors (no IDs), and reads per-instance config from `data-` attributes on the
  container.
- `count ≠ 60`: the number columns/rows and arc maths fully reflect `count`; the bundled
  wheel image is for 60 points, so for other counts supply a matching `image`.

---

## 6. Front-end interaction model

- **First paint / no-JS:** PHP `Calculator` renders the full table for the initial seed
  so the shortcode is useful without JavaScript.
- **Live update:** JS mirrors the PHP algorithm exactly; on seed input (slider or number
  box) it recomputes and re-renders the number columns. The slider and number box stay
  synced.
- No AJAX is needed for the core interaction (pure client-side maths).

### 6.1 Seed control

- A range **slider + number box**, kept in sync.
- `min = 0.0`, `max = 2.0`, `step = 0.01`, initial = `seed` attribute (default `1.0`).

---

## 7. Non-functional / build constraints

- **No Composer, no npm, no bundler.** Plain PHP/CSS/JS.
- **Standalone & self-hosted.** No external CDNs. Vendor any JS library into `assets/`.
  (Current expectation: none needed — plain JS + SVG/DOM is sufficient.)
- PHP 8.0+, no `declare(strict_types=1)`. WordPress 6.0+.
- WordPress Coding Standards via `phpcs` (prefixes `fhc`, `fibonacci_harmony`,
  `Fibonacci_Harmony_Calculator`).
- Conditionally enqueue assets — only on pages where the shortcode is present.
- Escape all output; sanitize/validate all input.
- `Calculator` class is pure (no WP functions) so the maths is unit-testable and mirrors
  the JS.

---

## 8. Open items (non-blocking)

- Client to supply the final wheel PNG/SVG (60 points). Placeholder used until then.
- Confirm exact decimal precision for displayed arc angles (default 1–2 dp, trimmed).
- Confirm any theme-specific styling/colour requirements (default: neutral, theme-
  inheriting styles).
