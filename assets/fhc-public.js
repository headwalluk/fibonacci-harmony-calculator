/**
 * Fibonacci Harmony Calculator - front-end behaviour.
 *
 * Mirrors the PHP Calculator exactly: the sequence is seed x classic Fibonacci,
 * computed with BigInt so large values stay precise, and the seed resolves to
 * the nearest 1/scale so every value is an exact integer divided by `scale`.
 * Only the value cells change with the seed (the arc angles depend on index and
 * count alone), so this script updates the [data-fhc-index] cells and nothing
 * else.
 *
 * Container-scoped and class-selector based so multiple shortcodes can coexist.
 */
( function () {
	'use strict';

	/**
	 * Group a non-negative integer's digit string with thousands separators.
	 * Matches PHP number_format()'s default comma grouping.
	 *
	 * @param {string} digits Digit string (no sign, no decimal point).
	 * @return {string}
	 */
	function groupThousands( digits ) {
		return digits.replace( /\B(?=(\d{3})+(?!\d))/g, ',' );
	}

	/**
	 * Manages one [fibonacci_harmony] instance.
	 */
	class FhcCalculator {
		constructor( container ) {
			this.container = container;

			this.count = parseInt( container.dataset.fhcCount, 10 ) || 0;
			this.scale = parseInt( container.dataset.fhcSeedScale, 10 ) || 100;
			this.min = parseFloat( container.dataset.fhcSeedMin );
			this.max = parseFloat( container.dataset.fhcSeedMax );

			this.range = container.querySelector( '.fhc-seed--range' );
			this.number = container.querySelector( '.fhc-seed--number' );
			this.valueCells = container.querySelectorAll( '[data-fhc-index]' );

			// Cache the classic Fibonacci sequence (1-indexed) as BigInt.
			this.classic = FhcCalculator.classicFibonacci( this.count );

			this.seed = this.clamp( parseFloat( container.dataset.fhcSeed ) );

			this.bind();
		}

		/**
		 * Generate the classic Fibonacci sequence (1, 1, 2, 3, ...) as BigInt.
		 *
		 * @param {number} count Number of terms.
		 * @return {Object} Map of 1-based index to BigInt value.
		 */
		static classicFibonacci( count ) {
			const seq = {};
			for ( let i = 1; i <= count; i++ ) {
				seq[ i ] = i <= 2 ? 1n : seq[ i - 1 ] + seq[ i - 2 ];
			}
			return seq;
		}

		clamp( value ) {
			if ( Number.isNaN( value ) ) {
				return this.seed;
			}
			return Math.min( this.max, Math.max( this.min, value ) );
		}

		bind() {
			if ( this.range ) {
				this.range.addEventListener( 'input', () => {
					this.setSeed( this.range.value, 'range' );
				} );
			}
			if ( this.number ) {
				this.number.addEventListener( 'input', () => {
					this.setSeed( this.number.value, 'number' );
				} );
				// Normalise the field's display when the user commits a value.
				this.number.addEventListener( 'change', () => {
					this.number.value = String( this.seed );
				} );
			}
		}

		/**
		 * Update the seed, sync the controls, and re-render the values.
		 *
		 * @param {string} raw    Raw input value.
		 * @param {string} source Which control fired ('range' | 'number').
		 * @return {void}
		 */
		setSeed( raw, source ) {
			this.seed = this.clamp( parseFloat( raw ) );

			// Mirror the seed onto the *other* control so we never clobber the
			// field the user is currently typing in.
			if ( 'range' === source && this.number ) {
				this.number.value = String( this.seed );
			} else if ( 'number' === source && this.range ) {
				this.range.value = String( this.seed );
			}

			this.render();
		}

		/**
		 * Format a value: scaledSeed x classic Fibonacci, divided by scale.
		 * Identical output to Calculator::format_value() in PHP.
		 *
		 * @param {BigInt} classicValue Classic Fibonacci value.
		 * @return {string}
		 */
		formatValue( classicValue ) {
			const scaledSeed = BigInt( Math.round( this.seed * this.scale ) );
			const scale = BigInt( this.scale );
			const numerator = classicValue * scaledSeed;

			const integerPart = numerator / scale;
			const fraction = numerator % scale;

			let out = groupThousands( integerPart.toString() );

			if ( fraction > 0n ) {
				// scale is 100, so the fraction is at most two digits.
				const fractionString = fraction
					.toString()
					.padStart( String( this.scale ).length - 1, '0' )
					.replace( /0+$/, '' );
				out += '.' + fractionString;
			}

			return out;
		}

		render() {
			this.valueCells.forEach( ( cell ) => {
				const index = parseInt( cell.dataset.fhcIndex, 10 );
				const classicValue = this.classic[ index ];
				if ( undefined !== classicValue ) {
					cell.textContent = this.formatValue( classicValue );
				}
			} );
		}
	}

	function init() {
		document
			.querySelectorAll( '.fhc-calculator' )
			.forEach( ( container ) => new FhcCalculator( container ) );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', init );
	} else {
		init();
	}
} )();
