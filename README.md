# Fibonacci Harmony Calculator

![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-21759B?logo=wordpress&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4?logo=php&logoColor=white)
![License](https://img.shields.io/badge/license-GPLv2-blue)

A standalone WordPress plugin that adds a shortcode, `[fibonacci_harmony]`, rendering an
interactive calculator and graphic that explores the **Fibonacci 60 Repeating Pattern**.

Visitors set a **seed** (a decimal from `0.0` to `2.0`) that scales a Fibonacci sequence
of N numbers (default 60). The sequence is laid out as a clock face around a circular
wheel graphic — index 60 at the top, running clockwise — with each entry showing its
value and its arc angle in both the standard 360° and the "Asian" 432° systems. The
numbers recalculate live as the seed changes.

No build step, no Composer, no external CDNs — drop it in and activate.

## Usage

Add the shortcode to any post or page:

```
[fibonacci_harmony]
[fibonacci_harmony seed="0.5" count="60"]
```

Full attribute reference and examples: **[docs/shortcode.md](docs/shortcode.md)**.

## Documentation

- **[docs/shortcode.md](docs/shortcode.md)** — using the shortcode: attributes, examples,
  layout, and the maths behind the numbers (for site owners).
- **[docs/hooks.md](docs/hooks.md)** — action hooks and filters for developers extending
  or theming the plugin.

Per-version release notes: [CHANGELOG.md](CHANGELOG.md). Project history and active
milestones live in [dev-notes/00-project-tracker.md](dev-notes/00-project-tracker.md);
the full functional spec is in [dev-notes/01-requirements.md](dev-notes/01-requirements.md).

## License

Licensed under [GPLv2](LICENSE). Built by [Headwall Hosting](https://headwall-hosting.com).
