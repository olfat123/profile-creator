<?php
namespace ProfileCreator;

use ProfileCreator\Form\ConsultantFormHandler;
use ProfileCreator\Form\DipFormHandler;
use ProfileCreator\Form\DapFormHandler;
use ProfileCreator\PostType\PostTypeCreator;

class ProfileCreator {
    private $post_type_creator;
    private $form_handlers = array();

    public function __construct() {
        $this->post_type_creator = new PostTypeCreator();
        $this->form_handlers = array(
            'consultant' => new ConsultantFormHandler(),
            'dip'        => new DipFormHandler(),
            'dap'        => new DapFormHandler(),
        );
    }

    public function init() {
        $this->register_post_types();
        $this->setup_hooks();
    }

    private function register_post_types() {
        $this->post_type_creator->register_post_types();
    }

    private function setup_hooks() {
        foreach ( $this->form_handlers as $type => $handler ) {
			// Use a closure to handle shortcode rendering and form processing.
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
			add_action( 'init', array( $handler, 'process_form' ) );
		}
        // foreach ( $this->form_handlers as $type => $handler ) {
        //     add_shortcode( "cpc_{$type}_form", array( $handler, 'render_form' ) );
        //     add_action( 'init', array( $handler, 'process_form' ) );
        // }
        // add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_nav_menu_items', array( $this, 'add_profile_menu_items' ), 10, 2 );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'cpc-styles',
            plugin_dir_url( __DIR__ ) . 'assets/css/style.css'
        );
    }

    public function add_profile_menu_items( $items, $args ) {
        if ( ! is_user_logged_in() ) {
            $types = array( 'consultant', 'dip', 'dap' );
            foreach ( $types as $type ) {
                $items .= '<li><a href="' . esc_url( home_url( "/create-{$type}-profile" ) ) . '">' 
                        . sprintf( __( 'Create %s Profile', 'consultant-profile-creator' ), ucfirst( $type ) ) 
                        . '</a></li>';
            }
        }
        return $items;
    }
}