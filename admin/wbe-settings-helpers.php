<?php
/**
 * WBE Settings Helpers
 * @version 1.0.0
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cmb2_render_shortcode_fieldtype_wbe_field
 *
 * This is a custom field type for displaying shortcodes in a selectable table.
 * This is actually an override for the entire display of the Shortcode tab in settings.
 * So for this particular tab in wbe-settings.php there is a single entry. All of the
 * machinery to display the ENTIRE tab is here
 *
 * @param $field
 *
 * @param $escaped_value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 *
 * @since 1.0.0
 */
function cmb2_render_shortcode_fieldtype_wbe_field( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

	if ( isset( $_POST['action'] ) == 'wbe_delete_shortcodes' ) {

		$cmb = $_POST;
		if ( ! is_array( $cmb ) ) {
			return;
		}

		$array = array_filter( $cmb, function ( $key ) {
			return strpos( $key, 'id_' ) === 0;
		}, ARRAY_FILTER_USE_KEY );
		if ( count( $array ) > 0 ) {

			foreach ( $array as $key => $value ) {

				$shortcode_id = str_replace( 'id_', '', $key );

				wbe_db_delete_shortcode( $shortcode_id );

			}
		}
	}

	wp_enqueue_style( 'wpbmb-entrez-admin-style', WBE_PLUGIN_URL . 'admin/css/wbe-admin-style.css' );

	$codes = wbe_db_get_all_shortcodes();
	?>
	<div class="wbe-section-header" style="margin-top: 20px;">Current Shortcodes</div>
	<div class="wbe-developer-header-text" style="max-width: 100%;">
		<p>Below are the shortcodes that are or have been used on the site. The shortcodes listed below aren't
			necessarily all being used. When a new shortcode is added to a post, it gets registered in the database,
			and
			optionally if caching is on, placed in the cache table. If the shortcode is removed from the post it
			isn't
			removed automatically from the database.</p>
		<p>The "Used On" column is simply a guide to help track down what shortcode may be used, and where. If
			you no longer need a shortcode, you can simply delete it below. If the same shortcode happens to be in
			use
			on a page, it'll be automatically added back to the database, the next time that particular page/post is
			loaded again.</p>
	</div>
	<form action="" method="post">
		<div class="cmb2-checkbox-list cmb2-list"></div>
		<div class="wbe-table">
			<div class="wbe-tr wbe-th">
				<div class="wbe-td checkbox"></div>
				<div class="wbe-td" style="flex-grow: 1; max-width: 40px;">SID</div>
				<div class="wbe-td" style="flex-grow: 3;">Shortcode</div>
				<div class="wbe-td" style="flex-grow: 1;">Tags</div>
				<div class="wbe-td" style="flex-grow: 2;">Used On (Post ID - Title)</div>
			</div>
			<?php
			foreach ( $codes as $code ) {
				$id        = $code->shortcode_id;
				$shortcode = $code->shortcode;
				$post_ids  = wbe_db_get_meta_for_shortcode( $id, 'post_id', 'meta_value', 'OBJECT_K' );
				$tags      = wbe_db_get_meta_for_shortcode( $id, 'tags', 'meta_value', 'OBJECT_K' );
				if ( ! empty( $tags ) ) {
					$tags = implode( ', ', array_keys( $tags ) );
				} else {
					$tags = '';
				}
				?>
				<div class="wbe-tr table-total">
					<div class="wbe-td checkbox">
						<?php
						echo $field_type_object->checkbox( array(
							'name'  => "id_{$id}",
							'id'    => "id_{$id}",
							'desc'  => '',
							'value' => '',
						), '' ); ?>
					</div>
					<div class="wbe-td" style="flex-grow: 1; max-width: 40px;"><?php echo intval( $id ) ?></div>
					<div class="wbe-td" style="flex-grow: 3;"><?php echo esc_html__( $shortcode ) ?></div>
					<div class="wbe-td" style="flex-grow: 1;"><?php echo esc_html__( $tags ) ?></div>
					<div class="wbe-td wbe-subcolumn" style="flex-grow: 2;">
						<?php
						foreach ( $post_ids as $post_key => $post_value ) {
							$post_title = get_the_title( $post_key );
							if ( empty( $post_title ) ) {
								continue;
							}
							?>
							<div class="wbe-subcolumn-stack"
								 style="flex-grow: 0;"><?php echo $post_key . ' - ' . $post_title ?></div>
						<?php } ?>
					</div>
				</div>

			<?php } ?>

		</div>
		<input type="hidden" name="action" name="wbe_delete_shortcodes" id="wbe_delete_shortcodes"
			   value="wbe_delete_shortcodes">
		<?php echo submit_button( 'Delete Selected', 'secondary', 'submit', false ); ?>
	</form>

	<?php

	wbe_shortcode_params_table();
}

