<?php
/**
 * Shortcode and class for access to Entrez (pubmed default)
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WBE_Shortcode {

	public $atts         = null;
	public $hash_id      = null;
	public $hashed_atts  = null;
	public $shortcode    = null;
	public $shortcode_id = null;
	public $cache_data   = null;
	public $results      = null;
	public $results_raw  = null;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct( $atts = null ) {

		if ( ! empty( $atts ) && is_array( $atts ) ) {

			$this->process_atts( $atts );

		}

		// Add shortcode
		add_shortcode( 'wpbmb', array( $this, 'wbe_run_query' ) );
	}

	/**
	 * wbe_run_query
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return null|string
	 *
	 * @since 1.0.0
	 */
	public function wbe_run_query( $atts, $content = null ) {

		$process_multiple = false;
		$shortcodes       = '';
		$element_count    = 0;

		//Make sure the input parameter names are all lowercase
		$atts = array_change_key_case( (array) $atts );

		if ( isset( $atts['use_tags'] ) && ! empty( $atts['use_tags'] ) ) {
			$process_multiple = true;
			$process_key      = 'use_tags';
			$values           = array_map( 'trim', explode( ',', $atts[ $process_key ] ) );
			$shortcodes       = wbe_db_get_shortcodes_by_meta( 'tag', $values );
			$element_count    = count( $values );
		} elseif ( isset( $atts['sid'] ) && ! empty( $atts['sid'] ) ) {

			$process_multiple = true;
			$process_key      = 'sid';
			$values           = array_map( 'trim', explode( ',', $atts[ $process_key ] ) );
			$shortcodes       = wbe_db_get_shortcodes( $values );
			$element_count    = count( $values );

		}

		$results_raw = array();
		$temp_result = null;

		if ( $process_multiple ) {

			if ( empty( $shortcodes ) ) {
				$message = __( 'None of the previously defined shortcode tags (or IDs) could be found. Please check that it still exists from the Settings Page in WP Admin.', 'wbe' );

				return $message;
			} elseif ( count( $shortcodes ) !== $element_count ) {
				//TODO: Make the error string a bit more helpful by printing which tag(s) are missing
				return 'One or more of the shortcode tags (or IDs) no longer exist.';
			}

			//For each shortcode retrieved process it mostly like normal
			//Then afterward reset the $this->atts variable since it
			//got changed by the various shortcodes during processing
			foreach ( $shortcodes as $db_data ) {

				if ( $db_data === null ) {
					continue;
				}

				$db_atts = maybe_unserialize( $db_data->atts );
				$content = isset( $db_atts['term'] ) ? $db_atts['term'] : null;

				//Process the database version of the shortcode atts
				//Note, they should already be clean/correct, but in the
				//event a new default is added or whatnot, this will update
				//the shortcode with those default values.
				$processed = $this->process_atts( $db_atts, $content );

				if ( $processed === 'noterm' ) {
					return __( 'No query specified in shortcode or default settings', 'wbe' );
				}

				//Check cache if present use it, else run a new
				//query using the shortcode
				$this->cache_data = wbe_check_cache( $this->hash_id );
				if ( $this->cache_data !== null ) {
					$temp_result = maybe_unserialize( $this->cache_data->results_raw );
					if ( is_string( $temp_result ) ) {
						continue;
					}
				} else {
					$this->results_raw = null;
					$query             = $this->setup_query();

					$temp_result = $this->wbe_do_query( $query );
					if ( is_string( $temp_result ) ) {
						continue;
					}
				}

				$results_raw = array_merge( $results_raw, $temp_result );

			}

			//Process the shortcode options passed in with THIS shortcode
			//That is, if the user passed in attributes for the meta
			//shortcode, use those relative to the defaults for further
			//processing
			$atts       = $this->merge_atts( $atts );
			$this->atts = wbe_sanitize_atts( $atts );

			$temp_result = $results_raw;

		} else {

			//Standard single shortcode routine
			//Process the attributes, check the cache,run a query if needed
			$processed = $this->process_atts( $atts, $content );

			if ( $processed === 'noterm' ) {
				return __( 'No query specified in shortcode or default settings' . 'wbe' );
			}

			$this->cache_data = wbe_check_cache( $this->hash_id );
			if ( $this->cache_data !== null ) {
				return $this->cache_data->results;
			}

			$this->results_raw = null;
			$query             = $this->setup_query();

			$temp_result = $this->wbe_do_query( $query );
			if ( is_string( $temp_result ) ) {
				return $temp_result;
			}

		}

		$db = $this->atts['db'];
		if ( ( $db === 'pubmed' ) ||
		     ( $db === 'structure' && $this->atts['template'] !== 'structure' )
		) {
			//Filters out duplicate results since multiple shortcodes
			//could overlap in their returned results.
			$this->results_raw = apply_filters( 'wbe_entrez_results_array', $temp_result, 'pmid', $this->atts['order_by'] );
		} elseif ( $db === 'structure' ) {
			$this->results_raw = apply_filters( 'wbe_entrez_results_array', $temp_result, 'pdbid', $this->atts['order_by'] );
		} else {
			$this->results_raw = $temp_result;
		}

		$this->results = $this->format_output();

		if ( $process_multiple === false ) {
			$this->update_cache_and_shortcode();
		}

		return $this->results;
	}

	/**
	 * merge_atts
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return array
	 *
	 * @since 1.0.0
	 */
	protected function merge_atts( $atts, $content = null ) {

		/**
		 * This is done in several places throughout the code. The way this works is to first
		 * take the current options in the database and filter those so that only the shortcode
		 * type options (not other options, like cache life) remain. THEN use shortcode_atts with
		 * the applicable options to fill in any user supplied results.
		 */
		$current_options = wbe_get_options_table_options( 'options' );
		$atts_options    = array_intersect_key( $current_options, wbe_getg( 'short_atts' ) );

		//Parse the shortcode getting allowed attributes
		$atts = shortcode_atts( $atts_options, $atts );

		return $atts;
	}

	/**
	 * process_atts
	 *
	 * @param      $atts
	 * @param null $content
	 *
	 * @return bool|string
	 *
	 * @since 1.0.0
	 */
	protected function process_atts( $atts, $content = null ) {

		$atts = $this->merge_atts( $atts );

		//If retmax is set to 0 the request is for all matching publications
		//NCBI allows a large number of returns (100,000), but in practice, a value of
		//10000 should be more than enough to cover what most queries need.
		if ( intval( $atts['retmax'] ) === 0 ) {
			$atts['retmax'] = RETMAX_MAX;
		}

		//Initialize the default search term using the value from settings (if set)
		//If a custom query is passed in, use that instead. If one isn't passed in
		//and no default has been set in General Settings, then bail.
		if ( $content ) {
			$atts['term'] = $content;
		}
		if ( empty( $atts['term'] ) ) {
			return 'noterm';
		}

		//Validates AND sanitizes the inputs.
		$this->atts = wbe_sanitize_atts( $atts );

		//Not all attributes are used in calculating the hash (which is used for caching and
		//other methods requiring identification of a shortcode with a specific set of returned
		//results.
		$this->hashed_atts = array_diff_assoc( $this->atts, wbe_getg( 'short_atts' ) );
		$this->hash_id     = generate_hash( $this->hashed_atts );

		return true;
	}

	/**
	 * setup_query
	 *
	 * @return WBE_Lib
	 *
	 * @since 1.0.0
	 */
	protected function setup_query() {

		$results = null;

		//Otherwise, we need to run a full query against Entrez
		$wbe_entrez = new WBE_Lib();

		$wbe_entrez->db     = $this->atts['db'];
		$wbe_entrez->retmax = $this->atts['retmax'];
		$wbe_entrez->term   = $this->atts['term'];

		return $wbe_entrez;

	}

	/**
	 * wbe_do_query
	 *
	 * @param $wbe_entrez
	 *
	 * @return null|string
	 *
	 * @since 1.0.0
	 */
	protected function wbe_do_query( $wbe_entrez ) {

		$this->results_raw = $wbe_entrez->query();

		// If no results at all, early exit
		if ( $this->results_raw === null ) {
			return "No results for search term: {$this->atts['term']}";
		}

		return $this->results_raw;

	}

	/**
	 * format_output
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	protected function format_output() {

		// Process the results according to the options
		$formatter = new WBE_Formatter( $this->results_raw, $this->atts );
		$results   = $formatter->process_results();

		return $results;
	}

	/**
	 * update_cache_and_shortcode
	 *
	 * @return mixed
	 *
	 * @since 1.0.0
	 */
	public function update_cache_and_shortcode() {

		//create or update or otherwise process shortcode
		$this->shortcode   = wbe_shortcode_from_atts( $this->hashed_atts );
		$shortcode_db_data = wbe_db_update_shortcode( $this );

		// insert/update the cache for this shortcode if needed:
		// is expired, doesn't exist
		if ( wbe_use_cache() !== false ) {
			wbe_db_update_cache( $shortcode_db_data, $this );
		}

		return $shortcode_db_data->shortcode_id;
	}

}

$wbe_shortcode = new WBE_Shortcode();
