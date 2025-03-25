<?php
namespace ProfileCreator\Form;

use ProfileCreator\User\UserCreator;

abstract class FormHandler implements FormHandlerInterface {
    protected $type;
    protected $user_creator;

    public function __construct() {
        $this->user_creator = new UserCreator();
    }

    public function render_form(): string {
        if ( is_user_logged_in() ) {
            return '<p>' . __( 'You already have an account.', 'profile-creator' ) . '</p>';
        }

        ob_start();
        ?>
        <form method="post" class="cpc-profile-form">
            <?php wp_nonce_field( "cpc_create_{$this->type}_profile", 'cpc_nonce' ); ?>
            <div class="form-group">
                <label for="cpc_name"><?php _e( 'Full Name', 'profile-creator' ); ?></label>
                <input type="text" id="cpc_name" name="cpc_name" required>
            </div>
            <div class="form-group">
                <label for="cpc_email"><?php _e( 'Email', 'profile-creator' ); ?></label>
                <input type="email" id="cpc_email" name="cpc_email" required>
            </div>
            <div class="form-group">
                <label for="cpc_bio"><?php _e( 'Bio', 'profile-creator' ); ?></label>
                <textarea id="cpc_bio" name="cpc_bio" required></textarea>
            </div>
            <input type="hidden" name="cpc_type" value="<?php echo esc_attr( $this->type ); ?>">
            <input type="submit" name="cpc_submit" value="<?php printf( __( 'Create %s Profile', 'profile-creator' ), ucfirst( $this->type ) ); ?>">
        </form>
        <?php
        return ob_get_clean();
    }

    public function process_form(): void {
        if ( ! isset( $_POST['cpc_submit'] ) || 
             ! isset( $_POST['cpc_type'] ) || 
             $_POST['cpc_type'] !== $this->type ||
             ! wp_verify_nonce( $_POST['cpc_nonce'], "cpc_create_{$this->type}_profile" ) ) {
            return;
        }

        $name = sanitize_text_field( $_POST['cpc_name'] );
        $email = sanitize_email( $_POST['cpc_email'] );
        $bio = sanitize_textarea_field( $_POST['cpc_bio'] );

        $user_id = $this->user_creator->create_user( $name, $email, $bio );

        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_content' => $bio,
            'post_status' => 'publish',
            'post_type' => "{$this->type}-entries",
            'post_author' => $user_id,
        ]);

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        wp_safe_redirect( get_permalink( $post_id ) );
        exit;
    }
}