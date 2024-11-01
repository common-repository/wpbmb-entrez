<?php

/**
 * Custom filters
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WBE_Filters
 */
class WBE_Filters {

	/**
	 * WBE_Filters constructor.
	 */
	public function __construct() {

		global $wbe_filters;

		global $wbe_highlights_types;
		$highlights_types = implode( ', ', $wbe_highlights_types );

		$wbe_filters = array(
			array(
				'filter'    => 'wbe_highlight_text',
				'callback'  => 'wbe_highlight_text_string',
				'arguments' => 3,
				'desc'      => __( 'Text string highlighting (e.g. authors list or abstract text).', 'wbe' ),
				'inputs'    => array(
					'string (text to apply highlights to)',
					'string (words to highlight)',
					"string (highlights type default: bold, options = {$highlights_types})",
				),
				'returns'   => 'string'
			),
			array(
				'filter'    => 'wbe_article_title_text',
				'callback'  => 'wbe_article_title_text_string',
				'arguments' => 1,
				'desc'      => __( 'Processing of the title. Default: removes the trailing period.', 'wbe' ),
				'inputs'    => 'string',
				'returns'   => 'string'
			),
			array(
				'filter'    => 'wbe_authors_array_list',
				'callback'  => 'wbe_process_authors_array_list',
				'arguments' => 1,
				'desc'      => __( 'Processing the returned authors array for a search result entry (e.g. swapping author order or truncating the list).', 'wbe' ),
				'inputs'    => __( 'array (one author string per element)', 'wbe' ),
				'returns'   => __( 'array (one author string per element)', 'wbe' )
			),
			array(
				'filter'    => 'wbe_authors_array_to_string',
				'callback'  => 'wbe_process_authors_array_to_string',
				'arguments' => 1,
				'desc'      => __( 'Override for producing the string of authors displayed in a publication entry.', 'wbe' ),
				'inputs'    => __( 'array (one author string per element)', 'wbe' ),
				'returns'   => 'string'
			),
			array(
				'filter'    => 'wbe_entrez_results_array',
				'callback'  => 'wbe_process_entrez_results_array',
				'arguments' => 3,
				'desc'      => __( 'Override for performing processing on the results of a search (e.g. change sorting).', 'wbe' ),
				'inputs'    => __( 'array (one citation record per element), unique_id, sort order', 'wbe' ),
				'returns'   => __( 'array (one citation record per element)', 'wbe' )
			),
		);

		foreach ( $wbe_filters as $filter ) {

			add_filter( $filter['filter'], $filter['callback'], 10, $filter['arguments'] );

		}
	}
}

new WBE_Filters();

// Custom filters
/**
 * wbe_highlight_text_string
 *
 * @param $string
 *
 * @return mixed|string
 *
 * @since 1.0.0
 */
function wbe_highlight_text_string( $string, $highlights = '', $highlights_type = 'bold' ) {

	if ( ! is_string( $string ) ) {
		return '';
	}

	//$display = wbe_get_options_table_options( 'display' );

	if ( ! empty( $highlights ) ) {

		$keywords = array_map( 'trim', explode( ',', $highlights ) );

		$type = "wbe-display-highlights-" . $highlights_type;
		$type = esc_attr( $type );

		foreach ( $keywords as $keyword ) {

			$lastPos = 0;
			$count   = 1;
			while ( ( $lastPos = stripos( $string, $keyword, $lastPos ) ) !== false ) {
				$beg   = $lastPos;
				$len   = strlen( $keyword );
				$style = '';

				if ( $lastPos !== false ) {
					$actual = substr( $string, $beg, $len );
					$style  = "<span class='" . $type . "'>" . esc_html( $actual ) . "</span>";
					$string = substr_replace( $string, $style, $beg, $len );
				}

				$lastPos = $lastPos + strlen( $style );
			}
		}
	}

	return $string;

}

/**
 * wbe_article_title_text_string
 *
 * @param $string
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_article_title_text_string( $string ) {
	$processed = rtrim( $string, '.' );

	return $processed;
}

/**
 * wbe_process_authors_array_list
 *
 * @param $array
 *
 * @return mixed
 *
 * @since 1.0.0
 */
function wbe_process_authors_array_list( $array ) {
	return $array;
}

/**
 * wbe_process_authors_array_to_string
 *
 * @param $author_array
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_process_authors_array_to_string( $author_array ) {

	$author_string = '';
	switch ( count( $author_array ) ) {
		case 1:
			$author_string = $author_array[0];
			break;
		case 2:
			$author_string = implode( ' and ', $author_array );
			break;
		default:
			$author_string = implode( ', ', array_slice( $author_array, 0, - 1 ) );
			$author_string = implode( ' and ', array( $author_string, end( $author_array ) ) );
			break;
	}

	return $author_string;
}

/**
 * wbe_compare_dates_dec
 *
 * @param $a
 * @param $b
 *
 * @return int
 *
 * @since 1.0.0
 */
function wbe_compare_dates_dec( $a, $b ) {
	$date_a = strtotime( $a['year'] . '-' . $a['month'] );
	$date_b = strtotime( $b['year'] . '-' . $b['month'] );

	return ( $date_a > $date_b ) ? - 1 : 1;
}

/**
 * wbe_compare_dates_asc
 *
 * @param $a
 * @param $b
 *
 * @return int
 *
 * @since 1.0.0
 */
function wbe_compare_dates_asc( $a, $b ) {
	$date_a = strtotime( $a['year'] . '-' . $a['month'] );
	$date_b = strtotime( $b['year'] . '-' . $b['month'] );

	return ( $date_a > $date_b ) ? 1 : - 1;
}

/**
 * wbe_process_entrez_results_array
 *
 * @param $results
 * @param $unique_id
 * @param $order_by
 *
 * @return array
 *
 * @since 1.0.0
 */
function wbe_process_entrez_results_array( $results, $unique_id, $order_by ) {

	$unique = wbe_array_unique_by_subkey( $results, $unique_id );

	$cmp_function = "wbe_compare_dates_dec";
	switch ( $order_by ) {
		case 'date-dec':
			$cmp_function = "wbe_compare_dates_dec";
			break;
		case 'date-asc':
			$cmp_function = "wbe_compare_dates_asc";
			break;
	}
	usort( $unique, $cmp_function );

	return $unique;
}
