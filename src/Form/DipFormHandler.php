<?php
namespace ProfileCreator\Form;

class DipFormHandler extends FormHandler {
    /**
	 * Form type identifier.
	 *
	 * @var string
	 */
    protected $type = 'dip';

    /**
     * Get the template path for the dip form.
     *
     * @return string Path to the form template file.
     */
    protected function get_template_path(): string {
        return PROFILE_CREATOR_PLUGIN_DIR_PATH . '/templates/dip-form-template.php';
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

		if ( ! wp_verify_nonce( $_POST['cpc_nonce'], "cpc_create_{$this->type}_profile" ) ) {
			return;
		}
		$this->errors = array();

		$errors         = $this->validate_form();
		$submitted_data = $_POST;

		if ( ! empty( $errors ) ) {
			$this->errors         = $errors;
			$this->submitted_data = $submitted_data;
			error_log( 'Validation errors found: ' . print_r( $errors, true ) );
			// Re-render the form with errors and submitted data.
			if ( ! headers_sent() ) {
				ob_start();
			}
			echo $this->render_form( $errors, $submitted_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			return;
		}

		$name  = sanitize_text_field( $_POST['cpc_name'] );
		$email = sanitize_email( $_POST['cpc_email'] );
		$bio   = sanitize_textarea_field( $_POST['cpc_qualifications'] );

		if ( ! is_user_logged_in() ) {
			$user_id = $this->user_creator->create_user( $name, $email, $bio );
			if ( is_wp_error( $user_id ) ) {
				$error_code    = $user_id->get_error_code();
				$error_message = $user_id->get_error_message( $error_code );
				// Handle specific error codes
				$this->errors = array();

				switch ( $error_code ) {
					case 'existing_user_email':
						$errors['cpc_email'] = __( 'This email is already registered.', 'profile-creator ' );
						break;
					case 'existing_user_login':
						$errors['user_creation'] = __( 'A user with a similar name already exists.', 'profile-creator' );
						break;
					case 'empty_user_login':
					case 'invalid_username':
						$errors['cpc_name'] = __( 'Invalid name provided.', 'profile-creator' );
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

		$photo_id  = $this->handle_file_upload( 'cpc_photo', $user_id );
		$photo_url = wp_get_attachment_url( $photo_id );

		$cv_id  = $this->handle_file_upload( 'cpc_cv', $user_id );
		$cv_url = wp_get_attachment_url( $cv_id );

		$post_id = wp_insert_post(
			array(
				'post_title'   => $name,
				'post_content' => wp_kses_post( $_POST['cpc_qualifications'] ),
				'post_status'  => 'publish',
				'post_type'    => "{$this->type}-entries",
				'post_author'  => $user_id,
			)
		);

		if ( $photo_id ) {
			set_post_thumbnail( $post_id, $photo_id );
		}

		update_post_meta( $post_id, 'consult_photo', $photo_url );
		update_post_meta( $post_id, 'consult_cv', $cv_url );
		$this->save_meta_data( $user_id, $post_id );

		$create_consultant_email = get_option( 'createConsultEmail' );
		$current_user            = wp_get_current_user();

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: DARPE <info@darpe.me>' . "\r\n";
		$message  = 'New Individual Consultant entry submitted: (' . $name . ')<br>' . 'Have a good day!';
		wp_mail( $create_consultant_email, 'New Consultant Submission', $message, $headers, '-f info@darpe.me' );
		// wp_mail( 'info@darpe.me', 'New Consultant Submission', $message, $headers, '-f info@darpe.me' );
		if ( ! ( empty( $current_user->user_email ) ) ) {
			add_post_meta( $post_id, 'author_email', $current_user->user_email );
		}
		add_post_meta( $post_id, 'author_name', $current_user->display_name );
		add_user_meta( $user_id, 'consultant_submitted_posts', $post_id );

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

		if ( empty( $_POST['cpc_citizenship'] ) ) {
			$errors['cpc_citizenship'] = __( 'Citizenship is required', 'profile-creator' );
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
		if ( empty( $_POST['dpc_services'] ) ) {
			$errors['dpc_services'] = __( 'At least one service must be selected', 'profile-creator' );
		}
		if ( empty( $_POST['dpc_sectors'] ) ) {
			$errors['dpc_sectors'] = __( 'At least one sector must be selected', 'profile-creator' );
		}

		return $errors;
	}

    	/**
	 * Handle file uploads.
	 *
	 * @param string $field_name The name of the file input field.
	 * @param int    $user_id    The ID of the user uploading the file.
	 * @return int|null Attachment ID on success, null on failure.
	 */
	private function handle_file_upload( string $field_name, int $user_id ): ?int {
		if ( ! empty( $_FILES[ $field_name ]['name'] ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			$upload = wp_handle_upload( $_FILES[ $field_name ], array( 'test_form' => false ) );

			if ( $upload && ! isset( $upload['error'] ) ) {
				$attachment = array(
					'post_mime_type' => $upload['type'],
					'post_title'     => sanitize_file_name( $_FILES[ $field_name ]['name'] ),
					'post_content'   => '',
					'post_status'    => 'inherit',
					'post_author'    => $user_id,
				);

				$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
				wp_update_attachment_metadata( $attach_id, $attach_data );

				return $attach_id;
			}
		}
		return null;
	}

	/**
	 * Save form meta data.
	 *
	 * @param int $user_id User ID.
	 * @param int $post_id Post ID.
	 */
	private function save_meta_data( int $user_id, int $post_id ): void {
		$fields = array(
			'dpc_websites'     => 'general_website',
			'dpc_email'        => 'general_email',
			'dpc_telephone'    => 'general_telephone',
			'specific_contact' => 'specific_contacts_info',
			'dpc_headquarters' => 'dip-citizenship',
			'dpc_countries'    => 'development-partner-country',
			'dpc_overview'     => 'dip-submission-overview',
			'dpc_clients'      => 'projects',
			'dpc_services'     => 'development-partner-type-of-service',
			'dpc_subservices'  => 'development-partner-sub-service',
			'dpc_sectors'      => 'development-partner-sector',
			'dpc_subsectors'   => 'development-partner-sub-sector',
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