<?php
namespace ProfileCreator\Form;

/**
 * Class DipFormHandler
 *
 * Handles the creation of new DIP entries in the Profile Creator plugin.
 *
 * @package ProfileCreator\Form
 */
class DipFormHandler extends FormHandler {
    /**
	 * Form type identifier.
	 *
	 * @var string
	 */
    protected $type = 'implement';

    /**
     * Get the template path for the dip form.
     *
     * @return string Path to the form template file.
     */
    protected function get_template_path(): string {
        return defined( 'PROFILE_CREATOR_PLUGIN_DIR_PATH' ) ? PROFILE_CREATOR_PLUGIN_DIR_PATH . '/templates/dip-form-template.php' : '';
    }

    /**
     * Get the user meta key for submitted dip posts.
     *
     * @return string Meta key for storing submitted post IDs.
     */
    protected function get_submitted_posts_meta_key(): string {
        return 'dip_submitted_posts';
    }

    /**
	 * Process the submitted form data.
	 */
	public function process_form(): void {
		if ( ! isset( $_POST['cpc_submit'] ) ) {
			return;
		}

		if ( ! isset( $_POST['cpc_nonce'] ) || ! wp_verify_nonce( $_POST['cpc_nonce'], 'cpc_create_' . $this->type . '_profile' ) ) {
			return;
		}
		$this->errors = array();

		$errors         = $this->validate_form();
		$submitted_data = $_POST;

		if ( ! empty( $errors ) ) {
			$this->errors         = $errors;
			$this->submitted_data = $submitted_data;
			// Re-render the form with errors and submitted data.
			if ( ! headers_sent() ) {
				ob_start();
			}
			echo $this->render_form( $errors, $submitted_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		$name  = isset( $_POST['dpc_name'] ) ? sanitize_text_field( $_POST['dpc_name'] ) : '';
		$bio   = isset( $_POST['dpc_overview'] ) ? sanitize_textarea_field( $_POST['dpc_overview'] ) : '';
		
		if ( ! is_user_logged_in() ) {
			$email    = isset( $_POST['dpc_email'] ) ? sanitize_email( $_POST['dpc_email'] ) : '';
			$password = isset( $_POST['dpc_password'] ) ? sanitize_text_field( $_POST['dpc_password'] ) : '';

			$user_id = $this->user_creator->create_user( $name, $email, $bio, $password );
			if ( is_wp_error( $user_id ) ) {
				$error_code    = $user_id->get_error_code();
				$error_message = $user_id->get_error_message( $error_code );
				// Handle specific error codes
				$this->errors = array();

				switch ( $error_code ) {
					case 'existing_user_email':
						$errors['cpc_email'] = __( 'This email is already registered.', 'profile-creator' );
						break;
					case 'existing_user_login':
						$errors['user_creation'] = __( 'A user with a similar name already exists.', 'profile-creator' );
						break;
					case 'empty_user_login':
					case 'invalid_username':
						$errors['dpc_name'] = __( 'Invalid name provided.', 'profile-creator' );
						break;
					default:
						$errors['user_creation'] = $error_message; // Fallback for unexpected errors
						break;
				}

				$this->errors         = $errors;
				$this->submitted_data = $submitted_data;

				echo $this->render_form( $errors, $submitted_data );
				return;
			}
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
		} else {
			$user_id = get_current_user_id();
		}

		$this->errors = array();

		$photo_id  = $this->handle_file_upload( 'dpc_photo', $user_id );
		$photo_url = $photo_id ? wp_get_attachment_url( $photo_id ) : '';

		$cv_id  = $this->handle_file_upload( 'dpc_cv', $user_id );
		$cv_url = $cv_id ? wp_get_attachment_url( $cv_id ) : '';

		$post_id = wp_insert_post(
			array(
				'post_title'   => $name,
				'post_content' => wp_kses_post( isset( $_POST['cpc_qualifications'] ) ? $_POST['cpc_qualifications'] : '' ),
				'post_status'  => 'publish',
				'post_type'    => $this->type . '-entries',
				'post_author'  => $user_id,
			)
		);
		if ( is_wp_error( $post_id ) ) {
			$this->errors['post_creation'] = __( 'Failed to create post.', 'profile-creator' );
			echo $this->render_form( $this->errors, $submitted_data );
			return;
		}
		if ( $photo_id ) {
			set_post_thumbnail( $post_id, $photo_id );
		}

		update_post_meta( $post_id, 'dip_pdf', $cv_url );
		$this->save_meta_data( $user_id, $post_id );

		$create_consultant_email = get_option( 'createConsultEmail' );
		$current_user            = wp_get_current_user();

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: DARPE <info@darpe.me>' . "\r\n";
		$message  = sprintf( __( 'New DIP entry submitted: (%s)<br>Have a good day!', 'profile-creator' ), esc_html( $name ) );
		wp_mail( $create_consultant_email, __( 'New Consultant Submission', 'profile-creator' ), $message, $headers, '-f info@darpe.me' );
		if ( ! empty( $current_user->user_email ) ) {
			add_post_meta( $post_id, 'author_email', $current_user->user_email );
		}
		add_post_meta( $post_id, 'author_name', $current_user->display_name );
		add_user_meta( $user_id, $this->get_submitted_posts_meta_key(), $post_id );
		wp_safe_redirect( get_permalink( $post_id ) );
		exit;
	}

    /**
	 * Validate form data.
	 *
	 * @return array Associative array of validation errors.
	 */
	protected function validate_form(): array {
		$errors = array();

		if ( empty( $_POST['dpc_name'] ) ) {
			$errors['dpc_name'] = __( 'Name is required', 'profile-creator' );
		}
		// if ( empty( $_POST['cpc_email'] ) || ! is_email( $_POST['cpc_email'] ) ) {
		// 	$errors['cpc_email'] = __( 'Valid email is required', 'profile-creator' );
		// }

		if ( empty( $_POST['dpc_headquarters'] ) ) {
			$errors['dpc_headquarters'] = __( 'Citizenship is required', 'profile-creator' );
		}

		if ( empty( $_FILES['dpc_photo']['name'] ) ) {
			$errors['dpc_photo'] = __( 'Photo upload is required', 'profile-creator' );
		}

        if ( empty( $_FILES['dpc_cv']['name'] ) ) {
			$errors['dpc_cv'] = __( 'Company profile upload is required', 'profile-creator' );
		}

		if ( empty( $_POST['dpc_clients'] ) ) {
			$errors['dpc_clients'] = __( 'Clients worked with is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_services'] ) ) {
			$errors['cpc_services'] = __( 'At least one service must be selected', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_sectors'] ) ) {
			$errors['cpc_sectors'] = __( 'At least one sector must be selected', 'profile-creator' );
		}

		return $errors;
	}

	/**
	 * Save form meta data.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 */
	protected function save_meta_data( int $user_id, int $post_id ): void {
		$fields = array(
			'dpc_websites'     => 'general_website',
			'dpc_email'        => 'general_email',
			'dpc_telephone'    => 'general_telephone',
			'specific_contact' => 'specific_contacts_info',
			'dpc_headquarters' => 'dip-citizenship',
			'dpc_countries'    => 'development-partner-country',
			'dpc_overview'     => 'dip-submission-overview',
			'dpc_clients'      => 'projects',
			'cpc_services'     => 'development-partner-type-of-service',
			'cpc_subservices'  => 'development-partner-sub-service',
			'cpc_sectors'      => 'development-partner-sector',
			'cpc_subsectors'   => 'development-partner-sub-sector',
			'dpc_categories'   => 'development-partner-type',
		);

		foreach ( $fields as $key => $meta_key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = in_array( $key, array( 'dpc_overview', 'dpc_clients' ), true )
					? wp_kses_post( $_POST[ $key ] )
					: $_POST[ $key ];
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
}