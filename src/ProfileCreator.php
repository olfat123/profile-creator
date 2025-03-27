<?php
/**
 * Profile Creator Plugin Main Class
 *
 * Initializes and manages the profile creation functionality.
 *
 * @package ProfileCreator
 */

namespace ProfileCreator;

use ProfileCreator\Form\ConsultantFormHandler;
use ProfileCreator\Form\DipFormHandler;
use ProfileCreator\Form\DapFormHandler;
use ProfileCreator\PostType\PostTypeCreator;

/**
 * Class ProfileCreator
 *
 * Main class for the Profile Creator plugin.
 */
class ProfileCreator {
	/**
	 * Post type creator instance.
	 *
	 * @var PostTypeCreator
	 */
	private $post_type_creator;

	/**
	 * Form handlers for different profile types.
	 *
	 * @var array
	 */
	private $form_handlers = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->post_type_creator = new PostTypeCreator();
		$this->form_handlers     = array(
			'consultant' => new ConsultantFormHandler(),
			'dip'        => new DipFormHandler(),
			'dap'        => new DapFormHandler(),
		);
	}

	/**
	 * Initialize the plugin.
	 */
	public function init() {
		$this->register_post_types();
		$this->setup_hooks();
	}

	/**
	 * Register custom post types.
	 */
	private function register_post_types() {
		$this->post_type_creator->register_post_types();
	}

	/**
	 * Setup hooks for shortcodes and actions.
	 */
	private function setup_hooks() {
		foreach ( $this->form_handlers as $type => $handler ) {
            add_shortcode(
				"cpc_{$type}_form",
				function ( $atts ) use ( $handler, $type ) {
					// Normalize $atts to an empty array if not provided.
					$atts = shortcode_atts( array(), $atts, "cpc_{$type}_form" );

					// If form is submitted, process it and capture output.
					if ( isset( $_POST['cpc_submit'] ) && isset( $_POST['cpc_type'] ) && $_POST['cpc_type'] === $handler->get_type() ) {
						ob_start();
						$handler->process_form();
						return ob_get_clean();
					}

					// Otherwise, render the form with empty arrays for errors and submitted data.
					return $handler->render_form( array(), array() );
				}
			);
        }
	}

	/**
	 * Enqueue plugin assets.
	 */
	public function enqueue_assets() {
		wp_enqueue_style(
			'cpc-styles',
			plugin_dir_url( __DIR__ ) . 'assets/css/style.css',
			array(),
			'1.0'
		);
	}

	/**
	 * Add profile creation links to the navigation menu.
	 *
	 * @param string   $items Menu items HTML.
	 * @param stdClass $args  Menu arguments.
	 * @return string Updated menu items HTML.
	 */
	public function add_profile_menu_items( $items, $args ) {
		if ( ! is_user_logged_in() ) {
			$types = array( 'consultant', 'dip', 'dap' );
			foreach ( $types as $type ) {
				$items .= '<li><a href="' . esc_url( home_url( "/create-{$type}-profile" ) ) . '">' 
						. esc_html( sprintf( __( 'Create %s Profile', 'consultant-profile-creator' ), ucfirst( $type ) ) ) 
						. '</a></li>';
			}
		}
		return $items;
	}

	/**
	 * Get form handler by type.
	 *
	 * @param string $type Form type.
	 * @return mixed Form handler instance or null if not found.
	 */
	public function get_form_handler( $type ) {
		return $this->form_handlers[ $type ] ?? null;
	}
}