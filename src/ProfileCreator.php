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
     * @var PostTypeCreator|null
     */
    private $post_type_creator = null;

    /**
     * Form handlers for different profile types.
     *
     * @var array
     */
    private $form_handlers = array();

    /**
	 * Constructor.
	 * Optionally accepts dependencies for extensibility and testability.
	 *
	 * @param PostTypeCreator|null $post_type_creator Optional post type creator instance.
	 * @param array $form_handlers Optional array of form handlers.
	 */
    public function __construct( $post_type_creator = null, $form_handlers = array() ) {
        // $this->post_type_creator = $post_type_creator ? $post_type_creator : ( class_exists( 'ProfileCreator\\PostType\\PostTypeCreator' ) ? new PostTypeCreator() : null );
        // Allow dynamic registration of form handlers for extensibility
        if ( ! empty( $form_handlers ) ) {
            $this->form_handlers = $form_handlers;
        } else {
            if ( class_exists( 'ProfileCreator\\Form\\ConsultantFormHandler' ) ) {
                $this->form_handlers['consultant'] = new ConsultantFormHandler();
            }
            if ( class_exists( 'ProfileCreator\\Form\\DipFormHandler' ) ) {
                $this->form_handlers['dip'] = new DipFormHandler();
            }
        }
    }

    /**
	 * Initialize the plugin.
	 */
    public function init() {
        // $this->register_post_types();
        $this->setup_hooks();
    }

	/**
	 * Register custom post types.
	 */
	private function register_post_types() {
		//$this->post_type_creator->register_post_types();
	}
    /**
	 * Setup hooks for shortcodes and actions.
	 */
    private function setup_hooks() {
        foreach ( $this->form_handlers as $type => $handler ) {
            add_shortcode(
                'cpc_' . $type . '_form',
                function( $atts = array() ) use ( $handler, $type ) {
                    // Normalize $atts to an empty array if not provided.
                    $atts = is_array( $atts ) ? $atts : array();
                    $atts = shortcode_atts( array(), $atts, 'cpc_' . $type . '_form' );

                    // If form is submitted, process it and capture output.
                    if ( isset( $_POST['cpc_submit'], $_POST['cpc_type'] ) && sanitize_text_field( $_POST['cpc_type'] ) === $handler->get_type() ) {
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
            'pcp-styles',
            PROFILE_CREATOR_PLUGIN_DIR_URL . '/assets/css/style.css',
            array(),
            defined( 'PROFILE_CREATOR_PLUGIN_VERSION' ) ? PROFILE_CREATOR_PLUGIN_VERSION : null
        );
    }

    /**
	 * Get form handler by type.
	 *
	 * @param string $type Form type.
	 * @return object|null Form handler instance or null if not found.
	 */
    public function get_form_handler( $type ) {
        return isset( $this->form_handlers[ $type ] ) ? $this->form_handlers[ $type ] : null;
    }

    /**
	 * Allow external code to register a new form handler.
	 *
	 * @param string $type   Form type.
	 * @param object $handler Handler instance.
	 */
    public function register_form_handler( $type, $handler ) {
        $this->form_handlers[ $type ] = $handler;
    }
}