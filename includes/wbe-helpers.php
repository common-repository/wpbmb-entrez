<?php
/**
 * WBE General Helpers
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wbe_setup_defaults
 *
 * @return bool
 *
 * @since 1.0.0
 */
function wbe_setup_defaults() {

	global $wbe_supported_dbs;
	$wbe_supported_dbs = array(
		'pubmed'    => 'Pubmed',
		'structure' => 'Structure',
	);

	global $wbe_highlights_types;
	$wbe_highlights_types = array(
		'bold'       => 'Bold',
		'italic'     => 'Italic',
		'underline'  => 'Underline',
		'background' => 'Background',
	);

	$defaults = array();

	$defaults['retmax'] = array(
		'type'     => 'string_or_int',
		'default'  => 5,
		'attr'     => true,
		'desc'     => __( 'Controls the maximum number of results to return.', 'wbe' ),
		'examples' => array( '[wpbmb retmax=10]' ),
		'hashed'   => true,
		'field'    => 'number',
	);

	$defaults['db'] = array(
		'type'     => 'string',
		'default'  => 'pubmed',
		'attr'     => true,
		'desc'     => __( 'Database to search. Changing this value to an arbitrary database (e.g. gene) is acceptable, however parsing and display of the results will probably fail.', 'wbe' ),
		'examples' => array( '[wpbmb db="pubmed"]' ),
		'options'  => $wbe_supported_dbs,
		'hashed'   => true,
		'field'    => 'select',
	);

	$defaults['term'] = array(
		'type'     => 'string',
		'default'  => '',
		'attr'     => true,
		'desc'     => __( 'The query to search. Any query that can be submitted through the Entrez site can be used. Queries should use the alternate enclosing shortcode syntax. If no default query is specified under General Options, then a term must be provided.', 'wbe' ),
		'examples' => array(
			'[wpbmb]Smith JA[Author][/wpbmb]',
			'[wpbmb retmax=10]((Saint Louis University[Affiliation]) AND Gohara DW[Author]) AND "Journal of Biological Chemistry"[Journal][/wpbmb]',
		),
		'hashed'   => true,
		'field'    => 'text',
	);

	$defaults['author_limit'] = array(
		'type'    => 'string_or_int',
		'default' => 0,
		'attr'    => true,
		'desc'    => __( 'Controls the maximum number of authors to display. Useful for publications with very long lists of authors. Use a value of 0 (zero) to include all authors.', 'wbe' ),
		'hashed'  => true,
		'field'   => 'number',
	);

	$defaults['template'] = array(
		'type'     => 'string',
		'default'  => 'lightbox',
		'attr'     => true,
		'desc'     => __( 'Override the template used to display results. Common parameters in templates are usually title and author(s). Most templates are specific to the database they are querying. You can also specify custom templates. For more information on templates, see the Developer tab.', 'wbe' ),
		'examples' => array(
			'[wpbmb db="structure" template="structure"]Smith JA[Author][/wpbmb]',
			'[wpbmb template="inline-abstract"]',
		),
		'options'  => wbe_templates_as_options(),
		'hashed'   => true,
		'field'    => 'select',
	);

	$defaults['highlights'] = array(
		'type'     => 'string',
		'default'  => '',
		'attr'     => true,
		'desc'     => __( 'A comma separated list of strings that will get highlighted in the output using the highlight type specified. Searching is case-insensitive, but will preserve the case of the text to be highlighted.', 'wbe' ),
		'examples' => array(
			'[wpbmb highlights="polymerase,Smith JA,virus]',
		),
		'hashed'   => true,
		'field'    => 'text',
	);

	$defaults['highlights_type'] = array(
		'type'    => 'string',
		'default' => 'bold',
		'attr'    => true,
		'desc'    => '',
		'options' => $wbe_highlights_types,
		'hashed'  => true,
		'field'   => 'select',
	);

	$defaults['cache'] = array(
		'type'    => 'string',
		'default' => 'on',
		'attr'    => false,
		'desc'    => '',
		'hashed'  => false,
	);

	$defaults['cache_life'] = array(
		'type'    => 'int',
		'default' => 7,
		'attr'    => false,
		'desc'    => '',
		'hashed'  => false,
	);

	$defaults['order_by'] = array(
		'type'    => 'string',
		'default' => 'date-dec',
		'attr'    => true,
		'desc'    => __( 'Sets the field used for sorting returned results. By default the results are listed newest to oldest by date.', 'wbe' ),
		'options' => array(
			'date-dec' => 'Newest First',
			'date-asc' => 'Oldest First'
		),
		'hashed'  => true,
		'field'   => 'select',
	);

	$defaults['tags'] = array(
		'type'     => 'string',
		'default'  => '',
		'attr'     => true,
		'desc'     => __( 'Tags for each shortcode can be supplied. Multiple comma-delimited tags can be assigned to a shortcode. This may be useful when a single search query cannot accurately or reliably return the full set of desired results.', 'wbe' ),
		'examples' => array( '[wpbmb tags="faculty,emeritus"]', ),
		'hashed'   => true,
		'field'    => 'text',
	);

	$defaults['use_tags'] = array(
		'type'     => 'string',
		'default'  => '',
		'attr'     => true,
		'desc'     => __( 'Specify tags defined in other shortcodes, for grouping or merging into a single list.', 'wbe' ),
		'examples' => array(
			'[wpbmb use_tags="faculty"]',
			'[wpbmb use_tags="faculty,polymerase"]',
		),
		'hashed'   => true,
		'field'    => 'text',
	);

	$defaults['sid'] = array(
		'type'     => 'string',
		'default'  => '',
		'attr'     => true,
		'desc'     => __( 'Specify shortcodes to use (or to merge). See the table for the SID of a particular shortcode. This may be convenient, if a shortcode you wish to use is long/complicated. You can use the Builder to create new shortcodes or simply reference shortcodes already in use. If you need a to reference a large number of shortcodes, use tags (see above).', 'wbe' ),
		'examples' => array(
			'[wpbmb sid="15"]',
			'[wpbmb sid="2,6,15"]',
		),
		'hashed'   => true,
	);

	global $wbe_options_full;
	global $wbe_options_defaults;
	global $wbe_short_atts;
	global $wbe_hashed_atts;

	$wbe_options_full = $defaults;

	$wbe_options_defaults = array();
	$wbe_short_atts       = array();
	$wbe_hashed_atts      = array();

	foreach ( $defaults as $key => $value ) {

		$wbe_options_defaults[ $key ] = $value['default'];

		if ( $value['attr'] == true ) {
			$wbe_short_atts[ $key ] = $value['default'];
		}

		if ( $value['hashed'] == true ) {
			$wbe_hashed_atts[ $key ] = $key;
		}

	}

	global $wbe_display_blocks;

	//Default blocks to show
	$blocks = array(
		'title',
		'authors',
		'journal',
		'abstract',
		'links',
	);

	$wbe_display_blocks = $blocks;

	return true;

}

