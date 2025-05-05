<?php
namespace ProfileCreator\PostType;

class PostTypeCreator implements PostTypeCreatorInterface {
    private $post_types = array(
        'consultant-entries' => 'Consultant Entries',
        'dip-entries'        => 'Dip Entries',
    );

    public function register_post_types(): void {
        add_action( 'init', function() {
            foreach ( $this->post_types as $slug => $label ) {
                register_post_type( $slug, array(
                    'public'        => true,
                    'label'         => __( $label, 'profile-creator' ),
                    'supports'      => array( 'title', 'editor', 'thumbnail' ),
                    'show_in_menu'  => true,
                    'menu_position' => 5,
                ) );
            }
        } );
    }
}