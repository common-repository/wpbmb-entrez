<?php

/**
 * Class for formatting the query results
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WBE_Formatter
 */
class WBE_Formatter {

	protected $atts;
	protected $results;
	protected $styles;
	protected $resnum;
	protected $template;

	protected $output;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 */
	function __construct( $results, $atts ) {

		$this->atts    = $atts;
		$this->results = $results;

		$template       = wbe_get_options_table_options( 'options' );
		$this->template = $template['template'];
	}

	/**
	 * process_results
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	public function process_results() {

		$count  = count( $this->results );
		$output = '';
		for ( $i = 0; $i < $count; $i ++ ) {

			$result       = $this->results[ $i ];
			$this->resnum = $i;

			$result = $this->process_entry( $result );

			$output .= $this->generate_output( $result );

		} // end for $count

		return $output;
	}

	/**
	 * process_entry
	 *
	 * @param $result
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function process_entry( $result ) {

		foreach ( $result as $key => $value ) {

			$method = 'process_' . $key;

			if ( method_exists( $this, $method ) ) {

				$processed = $this->$method( $value );
				foreach ( $processed as $new_key => $new_value ) {
					$result[ $new_key ] = $new_value;
				}
			}

		}

		return $result;
	}

	/**
	 * process_authors
	 *
	 * @param $authors
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function process_authors( $authors ) {

		$author_string = '';
		$author_array  = apply_filters( 'wbe_authors_array_list', $authors );
		if ( ! is_array( $author_array ) || empty( $authors ) ) {
			return array( 'author_string' => '' );
		}

		$author_limit = $this->atts['author_limit'];

		if ( ! empty( $author_limit ) && $author_limit != 0 ) {
			$author_array = array_slice( $author_array, 0, $author_limit );
		}

		$author_string = apply_filters( 'wbe_authors_array_to_string', $author_array );
		$author_string = apply_filters( 'wbe_highlight_text', $author_string, $this->atts['highlights'], $this->atts['highlights_type'] );
		if ( ! is_string( $author_string ) ) {
			$author_string = '';
		}

		return array( 'author_string' => $author_string );
	}

	/**
	 * process_title
	 *
	 * @param $title
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function process_title( $title ) {

		$processed = apply_filters( 'wbe_article_title_text', $title );

		return array( 'title' => $processed );

	}

	/**
	 * process_abstract
	 *
	 * @param $abstract
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function process_abstract( $abstract ) {

		$abstract = apply_filters( 'wbe_highlight_text', $abstract, $this->atts['highlights'], $this->atts['highlights_type'] );

		return array( 'abstract' => $abstract );

	}

	/**
	 * generate_output
	 *
	 * @param $result
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	protected function generate_output( $result ) {

		$template = $this->atts['template'];

		ob_start();

		$template_file = wbe_template_file( $template );

		if ( ! empty( $template_file ) ) {
			include( $template_file );
		}

		return ob_get_clean();
	}
}




