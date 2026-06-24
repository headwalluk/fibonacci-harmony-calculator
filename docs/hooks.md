# Hooks & filters

For developers extending or theming the plugin. All hook names are prefixed `fhc_`.

## Filters

### `fhc_default_atts`

Filter the shortcode's default attribute values before user-supplied attributes are
merged in. Useful for changing the site-wide defaults.

```php
add_filter(
	'fhc_default_atts',
	function ( array $defaults ) {
		$defaults['seed']  = 1.618; // open on the golden ratio
		$defaults['count'] = 60;
		return $defaults;
	}
);
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$defaults` | `array` | Default attributes, keyed by attribute name (`seed`, `count`, `image`). |

### `fhc_wheel_image_url`

Filter the wheel graphic URL before it is escaped and output. Return the URL you want to
use (it is passed through `esc_url()` afterwards).

```php
add_filter(
	'fhc_wheel_image_url',
	function ( string $url, array $atts ) {
		if ( 48 === (int) $atts['count'] ) {
			return 'https://example.com/wheels/wheel-48.svg';
		}
		return $url;
	},
	10,
	2
);
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$url` | `string` | The wheel image URL (the `image` attribute, or the bundled default). |
| `$atts` | `array` | The resolved shortcode attributes. |

### `fhc_template`

Filter the absolute path to the template file used to render the shortcode. The plugin
already checks for a theme override at `yourtheme/fibonacci_harmony/calculator.php` before
falling back to the bundled template; use this filter for paths outside that convention.

```php
add_filter(
	'fhc_template',
	function ( string $template ) {
		return MY_PLUGIN_DIR . 'templates/fibonacci-calculator.php';
	}
);
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$template` | `string` | Absolute path to the template file. |

### `fhc_output`

Filter the final rendered HTML returned by the shortcode — wrap it, inject markup, or
post-process it.

```php
add_filter(
	'fhc_output',
	function ( string $html, $calculator, array $atts ) {
		return '<div class="my-wrapper">' . $html . '</div>';
	},
	10,
	3
);
```

| Parameter | Type | Description |
|-----------|------|-------------|
| `$html` | `string` | The rendered HTML markup. |
| `$calculator` | `Fibonacci_Harmony_Calculator\Calculator` | The calculator instance for this render. |
| `$atts` | `array` | The resolved shortcode attributes. |

## Theme template override

Without writing any PHP, a theme can override the markup by copying the bundled template
to:

```
wp-content/themes/your-theme/fibonacci_harmony/calculator.php
```

The template receives `$calculator` (a `Calculator` instance), `$instance_id` (a unique
container id), and `$image_url` (the escaped wheel URL).

## Customising the styling

The front-end CSS exposes custom properties on `.fhc-calculator` that you can override
from your theme (no need to dequeue the plugin stylesheet):

| Property | Default | Purpose |
|----------|---------|---------|
| `--fhc-compass-green` | `#1a7f37` | Colour for cardinal points (N/E/S/W). |
| `--fhc-compass-red` | `#d63638` | Colour for quadrant-third rows. |
| `--fhc-max-wheel` | `420px` | Maximum width of the wheel graphic. |

```css
.fhc-calculator {
	--fhc-compass-green: #0b6b2e;
	--fhc-compass-red: #a30e0e;
	--fhc-max-wheel: 360px;
}
```

The compass colouring is applied by `fhc-azimuth-{deg}` classes on the relevant rows
(e.g. `fhc-azimuth-90` for East). Target those classes directly for finer control.

## Programmatic access to the maths

The `Fibonacci_Harmony_Calculator\Calculator` class is free of WordPress dependencies and
can be used directly:

```php
use Fibonacci_Harmony_Calculator\Calculator;

$calc     = new Calculator( 0.5, 60 );
$ordinals = $calc->get_ordinals();
// $ordinals[7] => array( 'index' => 7, 'value' => '6.5', 'arc_standard' => '42', 'arc_asian' => '50.4' )

$parts = Calculator::partition_indices( 60 );
// array( 'north' => 60, 'south' => 30, 'right' => [1..29], 'left' => [59..31] )
```

> **Note:** changing the displayed numbers via these PHP hooks affects the server-rendered
> first paint. The live recalculation that runs when a visitor changes the seed happens in
> the browser (JavaScript), which mirrors the `Calculator` maths but does not run PHP
> filters. Keep that in mind if you alter values rather than presentation.
