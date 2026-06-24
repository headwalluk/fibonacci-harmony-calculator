# Using the `[fibonacci_harmony]` shortcode

This guide is for site owners and editors. It covers how to place the calculator on a
page, the attributes you can set, and what the numbers mean.

## Quick start

Add the shortcode anywhere shortcodes are supported — the post/page editor, a shortcode
block, a widget, or a template via `do_shortcode()`:

```
[fibonacci_harmony]
```

That renders the calculator with the default seed (`1.0`) and 60 numbers. Visitors can
then drag the slider or type a seed to recalculate.

## Attributes

| Attribute | Default | Description |
|-----------|---------|-------------|
| `seed` | `1.0` | The starting value, a decimal from `0.0` to `2.0`. Sets where the calculator opens; visitors can still change it live. Values outside the range are clamped; the seed resolves to the nearest `0.01`. |
| `count` | `60` | How many numbers to generate. Drives the rows, the layout split, and the arc-angle steps. |
| `image` | *(bundled wheel)* | URL of an alternative wheel graphic. Use this if you need a wheel drawn for a different `count` (see note below). |

### Examples

```
[fibonacci_harmony seed="1.0"]
[fibonacci_harmony seed="0.5" count="60"]
[fibonacci_harmony seed="1.618" count="48" image="https://example.com/wheel-48.svg"]
```

You can place more than one calculator on the same page — each works independently.

## What the numbers mean

For a seed `s`, the sequence is `s × the classic Fibonacci sequence`:

- `F(1) = s`, `F(2) = s`, and every following number is the sum of the previous two.
- `seed = 1.0` → `1, 1, 2, 3, 5, 8, 13, …` (the classic Fibonacci sequence).
- `seed = 2.0` → `2, 2, 4, 6, 10, 16, …`
- `seed = 0.5` → `0.5, 0.5, 1, 1.5, 2.5, 4, …`

Each entry also shows two **arc angles** — its position around the circle:

- **Std** — the standard system, where a full circle is `360°` (so 60 points step by `6°`).
- **Asian** — a system where a full circle is `432°` (so 60 points step by `7.2°`).

The Fibonacci values are identical in both systems; only the circle's total degrees differ.

## How it's laid out

The numbers are arranged around the wheel like a clock, never as one long list:

- **Top (North):** the last number (e.g. #60).
- **Right of the wheel:** numbers `1` to `29`, top to bottom.
- **Bottom (South):** the middle number (e.g. #30).
- **Left of the wheel:** numbers `31` to `59`, reading bottom to top.

Read clockwise, the values flow continuously around the circle.

On phones and narrow screens the same layout is shown but scaled down, with a note
suggesting landscape or a larger screen for the best view.

## Colour coding

Entries are coloured by their position around the wheel:

- **Green** marks the compass points — North and South (the call-outs), plus East and
  West when an exact number lands on them (this happens when `count` is a multiple of 4,
  e.g. the default 60).
- **Red** marks the quadrant thirds (every 30° around the circle), where an exact number
  lands on the division.

If your `count` doesn't place a number exactly on one of these angles, that entry simply
isn't coloured. Colours can be restyled — see [hooks.md](hooks.md#customising-the-styling).

## A note on `count` and the wheel image

The bundled wheel graphic is drawn for **60 points**. If you change `count` to something
other than 60, the numbers and angles update correctly, but the bundled wheel won't match
— supply a matching graphic with the `image` attribute for other counts. Even counts work
best (the layout splits cleanly into top/right/bottom/left).