/**
 * wbe_shortcode_params_table
 *
 *
 * @since 1.0.0
 */
function wbe_shortcode_params_table() {
	?>

	<div class="wbe-section-header">Parameter Documentation</div>
	<div>
		<div class="wbe-table">
			<div class="wbe-tr wbe-th">
				<div class="wbe-td" style="flex-grow: 1; max-width:150px;">Parameter</div>
				<div class="wbe-td" style="flex-grow: 2;">Description</div>
			</div>
			<?php
			$options = wbe_getg( 'options_full' );
			foreach ( $options as $key => $value ) {
				if ( $value['attr'] == false ) {
					continue;
				}
				?>

				<div class="wbe-tr table-total">
					<div class="wbe-td"
						 style="flex-grow: 1; max-width:150px; font-weight: bold;"><?php echo $key ?></div>
					<div class="wbe-td wbe-subcolumn" style="flex-grow: 2;">
						<div class="wbe-subcolumn-item" style="margin-bottom: 10px;">
							<?php esc_html_e( $value['desc'] ) ?>
						</div>

						<?php if ( ! empty( $value['default'] ) ) { ?>
							<div class="wbe-subcolumn-item">
								Default: <?php esc_html_e( $value['default'] ) ?>
							</div>
						<?php } ?>

						<?php if ( isset( $value['options'] ) && ! empty( $value['options'] ) ) {
							$keys            = array_keys( $value['options'] );
							$default_options = implode( ', ', $keys );
							?>
							<div class="wbe-subcolumn-item" style="margin-bottom: 10px;">
								Options: <?php esc_html_e( $default_options ) ?>
							</div>
						<?php } //endif ?>

						<?php if ( isset( $value['examples'] ) && ! empty( $value['examples'] ) ) {
							foreach ( $value['examples'] as $example ) {
								?>
								<div class="wbe-subcolumn-item">
									Example: <?php esc_html_e( $example ) ?>
								</div>
							<?php } //end foreach ?>
						<?php } //endif ?>

					</div>
				</div>

			<?php } ?>

		</div>
	</div>

	<?php
}

/**
 * cmb2_render_buildertype_wbe_field
 *
 * @param $field
 * @param $escaped_value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 *
 * @since 1.0.0
 */
