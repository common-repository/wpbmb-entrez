<?php
/**
 * Database Functions
 *
 * @package WPBMB Entrez
 * @version 1.0.0
 *
 */


/**
 * Plugin action and row meta links
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * wbe_db_install
 *
 *
 * @since 1.0.0
 */
function wbe_db_install() {

	//Globals
	global $wpdb;

	//Variables
	$version       = WBE_VERSION;
	$installed_ver = get_option( 'wbe_version' );

	if ( $installed_ver != WBE_VERSION ) {

		$max_index_length = 191;

		$defaults = wbe_getg( 'options_defaults' );

		//Cache table
		$table_name      = wbe_db_get_table_name( WBE_TABLE_CACHE );
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
		cache_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		shortcode_id bigint(20) unsigned NOT NULL DEFAULT '0',
		hash_id varchar(255) DEFAULT '' NOT NULL,
		created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		results longtext DEFAULT '',
		results_raw longtext DEFAULT '',
		PRIMARY KEY  (cache_id),
		KEY shortcode_id (shortcode_id),
		KEY hash_id (hash_id($max_index_length))
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		//Shortcode table
		$table_name = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );

		$sql = "CREATE TABLE $table_name (
		shortcode_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		created timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		hash_id varchar(255) DEFAULT '' NOT NULL,
		atts longtext DEFAULT '',
		shortcode text DEFAULT '',
		PRIMARY KEY  (shortcode_id),
		KEY hash_id (hash_id($max_index_length))
		) $charset_collate;";

		dbDelta( $sql );

		//Meta table
		$table_name = wbe_db_get_table_name( WBE_TABLE_META );

		$sql = "CREATE TABLE $table_name (
		  meta_id bigint(20) unsigned NOT NULL auto_increment,
		  shortcode_id bigint(20) unsigned NOT NULL default '0',
		  meta_key varchar(255) default NULL,
		  meta_value longtext,
		  PRIMARY KEY  (meta_id),
		  KEY shortcode_id (shortcode_id),
		  KEY meta_key (meta_key($max_index_length))
		) $charset_collate;";

		dbDelta( $sql );

		update_option( 'wbe_version', $version );
	}
}

/**
 * wbe_db_get_row
 *
 * @param $table_name
 * @param $key
 * @param $value
 *
 * @return array|null|object|void
 *
 * @since 1.0.0
 */
function wbe_db_get_row( $table_name, $key, $value ) {

	global $wpdb;

	$sql      = "SELECT * FROM $table_name WHERE $key = %s";
	$prepared = $wpdb->prepare( $sql, $value );

	return $wpdb->get_row( $prepared );

}

/**
 * wbe_db_update_shortcode
 *
 * @param $shortcode_obj
 *
 * @return array|null|object|void
 *
 * @since 1.0.0
 */
function wbe_db_update_shortcode( $shortcode_obj ) {

	global $wpdb;

	$table_name  = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );
	$serial_atts = maybe_serialize( $shortcode_obj->atts );

	//check if shortcode is defined (by hash)
	$data = wbe_db_get_row( $table_name, 'hash_id', $shortcode_obj->hash_id );

	// if not defined add to the shortcodes table
	if ( $data == null ) {
		$insert_id = $wpdb->insert(
			$table_name,
			array(
				'shortcode' => $shortcode_obj->shortcode,
				'hash_id'   => $shortcode_obj->hash_id,
				'atts'      => $serial_atts
			),
			array(
				'%s',
				'%s',
				'%s'
			)
		);

		if ( $insert_id !== false ) {
			$shortcode_obj->shortcode_id = $insert_id;
			$data                        = wbe_db_get_row( $table_name, 'hash_id', $shortcode_obj->hash_id );
		}
	}

	wbe_update_post_id_meta( $data );
	wbe_update_tags_meta( $data, $shortcode_obj );

	return $data;
}

/**
 * wbe_update_post_id_meta
 *
 * @param $data
 *
 * @since 1.0.0
 */
