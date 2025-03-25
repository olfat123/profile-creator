<?php
namespace ProfileCreator\User;

class UserCreator implements UserCreatorInterface {
    public function create_user( string $name, string $email, string $bio ): int {
        $username = $this->generate_username( $name );
        $password = wp_generate_password( 12, true );
        
        $user_id = wp_create_user( $username, $password, $email );
        
        if ( is_wp_error( $user_id ) ) {
            wp_die( $user_id->get_error_message() );
        }

        update_user_meta( $user_id, 'description', $bio );
        
        return $user_id;
    }

    private function generate_username( string $name ): string {
        $username = sanitize_user( str_replace( ' ', '', strtolower( $name ) ) );
        $base_username = $username;
        $counter = 1;

        while ( username_exists( $username ) ) {
            $username = $base_username . $counter;
            $counter++;
        }

        return $username;
    }
}