wbe_setup_defaults();

/**
 * wbe_get_options_table_options
 *
 * @param string $option
 *
 * @return array|mixed|void
 *
 * @since 1.0.0
 */
function wbe_get_options_table_options( $option = 'options' ) {

	global $wbe_options_array;
	$options_full = wbe_getg( 'options_full' );

	$options = get_option( $wbe_options_array[ $option ] );
	if ( $options == false && $option == wbe_getg( 'options' ) ) {
		update_option( $wbe_options_array[ $option ], $options_full );
		$options = get_option( $wbe_options_array[ $option ] );
	}

	if ( $option == 'options' ) {
		$options = array_merge( wbe_getg( 'short_atts' ), $options );
	}

	return $options;
}

/**
 * wbe_getg
 *
 * @param $key
 *
 * @return mixed
 *
 * @since 1.0.0
 */
function wbe_getg( $key ) {

	$new_key = WBE_PREFIX . $key;

	global ${$new_key};

	return ${$new_key};
}

/**
 * generate_hash
 *
 * @param $atts
 *
 * @return string
 *
 * @since 1.0.0
 */
function generate_hash( $atts ) {

	$hash     = '';
	$hash_key = '';

	foreach ( $atts as $opt ) {
		$hash_key .= $opt;
	}
	$hash .= md5( $hash_key );

	return $hash;

}