function wbe_update_post_id_meta( $data ) {

	global $wpdb;

	// insert/update meta table with the post id
	$table_name = wbe_db_get_table_name( WBE_TABLE_META );

	$post_id        = get_the_ID();
	$true_statement = "select 1 from {$table_name} where shortcode_id = %d AND meta_key = 'post_id' AND meta_value = %d";
	$true_statement = $wpdb->query( $wpdb->prepare( $true_statement, $data->shortcode_id, $post_id ) );

	if ( ! $true_statement ) {
		$insert_id = $wpdb->insert(
			$table_name,
			array(
				'shortcode_id' => $data->shortcode_id,
				'meta_key'     => 'post_id',
				'meta_value'   => $post_id
			),
			array(
				'%d',
				'%s',
				'%s'
			)
		);
	}

}

/**
 * wbe_update_tags_meta
 *
 * @param $data
 * @param $shortcode_obj
 *
 * @since 1.0.0
 */
function wbe_update_tags_meta( $data, $shortcode_obj ) {

	global $wpdb;

	// insert/update meta table with the post id
	$table_name = wbe_db_get_table_name( WBE_TABLE_META );

	if ( isset( $shortcode_obj->atts['tags'] ) == false ||
	     empty( $shortcode_obj->atts['tags'] )
	) {
		return;
	}

	$tags = array_map( 'trim', explode( ',', $shortcode_obj->atts['tags'] ) );

	foreach ( $tags as $tag ) {
		$true_statement = "select 1 from {$table_name} where shortcode_id = %d AND meta_key = 'tags' AND meta_value = %s";
		$true_statement = $wpdb->query( $wpdb->prepare( $true_statement, $data->shortcode_id, $tag ) );

		if ( ! $true_statement ) {
			$insert_id = $wpdb->insert(
				$table_name,
				array(
					'shortcode_id' => $data->shortcode_id,
					'meta_key'     => 'tags',
					'meta_value'   => $tag
				),
				array(
					'%d',
					'%s',
					'%s'
				)
			);
		}
	}

}

/**
 * wbe_db_update_cache
 *
 * @param $shortcode_db_data
 * @param $shortcode_obj
 *
 * @since 1.0.0
 */
function wbe_db_update_cache( $shortcode_db_data, $shortcode_obj ) {

	global $wpdb;

	$table_name = wbe_db_get_table_name( WBE_TABLE_CACHE );
	$data       = wbe_db_get_row( $table_name, 'hash_id', $shortcode_db_data->hash_id );

	$new_values = array(
		'shortcode_id' => $shortcode_db_data->shortcode_id,
		'hash_id'      => $shortcode_db_data->hash_id,
		'results'      => maybe_serialize( $shortcode_obj->results ),
		'results_raw'  => maybe_serialize( $shortcode_obj->results_raw )
	);
	$format     = array( '%d', '%s', '%s', '%s' );

	//If not already defined (e.g. new shortcode or cache cleared) create a new cache object
	if ( $data == null ) {
		$insert_id = $wpdb->insert( $table_name, $new_values, $format );
	} else {
		unset( $new_values['hash_id'] );
		$updated = $wpdb->update( $table_name, $new_values, array( 'hash_id' => $shortcode_db_data->hash_id ) );
	}
}

/**
 * wbe_db_get_shortcode
 *
 * @param $short_id
 *
 * @return array|null|object|void
 *
 * @since 1.0.0
 */
function wbe_db_get_shortcode( $short_id ) {

	$table_name = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );

	return wbe_db_get_row( $table_name, 'shortcode_id', $short_id );
}

/**
 * wbe_db_get_shortcodes
 *
 * @param $shortcode_ids
 *
 * @return array|null|object
 *
 * @since 1.0.0
 */
function wbe_db_get_shortcodes( $shortcode_ids ) {

	global $wpdb;

	$table_name = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );

	$shortcode_ids = array_map( 'esc_sql', $shortcode_ids );
	$id_string     = '"' . implode( '","', $shortcode_ids ) . '"';

	$sql     = "select * from {$table_name} where shortcode_id in({$id_string})";
	$results = $wpdb->get_results( $sql );

	return $results;
}

