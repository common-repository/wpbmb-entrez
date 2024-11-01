<?php
/**
 * WPBMB Entrez Options
 * @version 1.0.0
 *
 * modified from: http://arushad.org/how-to-create-a-tabbed-options-page-for-your-wordpress-theme-using-cmb/
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once( WBE_PLUGIN_DIR . 'admin/wbe-settings-helpers.php' );

if ( ! class_exists( 'WBE_Settings' ) ) {

	class WBE_Settings {

		/**
		 * Options page metabox id
		 * @var array
		 */
		protected $option_metabox = array();

		/**
		 * Options Page title
		 * @var string
		 */
		protected $title = '';

		/**
		 * Options Page hook
		 * @var array
		 */
		protected $options_pages = array();

		/**
		 * Holds an instance of the object
		 *
		 * @var WBE_Settings
		 */
		protected static $instance = null;

		/**
		 * Returns the running object
		 *
		 * @return WBE_Settings
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
				self::$instance->hooks();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 * @since 1.0.0
		 */
		protected function __construct() {
			$this->title = __( 'WPBMB Entrez Settings', 'wbe' );
		}

		/**
		 * Initiate our hooks
		 * @since 1.0.0
		 */
		public function hooks() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );

			add_action( 'cmb2_render_displayitem_wbe', 'cmb2_render_displayitem_wbe_field', 10, 5 );
			add_action( 'cmb2_render_shortcode_fieldtype_wbe', 'cmb2_render_shortcode_fieldtype_wbe_field', 10, 5 );
			add_action( 'cmb2_render_buildertype_wbe', 'cmb2_render_buildertype_wbe_field', 10, 5 );

			add_action( 'cmb2_options-page_process_fields_wbe_display', 'wbe_display_clear_options', 10, 2 );
			add_action( 'cmb2_options-page_process_fields_wbe_options', 'wbe_process_general_options', 10, 2 );
			add_action( 'cmb2_before_form', 'wbe_display_header', 10, 2, 4 );
		}

		/**
		 * Register our setting to WP
		 * @since  1.0.0
		 */
		public function init() {
			$option_tabs = self::option_fields();
			foreach ( $option_tabs as $index => $option_tab ) {
				register_setting( $option_tab['id'], $option_tab['id'] );
			}
		}

		/**
		 * Add menu options page
		 * @since 1.0.0
		 */
		public function add_options_page() {

			$option_tabs = self::option_fields();
			foreach ( $option_tabs as $index => $option_tab ) {
				if ( $index == 0 ) {
					$this->options_pages[] = add_menu_page( $this->title, $this->title, 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ), 'dashicons-admin-settings' ); //Link admin menu to first tab
					add_submenu_page( $option_tabs[0]['id'], $this->title, $option_tab['title'], 'manage_options', $option_tab['id'], array( $this, 'admin_page_display' ) ); //Duplicate menu link for first submenu page
				} else {
					$this->options_pages[] = add_submenu_page( $option_tabs[0]['id'], $this->title, $option_tab['title'], 'manage_options', $option_tab['id'], array(
						$this,
						'admin_page_display'
					) );
				}
			}
		}

		/**
		 * Admin page markup. Mostly handled by CMB2
		 * @since  1.0.0
		 */
		public function admin_page_display() {

			global $wbe_options;

			$option_tabs = self::option_fields(); //get all option tabs
			$tab_forms   = array();

			?>
            <div class="wrap cmb_options_page <?php esc_attr_e( $wbe_options ); ?>">
                <h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

                <!-- Options Page Nav Tabs -->
                <h2 class="nav-tab-wrapper">
					<?php foreach ( $option_tabs as $option_tab ) :
						$tab_slug = $option_tab['id'];
						$nav_class = 'nav-tab';
						if ( $tab_slug == $_GET['page'] ) {
							$nav_class   .= ' nav-tab-active'; //add active class to current tab
							$tab_forms[] = $option_tab; //add current tab to forms to be rendered
						}
						?>
                        <a class="<?php esc_attr_e( $nav_class ); ?>"
                           href="<?php menu_page_url( $tab_slug ); ?>"><?php esc_attr_e( $option_tab['title'] ); ?></a>
					<?php endforeach; ?>
                </h2>
                <!-- End of Nav Tabs -->

				<?php foreach ( $tab_forms as $tab_form ) : //render all tab forms (normaly just 1 form) ?>
                    <div id="<?php esc_attr_e( $tab_form['id'] ); ?>" class="group">
						<?php cmb2_metabox_form( $tab_form, $tab_form['id'] ); ?>
                    </div>
				<?php endforeach; ?>
            </div>
			<?php
		}

		/**
		 * Defines the theme option metabox and field configuration
		 * @since  1.0.0
		 * @return array
		 */
		public function option_fields() {

			global $wbe_options;
			global $wbe_display;
			global $wbe_options_defaults;
			global $wbe_supported_dbs;
			global $wbe_options_full;

//			$options = get_option( $wbe_options );
//			if ( $options == false ) {
//				update_option( $wbe_options, $wbe_options_defaults );
//			}

			$options = wbe_get_options_table_options();

			// Only need to initiate the array once per page-load
			if ( ! empty( $this->option_metabox ) ) {
				return $this->option_metabox;
			}

			$templates = wbe_templates();
			foreach ( $templates as $key => $value ) {
//                if ( strpos( 'structure' ) != false ) continue;

				$value             = str_replace( '-', ' ', $value );
				$value             = ucwords( $value );
				$templates[ $key ] = $value;
			}

			$style                  = 'style="color: #8e8e8e;"';
			$this->option_metabox[] = array(
				'id'         => $wbe_options,
				'title'      => 'General Options',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $wbe_options ), ),
				'show_names' => true,
				'fields'     => array(
					array(
						'name'    => __( 'Default Query', 'wbe' ),
						'desc'    => __( "<div {$style}>View documentation at <a href='//www.ncbi.nlm.nih.gov/books/NBK3827/#pubmedhelp.Search_Field_Descriptions_and' target='_blank' >NCBI</a></div>", 'wbe' ),
						'id'      => 'term',
						'type'    => 'text',
						'default' => $wbe_options_defaults['term'],
					),
					array(
						'name'       => __( 'Maximum Returned Entries', 'wbe' ),
						'desc'       => __( "<div {$style}>Set to 0 to return all entries</div>", 'wbe' ),
						'id'         => 'retmax',
						'type'       => 'text_small',
						'default'    => $wbe_options_defaults['retmax'],
						'attributes' => array(
							'type'    => 'number',
							'pattern' => '\d*',
							'min'     => '0',
						)
					),
					array(
						'name'       => __( 'Limit Authors', 'wbe' ),
						'desc'       => __( "<div {$style}>Limit number of authors listed if over the specified number.<br>Set to 0 to list all authors</div>", 'wbe' ),
						'id'         => 'author_limit',
						'type'       => 'text_small',
						'default'    => $wbe_options_defaults['author_limit'],
						'attributes' => array(
							'type'    => 'number',
							'pattern' => '\d*',
							'min'     => '0',
						)
					),
					array(
						'name'    => __( 'Database', 'wbe' ),
						'desc'    => __( '', 'wbe' ),
						'id'      => 'db',
						'type'    => 'select',
						'default' => $wbe_options_defaults['db'],
						'options' => $wbe_supported_dbs,
					),
					array(
						'name'    => __( 'Layout Template', 'wbe' ),
						'desc'    => __( "<div {$style}>Structure templates should only be used with structure database searches.</div>", 'wbe' ),
						'id'      => 'template',
						'type'    => 'select',
						'default' => 'lightbox',
						'options' => $templates,
					),
					array(
						'name'    => __( 'Sort Order', 'wbe' ),
						'desc'    => __( "", 'wbe' ),
						'id'      => 'order_by',
						'type'    => 'select',
						'default' => $wbe_options_defaults['order_by'],
						'options' => $wbe_options_full['order_by']['options'],
					),
					array(
						'name'         => __( 'Cache Results', 'wbe' ),
						'desc'         => __( "<span {$style}>Store results in local database for faster access (recommended)</span>", 'wbe' ),
						'id'           => 'cache',
						'type'         => 'checkbox',
						'before_field' => '<div style="padding-top: 8px;"></div>',
					),
					array(
						'name'       => __( 'Cache Lifetime', 'wbe' ),
						'desc'       => __( "<div {$style}>(in days)</div>", 'wbe' ),
						'id'         => 'cache_life',
						'type'       => 'text_small',
						'default'    => $wbe_options_defaults['cache_life'],
						'attributes' => array(
							'type'    => 'number',
							'pattern' => '\d*',
						)
					),
					array(
						'name'         => __( 'Clear Cache', 'wbe' ),
						'desc'         => __( "<span {$style}>Clear WPBMB Entrez cache table on next save.</span>", 'wbe' ),
						'id'           => 'clear_cache',
						'type'         => 'checkbox',
						'before_field' => '<div style="padding-top: 8px;"></div>',
					),
				)
			);

			$display                = wbe_get_options_table_options( 'display' );
			$this->option_metabox[] = array(
				'id'         => $wbe_display,
				'title'      => 'Display Settings',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $wbe_display ), ),
				'show_names' => true,
				'fields'     => array(
					array(
						'name'            => 'Article Title',
						'id'              => 'title',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Authors',
						'id'              => 'authors',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Abstract',
						'id'              => 'abstract',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Links',
						'id'              => 'links',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),


					array(
						'name'          => 'Lightbox Display Settings',
						'desc'          => '',
						'type'          => 'title',
						'id'            => 'lightbox_title',
						'save_field'    => false,
						'render_row_cb' => 'wbe_display_section_title',
					),
					array(
						'name'            => 'Article Title',
						'id'              => 'title_lb',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Authors',
						'id'              => 'authors_lb',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Abstract',
						'id'              => 'abstract_lb',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),
					array(
						'name'            => 'Links',
						'id'              => 'links_lb',
						'type'            => 'displayitem_wbe',
						'default'         => '',
						'sanitization_cb' => 'wbe_sanitize_display_settings',
					),


					array(
						'name'          => 'Highlighting Settings',
						'desc'          => '',
						'type'          => 'title',
						'id'            => 'highlights_title',
						'save_field'    => false,
						'render_row_cb' => 'wbe_display_section_title',
					),
					array(
						'name'    => __( 'Highlight Keywords', 'wbe' ),
						'desc'    => __( "<div {$style}>Comma separated list of words to emphasize in the output.<br>Example: Smith JA, protease</div>", 'wbe' ),
						'id'      => 'highlights',
						'type'    => 'text',
						'default' => '',
					),
					array(
						'name'             => __( 'Highlight Type', 'wbe' ),
						'desc'             => "<div {$style}>If Highlight Keywords are specified, use the selected style.<br>Default: Bold</div>",
						'id'               => 'highlights_type',
						'type'             => 'select',
						'show_option_none' => false,
						'default'          => 'bold',
						'options'          => array(
							'bold'      => __( 'Bold', 'wbe' ),
							'italic'    => __( 'Italic', 'wbe' ),
							'underline' => __( 'Underline', 'wbe' ),
						),
					),
					array(
						'name'         => __( 'Clear All Display Settings', 'wbe' ),
						'desc'         => __( 'Reset custom values on the next save.', 'wbe' ),
						'id'           => 'clear',
						'type'         => 'checkbox',
						'before_field' => '<div style="padding-top: 8px;"></div>',
					),
				)
			);

			$this->option_metabox[] = array(
				'id'         => 'wbe_builder',
				'title'      => 'Builder',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( 'wbe_builder' ), ),
				'show_names' => false,
				'fields'     => array(
					array(
						'name'    => __( 'Default Query', 'wbe' ),
						'desc'    => '',
						'id'      => 'test',
						'type'    => 'buildertype_wbe',
						'default' => 'None',
					),
				)
			);

			$this->option_metabox[] = array(
				'id'         => 'wbe_shortcodes',
				'title'      => 'Shortcodes',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( 'wbe_shortcodes' ), ),
				'show_names' => false,
				'fields'     => array(
					array(
						'name'    => __( 'Default Query', 'wbe' ),
						'desc'    => '',
						'id'      => 'test',
						'type'    => 'shortcode_fieldtype_wbe',
						'default' => 'None',
					),
				)
			);

			$this->option_metabox[] = array(
				'id'         => 'wbe_developer',
				'title'      => 'Developer',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( 'wbe_developer' ), ),
				'show_names' => true,
			);


			return $this->option_metabox;
		}

	}
}

/**
 * Helper function to get/return the WBE_Settings object
 * @since  1.0.0
 * @return WBE_Settings object
 */
function wbe_settings() {
	return WBE_Settings::get_instance();
}

// Instantiate the class and create singleton
wbe_settings();

