<?php
/**
 * Plugin-scope constants.
 *
 * All defaults, attribute keys, and magic values live here so the rest of the
 * code never hardcodes them. See dev-notes/01-requirements.md for the rationale.
 *
 * @package Fibonacci_Harmony_Calculator
 */

namespace Fibonacci_Harmony_Calculator;

defined( 'ABSPATH' ) || die();

// ============================================================================
// Text domain
// ============================================================================

const TEXT_DOMAIN = 'fibonacci-harmony-calculator';

// ============================================================================
// Shortcode
// ============================================================================

const SHORTCODE_TAG = 'fibonacci_harmony';

// Shortcode attribute keys - prefix with ATT_.
const ATT_SEED  = 'seed';
const ATT_COUNT = 'count';
const ATT_IMAGE = 'image';

// ============================================================================
// Defaults - prefix with DEF_
// ============================================================================

const DEF_SEED  = 1.0;
const DEF_COUNT = 60;

// Ordinal count bounds. The shortcode clamps the `count` attribute to this range
// so a stray value can't render a runaway DOM (each ordinal is a table row). The
// canonical case is the 60-point wheel; the maximum is generous but bounded.
const COUNT_MIN = 1;
const COUNT_MAX = 360;

// Default wheel graphic, relative to the assets/ directory. The client supplies
// the real artwork; this is swapped out by the ATT_IMAGE attribute when set.
const DEF_WHEEL_IMAGE = 'wheel.png';

// ============================================================================
// Seed bounds & precision
// ============================================================================

const SEED_MIN = 0.0;
const SEED_MAX = 2.0;

// The range slider drags in coarse hundredths; the number field accepts a fine,
// fully-precise decimal (down to 1/SEED_SCALE) so a client can type an exact seed.
const SEED_STEP       = 0.01;
const SEED_INPUT_STEP = 0.000001;

// The seed resolves to the nearest 1/SEED_SCALE, so every value is an exact
// (classic Fibonacci x scaled seed) integer divided by SEED_SCALE -- this keeps
// the maths free of floating-point noise. SEED_SCALE must stay 10^SEED_DECIMALS
// so a seed typed to SEED_DECIMALS places is represented exactly. See
// Calculator::format_value().
const SEED_DECIMALS = 6;
const SEED_SCALE    = 1000000;

// ============================================================================
// Geometry
// ============================================================================

// Full-circle measures for each number system. Per-ordinal and per-row angles
// are derived from these and the count - they are never hardcoded.
const CIRCLE_DEGREES_STANDARD = 360;
const CIRCLE_DEGREES_ANCIENT  = 432;

// Decimal places used when displaying arc angles (trailing zeros trimmed).
const ARC_DECIMALS = 2;

// Value display precision. Rows in the first 1/PRECISE_ROWS_FRACTION of the
// sequence (the top quarter, i.e. the top of the right-hand table) show up to
// VALUE_DECIMALS_MAX decimal places so a finely-tuned decimal seed is visible;
// every other row caps at VALUE_DECIMALS_DEFAULT. Trailing zeros are trimmed in
// both cases, so whole results still render without a decimal point.
const VALUE_DECIMALS_MAX     = 6;
const VALUE_DECIMALS_DEFAULT = 2;
const PRECISE_ROWS_FRACTION  = 4;

// Compass overlay. The wheel is divided into COMPASS_POINT_COUNT cardinal points
// (N/E/S/W) and each quadrant is split into QUADRANT_SUBDIVISIONS thirds. A row
// whose standard angle lands exactly on one of these divisions gets an
// `fhc-azimuth-{deg}` class so the front-end can colour it (cardinals green,
// subdivisions red). Rows that don't land on a division get no class.
const COMPASS_POINT_COUNT   = 4;
const QUADRANT_SUBDIVISIONS = 3;

// ============================================================================
// Layout
// ============================================================================

// Width (px) below which the widget is treated as "small" - the clock layout is
// scaled down and an advisory note is shown. Mirrored in the front-end CSS.
const MOBILE_BREAKPOINT_PX = 768;

// ============================================================================
// Assets
// ============================================================================

const ASSET_HANDLE_CSS = 'fhc-public';
const ASSET_HANDLE_JS  = 'fhc-public';