/**
 * wbe_shortcode_from_atts
 *
 * @param      $atts
 * @param bool $filter
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_shortcode_from_atts( $atts, $filter = false ) {

	$diff = $atts;
	if ( $filter ) {
		$options = wbe_get_options_table_options( 'options' );
		$diff    = array_diff_assoc( $atts, $options );
	}

	$options_full = wbe_getg( 'options_full' );

	$params = '';
	foreach ( $diff as $key => $value ) {
		if ( $key == 'term' ) {
			continue;
		}
		if ( $key == 'retmax' && $value === RETMAX_MAX ) {
			$value = 0;
		}

		$type = $options_full[ $key ]['type'];

		switch ( $type ) {
			case 'string':
				$params .= " {$key}=\"{$value}\"";
				break;
			default:
				$params .= " {$key}={$value}";
		}

	}

	$code = "[wpbmb{$params}]";
	if ( isset( $diff['term'] ) ) {
		$code .= "{$diff['term']}[/wpbmb]";
	}

	return $code;

}

/**
 * wbe_templates
 *
 * @param bool $filter
 *
 * @return array
 *
 * @since 1.0.0
 */
function wbe_templates( $filter = true ) {

	$file_paths = glob( WBE_TEMPLATE_DIR . 'wbe-*.php' );

	$files = array();
	foreach ( $file_paths as $file_path ) {
		$filename = basename( $file_path );
		if ( $filter == true ) {
			$filename           = str_replace( 'wbe-', '', basename( $file_path, '.php' ) );
			$files[ $filename ] = $filename;
		} else {
			$files[] = $filename;
		}
	}

	return $files;
}

/**
 * wbe_templates_as_options
 *
 * @return array
 *
 * @since 1.0.0
 */
function wbe_templates_as_options() {

	$templates = wbe_templates();
	foreach ( $templates as $key => $value ) {
//                if ( strpos( 'structure' ) != false ) continue;

		$value             = str_replace( '-', ' ', $value );
		$value             = ucwords( $value );
		$templates[ $key ] = $value;
	}

	return $templates;
}

/**
 * wbe_template_file
 *
 * @param string $template
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_template_file( $template = 'lightbox' ) {

	$template_file = 'templates/wbe-' . $template . '.php';

	$located_template = locate_template( $template_file );
	if ( empty( $located_template ) && file_exists( WBE_PLUGIN_DIR . $template_file ) ) {
		$located_template = WBE_PLUGIN_DIR . $template_file;
	}

	return $located_template;
}

/**
 * wbe_partials_file
 *
 * @param $template
 * @param $block
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_partials_file( $template, $block ) {

	$partials_file = "wbe-{$block}.php";

	$located_partials = locate_template( 'templates/' . $template . '/' . $partials_file );
	if ( empty( $located_partials ) ) {
		$located_partials = locate_template( 'templates/default/' . $partials_file );
	}

	if ( empty( $located_partials ) ) {
		if ( file_exists( WBE_TEMPLATE_DIR . $template . '/' . $partials_file ) ) {
			$located_partials = WBE_TEMPLATE_DIR . $template . '/' . $partials_file;
		} elseif ( file_exists( WBE_TEMPLATE_DIR . 'default' ) ) {
			$located_partials = WBE_TEMPLATE_DIR . 'default/' . $partials_file;
		}
	}

	return $located_partials;
}

//from https://stackoverflow.com/a/44295407
/**
 * wbe_array_unique_by_subkey
 *
 * @param $array
 * @param $subkey
 *
 * @return array
 *
 * @since 1.0.0
 */
function wbe_array_unique_by_subkey( $array, $subkey ) {

	$temp = array();

	$unique = array_filter( $array, function ( $v ) use ( &$temp, $subkey ) {

		if ( is_object( $v ) ) {
			$v = (array) $v;
		}

		if ( ! array_key_exists( $subkey, $v ) ) {
			return false;
		}

		if ( in_array( $v[ $subkey ], $temp ) ) {
			return false;
		} else {
			array_push( $temp, $v[ $subkey ] );

			return true;
		}
	} );

	return $unique;
}

/**
 * wbe_include_template_part
 *
 * @param $template_part
 *
 * @since 1.0.0
 */
function wbe_include_template_part( $template_part ) {

	$template_file = 'templates/' . $template_part . '.php';
	if ( ! locate_template( $template_file ) ) {
		include( WBE_PLUGIN_DIR . $template_file );
	} else {
		include( locate_template( $template_file ) );
	}


}
