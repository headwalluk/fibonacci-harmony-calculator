# Project Tracker

**Version:** 0.2.0
**Last Updated:** 2026-06-24
**Current Phase:** Phase 3 — polish / wash-up adjustments
**Overall Progress:** ~85% (interactive front-end + colouring shipped; final tweaks pending)

---

## Overview

Fibonacci Harmony Calculator is a standalone WordPress plugin providing a single
shortcode, `[fibonacci_harmony]`, that renders an interactive calculator + graphic
exploring the Fibonacci 60 Repeating Pattern.

A user-set **seed** (decimal 0.0–2.0, default 1.0) drives a Fibonacci-style sequence of
N numbers (default 60), where `F(1)=F(2)=seed` and `F(n)=F(n-1)+F(n-2)` — i.e.
`seed × classic Fibonacci`. The sequence is shown as two columns of rows (index, value,
standard arc °, Asian arc °) flanking a static circular wheel graphic. The calculation
runs live, client-side.

Full behavioural spec: [`01-requirements.md`](01-requirements.md).

---

## Key Decisions (confirmed 2026-06-24)

- Sequence = `seed × classic Fibonacci` (`F1=F2=seed`).
- Display the **full** value (no mod-10 / digital-root reduction).
- Arc lengths are **cumulative angles** (`index × 360/N` and `index × 432/N`).
- Numbers are **never a flat list** — always a **clock/cross layout**: North call-out =
  index N, right table = 1…N/2−1 (1 top), South call-out = N/2, left table = N/2+1…N−1
  (smallest at bottom, ascending up). Reads clockwise around the wheel.
- **Mobile:** keep the clock layout, scale it down, show an advisory note (best on a
  larger screen). Layout is not re-flowed.
- Wheel is a **static** client-supplied image; only the number values react to input.
- Seed control = **slider + synced number box** (0.0–2.0, step 0.01, default 1.0).
- **Smart** number formatting (thousands separators; trimmed decimals).
- Integer/`BigInt` maths scaled by seed-as-hundredths to avoid float artifacts.
- Shortcode `[fibonacci_harmony]`, attrs `seed` / `count` / `image`.
- Client-side recompute (no AJAX); PHP renders the initial state for no-JS / first paint.

---

## Planned File Structure

```
fibonacci-harmony-calculator/
├── fibonacci-harmony-calculator.php   # main plugin file (bootstrap)
├── constants.php                      # namespaced constants (defaults, attr keys, degrees)
├── CLAUDE.md
├── README.md
├── readme.txt                         # WordPress-style readme
├── CHANGELOG.md
├── LICENSE                            # GPLv2 or later
├── phpcs.xml
├── .gitignore
├── includes/
│   ├── class-plugin.php               # orchestrator: hooks, shortcode + asset registration
│   ├── class-shortcode.php            # attribute parsing/validation, render, conditional enqueue
│   └── class-calculator.php           # pure maths (sequence + derived values), mirrors JS
├── public-templates/
│   └── calculator.php                 # markup (theme-overridable)
├── assets/
│   ├── fhc-public.css
│   ├── fhc-public.js                  # container-scoped live recompute (mirrors Calculator)
│   └── wheel.svg                      # placeholder until client supplies the real graphic
└── dev-notes/
    ├── 00-project-tracker.md
    ├── 01-requirements.md
    ├── patterns/
    └── workflows/
```

---

## Milestones

### Phase 0 — Requirements ✅
- [x] Read template (bullfix-erp) conventions
- [x] Write `CLAUDE.md`
- [x] Resolve ambiguities with client (seed maths, displayed value, wheel, arcs, formatting, naming)
- [x] Write `01-requirements.md`

### Phase 1 — Scaffold
- [x] Main plugin file, `constants.php`, `phpcs.xml`, `.gitignore`
- [x] `Plugin` orchestrator with shortcode + conditional asset enqueue wiring
- [x] `Calculator` (PHP) — sequence + derived values, precision strategy
- [x] `public-templates/calculator.php` — clock-layout markup, escaped output
- [x] Client wheel graphic copied to `assets/wheel.png` (placeholder until final art)
- [x] `phpcs` clean (0 violations)
- [x] Code-style review (approved by Paul)
- [x] Extension filters added (`fhc_default_atts`, `fhc_wheel_image_url`, `fhc_template`, `fhc_output`)
- [x] Strings aligned with wp-translate conventions (`_x()` contexts)
- [x] LICENSE (GPLv2), README.md, docs/shortcode.md, docs/hooks.md
- [x] `git init` + initial commit + push to remote
- [ ] readme.txt, CHANGELOG.md (deferred)

### Phase 2 — Core build ✅
- [x] `fhc-public.css` — desktop clock/cross grid (CSS grid areas), seed controls,
      container-query scale-down + advisory note on narrow widget widths
- [x] `fhc-public.js` — Calculator mirror (BigInt), slider/number sync, live value updates
- [x] `data-fhc-seed-scale` exposed so JS reads SEED_SCALE (no hardcoded /100)
- [x] PHP/JS output parity verified (node + php harness, identical results)
- [x] Assets confirmed served on the live demo CDN path
- [x] Compass colouring — `fhc-azimuth-{deg}` row classes, CSS maps cardinals→green
      (N/E/S/W) and quadrant-thirds→red; only colours exact-ordinal angles
      (requirements §4.5)

**Demo note:** the showcase post uses `count=72`, so the bundled 60-point wheel
graphic does not match the point count (documented caveat). Either set the demo to
`count=60` or supply a 72-point wheel via the `image` attribute.

### Phase 3 — Polish & verify
- [ ] Visual check on the live demo (desktop clock layout + mobile scale/note)
- [ ] Multiple-instances-per-page check
- [ ] Edge cases: seed 0.0 / 2.0, odd `count`, large `count`
- [ ] Swap in client's real wheel graphic when supplied

### Phase 3 — Polish & verify
- [ ] Multiple-instances-per-page check
- [ ] No-JS / first-paint check
- [ ] Edge cases: seed 0.0 / 2.0, odd `count`, `count` ≠ 60, large `count`
- [ ] `phpcs` clean
- [ ] Swap in client's real wheel graphic when supplied

---

## Technical Debt

_None yet._

---

## Notes for Development

- No magic numbers: `count` default, seed range, and full-circle degrees (360 / 432) are
  named constants; per-row/per-step angles derive from `count`.
- Keep PHP `Calculator` and JS `fhc-public.js` algorithms identical.
- Wheel image is for 60 points; for other `count` values supply a matching `image`.
