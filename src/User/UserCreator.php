<?php
namespace ProfileCreator\User;

/**
 * Class UserCreator
 *
 * Handles the creation of new WordPress users for the Profile Creator plugin.
 * Ensures unique usernames and updates user meta with biography information.
 *
 * @package ProfileCreator\User
 */
class UserCreator implements UserCreatorInterface {
    /**
     * Create a new user.
     *
     * @param string $name     User's full name.
     * @param string $email    User's email address.
     * @param string $bio      User's biography.
     * @param string $password User's password.
     *
     * @return int|WP_Error User ID on success, WP_Error on failure.
     */
    public function create_user( string $name, string $email, string $bio, string $password ) {
        $username = $this->generate_username( $name );        
        $user_id  = wp_create_user( $username, $password, $email );

        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        update_user_meta( $user_id, 'description', $bio );

        return $user_id;
    }

    /**
     * Generate a unique username based on the user's name.
     *
     * @param string $name User's full name.
     * @return string Unique username.
     */
    private function generate_username( string $name ): string  {
        $username      = sanitize_user( str_replace( ' ', '', strtolower( $name ) ) );
        $base_username = $username;
        $counter       = 1;
        $max_attempts  = 100;

        while ( username_exists( $username ) && $counter <= $max_attempts ) {
            $username = $base_username . $counter;
            $counter++;
        }

        // Fallback if too many attempts
        if ( username_exists( $username ) ) {
            $username = $base_username . wp_rand( 1000, 9999 );
        }

        return $username;
    }
}