function cmb2_render_buildertype_wbe_field( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

	wp_enqueue_style( 'wpbmb-entrez-admin-style', WBE_PLUGIN_URL . 'admin/css/wbe-admin-style.css' );


	$options  = wbe_get_options_table_options();
	$defaults = wbe_getg( 'options_full' );

	if ( isset( $_POST['action'] ) == 'wbe_generate_shortcode' ) {

		$atts         = $_POST;
		$shortcode    = new WBE_Shortcode( $atts );
		$shortcode_id = $shortcode->update_cache_and_shortcode();


		echo '<div class="wbe-builder-generated" style="margin-bottom: 20px; background: #fff;padding:10px;">';
		echo '<div class="wbe-builder-header" style="font-size: 1.3em; margin-bottom: 10px;">Generated Shortcode</div>';

		echo "<div style='margin-bottom: 10px;'><b>ID: </b>" . esc_html( $shortcode_id ) . "<br/><b>Shortcode: </b>" . esc_html( $shortcode->shortcode ) . "</div>";
		echo "<div>The shortcode can also be viewed in the Shortcodes tab.</div>";
		echo '</div>';

		$options = array_merge( wbe_getg( 'short_atts' ), $shortcode->atts );
	}

	?>

	<div style="margin-top: 20px; margin-bottom: 20px; font-size: 16px; font-weight: bold;">Shortcode Builder</div>
	<p>Use the form below to generate shortcodes. However, you can create shortcodes on the fly by simply inserting
		one
		in the content of a page or post.<br/>
		Current shortcodes, attribute options and their documentation are on the Shortcodes tab.</p>
	<div class="wbe-builder-table">
		<form name="builder" action="" method="post">
			<div class="wbe-builder-column-left" style="margin-bottom: 15px;">
				NCBI Search Query<br>
				<div class="wbe-builder-label">
					<input type="text" name="term" size="60" value="<?php echo $options['term'] ?>"
						   required="required">
				</div>
				<br>

				<div class="wbe-builder-label">Max Results :
					<input type="number" name="retmax" value="<?php echo $options['retmax'] ?>" min="0" max="10000">&nbsp;
					Author Limit: <input type="number" name="author_limit" value="0" min="0" max="10000">
				</div>
				<br>

				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Database:</div>
					<div class="wbe-builder-input">
						<select name="db">
							<?php
							foreach ( $defaults['db']['options'] as $opt_key => $opt_value ) {
								$selected = ( $opt_key == $options['db'] ) ? "selected='selected'" : "";
								echo '<option value="' . esc_attr( $opt_key ) . '" ' . $selected . '>' . esc_html( $opt_value ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Template:</div>
					<div class="wbe-builder-input">
						<select name="template">
							<?php
							foreach ( $defaults['template']['options'] as $opt_key => $opt_value ) {
								$selected = ( $opt_key == $options['template'] ) ? "selected='selected'" : "";
								echo '<option value="' . esc_attr( $opt_key ) . '" ' . $selected . '>' . esc_html( $opt_value ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Order By:</div>
					<div class="wbe-builder-input">
						<select name="order_by">
							<?php
							foreach ( $defaults['order_by']['options'] as $opt_key => $opt_value ) {
								$selected = ( $opt_key == $options['order_by'] ) ? "selected='selected'" : "";
								echo '<option value="' . esc_attr( $opt_key ) . '" ' . $selected . '>' . esc_html( $opt_value ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Highlight:</div>
					<div class="wbe-builder-input">
						<input type="text" name="highlights" size="50" placeholder="Words or phrases to highlight">
						<select name="highlights_type">
							<?php
							foreach ( $defaults['highlights_type']['options'] as $opt_key => $opt_value ) {
								$selected = ( $opt_key == $options['highlights_type'] ) ? "selected='selected'" : "";
								echo '<option value="' . esc_attr( $opt_key ) . '" ' . $selected . '>' . esc_html( $opt_value ) . '</option>';
							}
							?>
						</select>
					</div>
				</div>

				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Tags:</div>
					<div class="wbe-builder-input">
						<input type="text" name="tags" value="<?php echo $options['tags'] ?>" size="60"
							   placeholder="Tags (ex: virus, polymerase)">
					</div>
				</div>
				<div class="wbe-builder-element">
					<div class="wbe-builder-label">Use Tags:</div>
					<div class="wbe-builder-input">
						<input type="text" name="use_tags" value="<?php echo $options['use_tags'] ?>" size="60"
							   placeholder="Use specified tags">
					</div>
				</div>
			</div>
			<input type="hidden" name="action" name="wbe_generate_shortcode" id="wbe_generate_shortcode"
				   value="wbe_generate_shortcode">
			<?php echo submit_button( 'Generate Shortcode', 'secondary', 'submit', false ); ?>
		</form>
	</div>


	<?php
}

/**
 * wbe_shortcodes_modify_cmb2_metabox_form_format
 *
 *  Just an override to keep the default forms for the Shortcode and Developer tabs in settings.
 *
 * @param $form_format
 * @param $object_id
 * @param $cmb
 *
 * @return string
 *
 * @since 1.0.0
 */
function wbe_shortcodes_modify_cmb2_metabox_form_format( $form_format, $object_id, $cmb ) {

	if ( 'wbe_developer' == $cmb->cmb_id ||
	     'wbe_shortcodes' == $cmb->cmb_id ||
	     'wbe_builder' == $cmb->cmb_id
	) {

		return '';

	}

	return $form_format;
}

add_filter( 'cmb2_get_metabox_form_format', 'wbe_shortcodes_modify_cmb2_metabox_form_format', 10, 3 );

/**
 * wbe_process_general_options
 *
 * Hook to process the General Options option clear_cache
 *
 * @param $cmb
 * @param $object_id
 *
 * @return mixed
 *
 * @since 1.0.0
 */
function wbe_process_general_options( $cmb, $object_id ) {

	if ( isset( $cmb->data_to_save['clear_cache'] ) ) {

		$table_name = wbe_db_get_table_name( WBE_TABLE_CACHE );
		wbe_db_clear_table( $table_name );
	}
	unset( $cmb->data_to_save['clear_cache'] );

	return $cmb;
}

/**
 *
 * Below are elements for the Display settings field
 *
 */

/**
 * cmb2_render_displayitem_wbe_field
 *
 * Custom field for the Display settings page.
 *
 * @param $field_args
 * @param $value
 * @param $object_id
 * @param $object_type
 * @param $field_type_object
 *
 * @since 1.0.0
 */
function cmb2_render_displayitem_wbe_field( $field_args, $value, $object_id, $object_type, $field_type_object ) {

	$value = wp_parse_args( $value, array(
		'color'       => '',
		'font_size'   => '',
		'font_weight' => '',
	) );

	$weight_list = array(
		'default'  => '',
		'thin'     => 'Thin',
		'normal'   => 'Normal',
		'semibold' => 'Semibold',
		'bold'     => 'Bold',
		'bolder'   => 'Bolder',
	);

	$weight_options = '';
	foreach ( $weight_list as $abrev => $weight ) {
		$weight_options .= '<option value="' . $abrev . '" ' . selected( $value['font_weight'], $abrev, false ) . '>' . $weight . '</option>';
	}

	?>
	<div class="alignleft" style="padding-top: 5px;"><label
				for="<?php echo $field_type_object->_id( '_color' ); ?>"> </label>
		<?php echo $field_type_object->colorpicker( array(
			'name'  => $field_type_object->_name( '[color]' ),
			'id'    => $field_type_object->_id( '_color' ),
			'desc'  => '',
			'value' => $value['color'],
		), '#' ); ?>
	</div>
	<div class="alignleft" style="margin-right: 10px;"><label
				for="<?php echo $field_type_object->_id( '_font_size' ); ?>">Size (px): </label>
		<?php echo $field_type_object->input( array(
			'name'  => $field_type_object->_name( '[font_size]' ),
			'class' => 'cmb_text_small',
			'id'    => $field_type_object->_id( '_font_size' ),
			'value' => $value['font_size'],
			'desc'  => '',
			'size'  => 5,
		) ); ?>
	</div>
	<div class="alignleft"><label for="<?php echo $field_type_object->_id( '_font_weight' ); ?>'">Weight: </label>
		<?php echo $field_type_object->select( array(
			'name'             => $field_type_object->_name( '[font_weight]' ),
			'id'               => $field_type_object->_id( '_font_weight' ),
			'show_option_none' => true,
			'options'          => $weight_options,
			'desc'             => '',

		) ); ?>
	</div>
	<?php

	$class_tag = '.wbe-' . $field_args->args['id'];
	if ( strpos( $field_args->args['id'], '_lb' ) != false ) {
		$class_tag = '.lightbox .wbe-';
		$class_tag .= str_replace( '_lb', '', $field_args->args['id'] );
	}

	?>
	<div>CSS class tag: <?php echo $class_tag ?></div>
	<?php
	echo $field_type_object->_desc( true );

}

/**
 * wbe_display_clear_options
 *
 * Resets the Display settings page.
 *
 * @param $cmb
 * @param $object_id
 *
 * @return mixed
 *
 * @since 1.0.0
 */
function wbe_display_clear_options( $cmb, $object_id ) {

	global $wbe_display;

	if ( $object_id == $wbe_display && isset( $cmb->data_to_save['clear'] ) ) {
		$options = get_option( $wbe_display );
		foreach ( $options as $option => $entry ) {
			if ( $option == 'computed' && ! isset( $cmb->data_to_save['computed'] ) ) {
				continue;
			}
			if ( is_array( $cmb->data_to_save[ $option ] ) ) {
				foreach ( $cmb->data_to_save[ $option ] as $key => $value ) {
					$cmb->data_to_save[ $option ][ $key ] = '';
				}
			} else {
				$cmb->data_to_save[ $option ] = '';
			}
		}
		unset( $cmb->data_to_save['clear'] );
		delete_option( $object_id );
	}

	return $cmb;
}

function wbe_display_header( $object_type, $cmb ) {

	global $wbe_display;

	if ( $object_type == $wbe_display ) {

		$output = '<div style="margin-top:25px; ">';
		$output .= 'You can customize the typography by changing the settings below. For reference the class tags are shown if you prefer to make the changes in a style sheet. For additional (or more fine grained) styling options, see the Styles, Templates and Partials section on the Developer tab.';
		$output .= '</div>';
		$output .= '<div style="font-size: 16px; margin-top:25px; font-weight: bold;">';
		$output .= 'Page Display Settings';
		$output .= '</div>';

		echo $output;
	} elseif ( $object_type == 'wbe_shortcodes' ) {

		//wbe_display_shortcode_information();

	} elseif ( $object_type == 'wbe_developer' ) {
		wbe_display_developer_information();
	}

	return $cmb;
}

function wbe_display_section_title( $field_args, $field ) {

	$output = '';
	$output .= '<div style="font-size: 16px; margin-top:15px; font-weight: bold;">';
	$output .= esc_html( $field_args['name'] );
	$output .= '</div>';

	return $output;

}

function wbe_display_developer_information() {

	wp_enqueue_style( 'wpbmb-entrez-admin-style', WBE_PLUGIN_URL . 'admin/css/wbe-admin-style.css' );

	$filters   = wbe_getg( 'filters' );
	$templates = wbe_templates();

	?>

	<div class="wbe-section-header">Filters</div>
	<div class="wbe-developer-header-text" style="max-width: 100%;">
		<?php echo __( 'For developers the current list of available filters (e.g. apply_filter(...)) are listed below. For reference, implementations can be found in the file &lt;plugin_dir&gt;/classes/wbe-filters.php', 'wbe' )
		?>
	</div>
	<div class="wbe-table">
		<div class="wbe-tr wbe-th">
			<div class="wbe-td" style="flex-grow: 1;">Filter</div>
			<div class="wbe-td" style="flex-grow: 2;">Description</div>
		</div>
		<?php
		foreach ( $filters as $filter ) {
			?>
			<div class="wbe-tr table-total">
				<div class="wbe-td" style="flex-grow: 1;"><?php esc_html_e( $filter['filter'] ) ?></div>
				<div class="wbe-td" style="flex-grow: 2;"><?php esc_html_e( $filter['desc'] ) ?></div>
			</div>

		<?php } ?>

	</div>

	<div class="wbe-section-header">Styles, Templates and Partials</div>
	<div class="wbe-developer-header-text" style="max-width: 100%;">
		<p>Additionally, the CSS and template files used for styling query results are provided below. These can be
			overriden by creating an equivalent directory in your theme folder (preferably child theme) and placing the
			modified files there.</p>
		<p>When results are rendered using the plugin provided templates, special class tags are added so you can target
			one specific element for only that particular template (as opposed to setting the general class tag,
			affecting all templates). The dynamically generated CSS tags use the following form:</p>
		<p>(template-name)-section</p>
		<p>As an example if you are using the Inline Abstract template, and you want to modify the CSS for the title and
			abstract (but not the links). The CSS tag would be:</p>
		<p>.inline-abstract-title { your custom CSS }<br/>
			.inline-abstract-abstract { your custom CSS }</p>
		<p>Note the extra "abstract" in the second one, due to the template containing the word abstract.</p>
		<p>Custom templates can also be designed and used. For example to create a custom layout template for pubmed
			search results (e.g. Lightbox):</p>
		<p>
			1) Create a folder called 'templates' in your theme/child theme.<br>
			2) Copy the file of interest (wbe-lightbox.php) from the plugin 'templates' folder into the 'templates'
			folder you just created.<br/>
			3) Modify the layout to suit your requirements.
		</p>
		<p>Many of the templates make use of partials. You can either copy those (making the same directory structure as
			in the plugin) or you can simply modify the file you copied into your templates folder and have it in a
			single template.</p>

		<p>Creating new templates without overriding an existing one is also possible. Simply create a file in your
			templates folder with the following format wbe-&lt;new&gt;-&lt;layout&gt;.php (obviously don't include the
			&lt; and &gt; symbols). The name of the layout will appear on the General Settings. And you can reference it
			in manual shortcodes by using the &lt;new&gt;-&lt;layout&gt; part of the filename as the template name. For
			example, to create a new template called "My Awesome Template":</p>
		<p>Create a file in your child theme templates folder (see above) called: wbe-my-awesome-template.php</p>
		<p>This will do two things. Make your template available as a default option in the General Settings tab. And
			allow you to specify the template in the shortcode.</p>
		<p>Example: [wpbmb template="my-awesome-template"]</p>
		<p>Below are a listing of the style sheet and default templates. Partials aren't shown below.</p><br/>
	</div>
	<div class="wbe-table">
		<div class="wbe-tr wbe-th">
			<div class="wbe-td" style="flex-grow: 1;">Type</div>
			<div class="wbe-td" style="flex-grow: 2;">Filename</div>
		</div>
		<div class="wbe-tr table-total">
			<div class="wbe-td" style="flex-grow: 1;">style</div>
			<div class="wbe-td" style="flex-grow: 2;">&lt;plugin_dir&gt;/css/wpbmb-entrez.css</div>
		</div>
		<?php
		foreach ( $templates as $template ) {
			?>
			<div class="wbe-tr table-total">
				<div class="wbe-td" style="flex-grow: 1;">template</div>
				<div class="wbe-td" style="flex-grow: 2;">
					&lt;plugin_dir&gt;/templates/wbe-<?php esc_html_e( $template ) ?>.php
				</div>
			</div>

		<?php } ?>

	</div>

	<?php
}

function wbe_generate_css_on_save( $options, $cmb_option ) {

	$css = '';
	foreach ( $cmb_option as $group => $data ) {

		if ( ! is_array( $data ) ) {
			continue;
		}

		$ndata                = array_filter( $data );
		$cmb_option[ $group ] = $ndata;

		if ( ! empty( $ndata ) ) {

			$computed_value = '/* Custom CSS for WPBMB Entrez */';
			foreach ( $ndata as $key => $value ) {
				$computed_value .= "\n\t";
				switch ( $key ) {
					case "color":
						$computed_value .= "color: {$value}; ";
						break;
					case "font_size":
						$value          = wbe_sanitize( $value, 'font_size' );
						$computed_value .= "font-size: {$value}; ";
						break;
					case "font_weight":
						if ( $value == 'default' || $value == 'normal' || $value == 'none' ) {
							$data[ $key ] = '';
							break;
						}

						$value          = wbe_sanitize( $value, 'font_weight' );
						$computed_value .= "font-weight: {$value}; ";
						break;
				} //end switch

//                $css .= $computed_value;

			} // foreach

			if ( ! empty( preg_replace( '/\s+/', '', $computed_value ) ) ) {

				$lightbox = ( strpos( $group, '_lb' ) != false ) ? ".lightbox " : "";

				$css .= "\n" . $lightbox . '.wbe-' . str_replace( '_lb', '', $group ) . "{";
				$css .= $computed_value;
				$css .= "\n}";
			}

		} // end if
	}
	if ( ! empty( $css ) ) {
		$cmb_option['computed'] = $css;
	} elseif ( isset( $cmb_option['computed'] ) ) {
		unset( $cmb_option['computed'] );
	}

	update_option( 'wbe_display', array_filter( $cmb_option ) );

	return false;
}

add_filter( "cmb2_override_option_save_wbe_display", "wbe_generate_css_on_save", 10, 2 );

