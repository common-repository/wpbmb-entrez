<?php
/*
 * Plugin Name: WPBMB Entrez
 * Plugin URI: http://biochem.slu.edu
 * Description: Automatically add and update NCBI Entrez (Pubmed) publications.
 * Version: 1.1.0
 * Text Domain: wbe
 * Author: David Gohara
 * Author URI: http://dgohara.me
 * License: MIT License
 *
 * @package WPBMB Entrez
 * @author David Gohara
 * @since 1.0.0
 *
 */

define( 'WBE_DOMAIN', 'wbe' );
define( 'WBE_PREFIX', WBE_DOMAIN . '_' );
define( 'WBE_VERSION', '1.1.0' );

define( 'WBE_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WBE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WBE_TEMPLATE_DIR', WBE_PLUGIN_DIR . 'templates/' );

//Custom tables
define( 'WBE_TABLE_CACHE', WBE_PREFIX . 'cache' );
define( 'WBE_TABLE_SHORTCODES', WBE_PREFIX . 'shortcodes' );
define( 'WBE_TABLE_META', WBE_PREFIX . 'meta' );

//Misc.
define( 'RETMAX_MAX', 10000 );

//Settings (stored in wp_options table)
global $wbe_options_array, $wbe_options, $wbe_display;
$wbe_options = WBE_PREFIX . 'options';
$wbe_display = WBE_PREFIX . 'display';

$wbe_options_array = array(
	'options' => $wbe_options,
	'display' => $wbe_display,
	'version' => WBE_PREFIX . 'version',
);

//Defaults
global $wbe_options_full;
global $wbe_options_defaults;
global $wbe_short_atts;
global $wbe_hashed_atts;
global $wbe_supported_dbs;
global $wbe_highlights_types;
global $wbe_display_blocks;

// CMB2 initialization for admin/settings pages
// Checks for the global CMB2_LOADED to ensure only one copy of CMB2
// is installed on a users site (either a dependency for another plugin or
// the CMB2 Plugin itself.
//
// See      : https://github.com/CMB2/CMB2/issues/181
// See also : includes/cmb2/readme.txt for reference to the global
//
if ( ! defined( 'CMB2_LOADED' ) ) {

	if ( file_exists( WBE_PLUGIN_DIR . 'includes/cmb2/init.php' ) ) {
		require_once( WBE_PLUGIN_DIR . 'includes/cmb2/init.php' );
	}

}

// General includes
require_once( WBE_PLUGIN_DIR . 'includes/wbe-sanitization.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-helpers.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-filters.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-db.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-entrez-lib.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-cache.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-shortcode.php' );
require_once( WBE_PLUGIN_DIR . 'includes/wbe-formatter.php' );

//Admin and settings includes
if ( is_admin() ) {

	require_once( WBE_PLUGIN_DIR . 'admin/wbe-settings.php' );

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpbmb_entrez_plugin_action_links', 10, 4 );

}

function wpbmb_entrez_plugin_action_links( $links, $plugin_file, $plugin_data, $context ) {

	$extra_links[] = '<a href="' . get_admin_url( null, "admin.php?page=wbe_options" ) . '">Settings</a>';

	return array_merge( $extra_links, $links );

}

// enqueue the CSS files
function wbe_enqueue_styles() {

	wp_enqueue_style( 'wpbmb-entrez', WBE_PLUGIN_URL . 'css/wpbmb-entrez.css' );
	wp_enqueue_style( 'wpbmb-entrez-featherlight', WBE_PLUGIN_URL . 'css/featherlight.min.css' );

	$display = wbe_get_options_table_options( 'display' );
	if ( ! empty( $display['computed'] ) ) {
		wp_add_inline_style( 'wpbmb-entrez', $display['computed'] );
	}

	// enqueue javascripts
	wp_register_script( 'wpbmb-entrez-featherlight-js', WBE_PLUGIN_URL . 'js/featherlight.min.js', array( 'jquery' ), WBE_VERSION, true );
	wp_enqueue_script( 'wpbmb-entrez-featherlight-js' );

}
add_action( 'wp_enqueue_scripts', 'wbe_enqueue_styles' );

// Check if the database needs up upgrading
function wbe_update_check() {

	$options_defaults = wbe_getg( 'options_defaults' );
	$opt_name         = wbe_getg( 'options' );

	if ( get_site_option( 'wbe_version' ) != WBE_VERSION ) {
		wbe_db_install();
	}

	//Schedule cache cleaning (or make sure it's scheduled)
	wbe_db_clean_cache();

}

add_action( 'plugins_loaded', 'wbe_update_check' );

// Schedule Cron Job Event
function wbe_schedule_clean_cache() {
	if ( ! wp_next_scheduled( 'wbe_db_clean_cache' ) ) {
		wp_schedule_event( current_time( 'timestamp' ), 'hourly', 'wbe_db_clean_cache' );
	}
}

add_action( 'plugins_loaded', 'wbe_schedule_clean_cache' );
add_action( 'wbe_db_clean_cache', 'wbe_db_clean_cache' );

function wbe_activate() {

	wbe_db_install();

	//Set up a scheduled task to clean up old cache files from posts
	//We do this since multiple cache objects can be associated with a post
	//and if the parameters get changed an old one can be orphaned in the meta data
	wbe_schedule_clean_cache();

}

register_activation_hook( __FILE__, 'wbe_activate' );

function wbe_deactivate() {

	wp_clear_scheduled_hook( 'wbe_db_clean_cache' );

}

register_deactivation_hook( __FILE__, 'wbe_deactivate' );

function wbe_uninstall() {

	/**
	 *   Remove settings:
	 *      versions (options table)
	 *      options  (options table)
	 *      display  (options table)
	 */
	global $wbe_options_array;
	foreach ( $wbe_options_array as $option ) {

		$ok = delete_option( $option );

	}

	/**
	 *   Remove tables:
	 *      wp_wbe_cache
	 *      wp_wbe_meta
	 *      wp_wbe_shortcodes
	 */

	wbe_db_delete_table( wbe_db_get_table_name( WBE_TABLE_CACHE ) );
	wbe_db_delete_table( wbe_db_get_table_name( WBE_TABLE_META ) );
	wbe_db_delete_table( wbe_db_get_table_name( WBE_TABLE_SHORTCODES ) );

}

register_uninstall_hook( __FILE__, 'wbe_uninstall' );



