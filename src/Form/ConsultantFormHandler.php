<?php
/**
 * Consultant Form Handler
 *
 * Handles the creation and processing of consultant profile forms.
 *
 * @package ProfileCreator\Form
 */

namespace ProfileCreator\Form;

/**
 * Class ConsultantFormHandler
 *
 * Extends FormHandler to manage consultant-specific form functionality.
 */
class ConsultantFormHandler extends FormHandler {
	/**
	 * Form type identifier.
	 *
	 * @var string
	 */
	protected $type = 'consultant';

	/**
	 * Get the template path for the consultant form.
	 *
	 * @return string Path to the form template file.
	 */
	protected function get_template_path(): string {
		return defined( 'PROFILE_CREATOR_PLUGIN_DIR_PATH' ) ? PROFILE_CREATOR_PLUGIN_DIR_PATH . '/templates/consultant-form-template.php' : '';
	}

	/**
	 * Get the user meta key for submitted consultant posts.
	 *
	 * @return string Meta key for storing submitted post IDs.
	 */
	protected function get_submitted_posts_meta_key(): string {
		return 'consultant_submitted_posts';
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

		$name     = isset( $_POST['cpc_name'] ) ? sanitize_text_field( $_POST['cpc_name'] ) : '';
		$email    = isset( $_POST['cpc_email'] ) ? sanitize_email( $_POST['cpc_email'] ) : '';
		$bio      = isset( $_POST['cpc_qualifications'] ) ? sanitize_textarea_field( $_POST['cpc_qualifications'] ) : '';
		$password = isset( $_POST['cpc_password'] ) ? sanitize_text_field( $_POST['cpc_password'] ) : '';

		if ( ! is_user_logged_in() ) {
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

		$photo_id  = $this->handle_file_upload( 'dpc_photo', $user_id );
		$photo_url = $photo_id ? wp_get_attachment_url( $photo_id ) : '';

		$cv_id  = $this->handle_file_upload( 'cpc_cv', $user_id );
		$cv_url = $cv_id ? wp_get_attachment_url( $cv_id ) : '';

		// Check if user has a consultant profile
		$submitted_posts = get_user_meta( $user_id, $this->get_submitted_posts_meta_key() );
		if ( is_array( $submitted_posts ) && ! empty( $submitted_posts ) ) {
			$existing_post_id = end( $submitted_posts );
			if ( ! get_post( $existing_post_id ) ) {
				$existing_post_id = null;
			}
		}

		$post_data = array(
			'post_title'   => $name,
			'post_content' => wp_kses_post( $_POST['cpc_qualifications'] ),
			'post_status'  => 'publish',
			'post_type'    => $this->type . '-entries',
			'post_author'  => $user_id,
		);

		if ( $existing_post_id ) {
			$post_data['ID'] = $existing_post_id;
			$post_id = wp_update_post( $post_data );
		} else {
			$post_id = wp_insert_post( $post_data );
			add_user_meta( $user_id, $this->get_submitted_posts_meta_key(), $post_id );
		}

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
			$this->errors['post_creation'] = __( 'There was an error creating the profile post.', 'profile-creator' );
			echo $this->render_form( $this->errors, $submitted_data );
			return;
		}

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
		$message  = sprintf( __( 'New Individual Consultant entry submitted: (%s)<br>Have a good day!', 'profile-creator' ), esc_html( $name ) );
		wp_mail( $create_consultant_email, __( 'New Consultant Submission', 'profile-creator' ), $message, $headers, '-f info@darpe.me' );
		if ( ! empty( $current_user->user_email ) ) {
			add_post_meta( $post_id, 'author_email', $current_user->user_email );
		}
		add_post_meta( $post_id, 'author_name', $current_user->display_name );

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

		if ( empty( $_POST['cpc_name'] ) ) {
			$errors['cpc_name'] = __( 'Name is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_email'] ) || ! is_email( $_POST['cpc_email'] ) ) {
			$errors['cpc_email'] = __( 'Valid email is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_experience'] ) || ! is_numeric( $_POST['cpc_experience'] ) || $_POST['cpc_experience'] < 0 ) {
			$errors['cpc_experience'] = __( 'Valid years of experience is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_languages'] ) ) {
			$errors['cpc_languages'] = __( 'Languages are required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_citizenship'] ) ) {
			$errors['cpc_citizenship'] = __( 'Citizenship is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_country_of_exp'] ) ) {
			$errors['cpc_country_of_exp'] = __( 'Country of experience is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_gender'] ) ) {
			$errors['cpc_gender'] = __( 'Gender is required', 'profile-creator' );
		}
		if ( empty( $_FILES['cpc_cv']['name'] ) ) {
			$errors['cpc_cv'] = __( 'CV upload is required', 'profile-creator' );
		}
		if ( empty( $_POST['cpc_clients'] ) ) {
			$errors['cpc_clients'] = __( 'Clients worked with is required', 'profile-creator' );
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
	 * Handle file uploads.
	 *
	 * @param string $field_name The name of the file input field.
	 * @param int    $user_id    The ID of the user uploading the file.
	 * @return int|null Attachment ID on success, null on failure.
	 */
	private function handle_file_upload( $field_name, $user_id ) {
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
	private function save_meta_data( $user_id, $post_id ) {
		$fields = array(
			'cpc_telephone'      => 'consultant-telephone',
			'cpc_mobile'         => 'consultant-mobile',
			'cpc_email'          => 'consultant-email',
			'cpc_linkedin'       => 'consultant-linkedin',
			'cpc_experience'     => 'consultant-experience',
			'cpc_languages'      => 'consultant-langs',
			'cpc_citizenship'    => 'consultant-citizenship',
			'cpc_gender'         => 'consultant-gender',
			'cpc_qualifications' => 'overview',
			'cpc_clients'        => 'partners',
			'cpc_education'      => 'education',
			'cpc_services'       => 'consultant-services',
			'cpc_subservices'    => 'consultant-sub-service',
			'cpc_sectors'        => 'consultant-sectors',
			'cpc_subsectors'     => 'consultant-sub-sectors',
			'cpc_country_of_exp' => 'consultant-working-country',
		);

		foreach ( $fields as $key => $meta_key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = in_array( $key, array( 'cpc_qualifications', 'cpc_clients' ), true )
					? wp_kses_post( $_POST[ $key ] )
					: ( 'cpc_education' === $key ? $this->sanitize_education( $_POST[ $key ] )
						: $_POST[ $key ] );
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}

	/**
	 * Sanitize education data.
	 *
	 * @param array $education Education data array.
	 * @return array Sanitized education data.
	 */
	private function sanitize_education( $education ) {
		$sanitized = array();
		if ( is_array( $education ) ) {
			foreach ( $education as $entry ) {
				$sanitized[] = array(
					'school'     => sanitize_text_field( isset( $entry['school'] ) ? $entry['school'] : '' ),
					'degree'     => sanitize_text_field( isset( $entry['degree'] ) ? $entry['degree'] : '' ),
					'field'      => sanitize_text_field( isset( $entry['field'] ) ? $entry['field'] : '' ),
					'start_date' => sanitize_text_field( isset( $entry['start_date'] ) ? $entry['start_date'] : '' ),
					'end_date'   => sanitize_text_field( isset( $entry['end_date'] ) ? $entry['end_date'] : '' ),
				);
			}
		}
		return $sanitized;
	}

}