/**
 * wbe_db_get_shortcodes_by_meta
 *
 * @param $meta_key
 * @param $meta_values
 *
 * @return array|null|object
 *
 * @since 1.0.0
 */
function wbe_db_get_shortcodes_by_meta( $meta_key, $meta_values ) {

	global $wpdb;

	$table_name = wbe_db_get_table_name( WBE_TABLE_META );

	$meta_values = array_map( 'esc_sql', $meta_values );
	$meta_string = '"' . implode( '","', $meta_values ) . '"';

	$sql     = "SELECT DISTINCT shortcode_id FROM {$table_name} WHERE meta_key = '{$meta_key}' AND meta_value IN({$meta_string})";
	$results = $wpdb->get_results( $sql, 'OBJECT_K' );

	if ( ! empty( $results ) && is_array( $results ) ) {
		$results = array_keys( $results );
	}

	return wbe_db_get_shortcodes( $results );
}

/**
 * wbe_db_get_all_shortcodes
 *
 * @return array|null|object
 *
 * @since 1.0.0
 */
function wbe_db_get_all_shortcodes() {

	global $wpdb;
	$table_name = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );

	$sql = "SELECT * FROM {$table_name}";

	return $wpdb->get_results( $sql );

}

/**
 * wbe_db_get_meta_for_shortcode
 *
 * @param        $shortcode_id
 * @param        $meta_key
 * @param string $fields
 * @param string $return_type
 *
 * @return array|null|object
 *
 * @since 1.0.0
 */
function wbe_db_get_meta_for_shortcode( $shortcode_id, $meta_key, $fields = 'shortcode_id,meta_value', $return_type = 'ARRAY_N' ) {

	global $wpdb;

	$sql = "SELECT {$fields} FROM wp_wbe_meta WHERE shortcode_id = %s AND meta_key = %s";

	return $wpdb->get_results( $wpdb->prepare( $sql, $shortcode_id, $meta_key ), $return_type );

}

/**
 * wbe_db_delete_shortcode
 *
 * @param $shortcode_id
 *
 * @since 1.0.0
 */
function wbe_db_delete_shortcode( $shortcode_id ) {

	global $wpdb;

	$tables = array();

	$tables[] = wbe_db_get_table_name( WBE_TABLE_SHORTCODES );
	$tables[] = wbe_db_get_table_name( WBE_TABLE_META );
	$tables[] = wbe_db_get_table_name( WBE_TABLE_CACHE );

	foreach ( $tables as $table ) {
		$wpdb->delete( $table, array( 'shortcode_id' => $shortcode_id ) );
	}

}

/**
 * wbe_db_clear_table
 *
 * @param $table_name
 *
 * @since 1.0.0
 */
function wbe_db_clear_table( $table_name ) {

	global $wpdb;

	$sql = "TRUNCATE TABLE $table_name";
	$wpdb->query( $sql );

}

/**
 * wbe_db_delete_table
 *
 * @param $table_name
 *
 * @since 1.0.0
 */
function wbe_db_delete_table( $table_name ) {

	global $wpdb;

	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query( $sql );
}


/**
 * wbe_db_get_table_name
 *
 * @param $table_name
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_db_get_table_name( $table_name ) {
	global $wpdb;

	return $wpdb->prefix . $table_name;
}


// Scheduled Action Hook
/**
 * wbe_db_clean_cache
 *
 *
 * @since 1.0.0
 */
function wbe_db_clean_cache() {

	global $wpdb;

	$options = wbe_get_options_table_options();

	$cache_life   = $options['cache_life'] * 86400;
	$current_time = time();

	$past_time = $current_time - $cache_life;
	$past_time = date( 'Y-m-d h:m:s', $past_time );

	$table_name = wbe_db_get_table_name( WBE_TABLE_CACHE );

	$sql = $wpdb->prepare(
		"
                DELETE FROM $table_name
		 		WHERE created < %s
				",
		$past_time
	);

	$wpdb->query( $sql );

}


