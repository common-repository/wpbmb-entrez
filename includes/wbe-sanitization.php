<?php

/**
 * Sanitize inputted data
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 *
 */
class WBE_Sanitize_Data {

	protected static $instance = null;

	/**
	 * get_instance
	 *
	 * @return null|WBE_Sanitize_Data
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function parse_data( $data, $type ) {
		$type = str_replace( '-', '_', $type );

		if ( method_exists( $this, $type ) ) {
			return $this->$type( $data );
		} else {
			return $data;
		}
	}

	private function px( $data ) {
		if ( 'none' == $data ) {
			return '0';
		} else {
			return floatval( $data ) . 'px';
		}
	}

	private function font_size( $data ) {
		if ( strpos( $data, 'px' ) || strpos( $data, 'em' ) ) {
			$data = esc_html( $data );
		} else {
			$data = intval( $data ) . 'px';
		}
		if ( $data != '0px' && $data != '0em' ) {
			return esc_html( $data );
		}
	}

	private function font_weight( $data ) {
		if ( 'thin' == $data ) {
			return '300';
		} elseif ( 'normal' == $data ) {
			return '400';
		} elseif ( 'semibold' == $data ) {
			return '600';
		} elseif ( 'bold' == $data ) {
			return '700';
		} elseif ( 'bolder' == $data ) {
			return '900';
		} else {
			return esc_html( $data );
		}
	}

	private function html( $data ) {
		return wp_kses_post( $data );
	}

	private function int( $data ) {

		if ( is_numeric( $data ) ) {
			return intval( $data );
		}

		return 0;
	}

	private function string( $data ) {

		$data = preg_replace( '/\xc2\xa0/', ' ', $data );
		$data = preg_replace( "/ {2,}/", ' ', $data );
		$data = trim( $data, " \t\n\r\0\x0B" );
		$data = wp_strip_all_tags( $data, true );

		return $data;

	}

	private function boolean( $data ) {

		if ( is_string( $data ) ) {
			return ( $data === "true" ) ? true : false;
		} elseif ( is_bool( $data ) ) {
			return (bool) $data;
		}

		return false;
	}

	private function string_or_int( $data ) {

		if ( is_numeric( $data ) ) {
			return $this->int( $data );
		} elseif ( is_string( $data ) ) {
			return ( $data === 'all' ) ? 10000 : 0;
		}

		return 0;
	}
}

function wbe_sanitizer() {
	return WBE_Sanitize_Data::get_instance();
}

function wbe_sanitize( $data = '', $type = '' ) {
//	if ( ! empty( $data ) && ! empty( $type ) ) {
	$class = wbe_sanitizer();

	return $class->parse_data( $data, $type );
//	}
}

function wbe_sanitize_atts( $atts ) {

	$options_full = wbe_getg( 'options_full' );
	foreach ( $atts as $key => $data ) {

		$type = $options_full[ $key ]['type'];

		$cleaned      = wbe_sanitize( $data, $type );
		$atts[ $key ] = $cleaned;
	}

	return $atts;

}

// Instantiate the class and create singleton
wbe_sanitizer();