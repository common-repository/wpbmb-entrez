<?php

/**
 * Cache helper functions
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wbe_use_cache
 *
 * @return bool
 *
 * @since 1.0.0
 */
function wbe_use_cache() {

	$options = wbe_get_options_table_options();

	$use_cache = false;
	if ( isset( $options['cache'] ) && $options['cache'] == 'on' ) {
		$use_cache = true;
	}

	return $use_cache;
}

/**
 * wbe_cache_is_valid
 *
 * @param $cache_data
 *
 * @return bool
 *
 * @since 1.0.0
 */
function wbe_cache_is_valid( $cache_data ) {

	$options        = wbe_get_options_table_options();
	$cache_is_valid = false;

	if ( time() - strtotime( $cache_data->created ) <= ( $options['cache_life'] * 86400 ) ) {
		$cache_is_valid = true;
	}

	return $cache_is_valid;
}

/**
 * wbe_check_cache
 *
 * @param $hash_id
 *
 * @return array|null|object|void
 *
 * @since 1.0.0
 */
function wbe_check_cache( $hash_id ) {

	$use_cache = wbe_use_cache();
	if ( $use_cache === false ) {
		return null;
	}

	$cache_table = wbe_db_get_table_name( WBE_TABLE_CACHE );
	$cache_data  = wbe_db_get_row( $cache_table, 'hash_id', $hash_id );

	// If it's not found return null
	if ( $cache_data == null ) {
		return $cache_data;
	}

	// Found something, check the timestamp
	if ( wbe_cache_is_valid( $cache_data ) == false ) {
		return null;
	}

	$cache_data->results     = maybe_unserialize( $cache_data->results );
	$cache_data->results_raw = maybe_unserialize( $cache_data->results_raw );

	return $cache_data;
}
