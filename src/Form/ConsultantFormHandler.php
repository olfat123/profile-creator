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
	 * Handle file uploads for consultant form.
	 */
	protected function handle_file_uploads( $user_id, $post_id ) {
		$photo_id = $this->handle_file_upload( 'dpc_photo', $user_id );
		$cv_id    = $this->handle_file_upload( 'cpc_cv', $user_id );
		if ( $photo_id ) {
			set_post_thumbnail( $post_id, $photo_id );
			update_post_meta( $post_id, 'consult_photo', wp_get_attachment_url( $photo_id ) );
		}
		if ( $cv_id ) {
			update_post_meta( $post_id, 'consult_cv', wp_get_attachment_url( $cv_id ) );
		}
	}

	/**
	 * Save consultant-specific meta data.
	 */
	protected function save_meta_data( int $user_id, int $post_id ): void {
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
	 * After successful submission: send emails and add post meta, then redirect.
	 */
	protected function after_successful_submission( $user_id, $post_id ) {
		$create_consultant_email = get_option( 'createConsultEmail' );
		$current_user            = wp_get_current_user();

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: DARPE <info@darpe.me>' . "\r\n";
		$message  = sprintf( __( 'New Individual Consultant entry submitted: (%s)<br>Have a good day!', 'profile-creator' ), esc_html( get_the_title( $post_id ) ) );
		wp_mail( $create_consultant_email, __( 'New Consultant Submission', 'profile-creator' ), $message, $headers, '-f info@darpe.me' );
		if ( ! empty( $current_user->user_email ) ) {
			add_post_meta( $post_id, 'author_email', $current_user->user_email );
		}
		add_post_meta( $post_id, 'author_name', $current_user->display_name );
		parent::after_successful_submission( $user_id, $post_id );
	}

	/**
	 * Get the post content for the consultant form (uses cpc_qualifications).
	 */
	protected function get_post_content() {
		return isset( $_POST['cpc_qualifications'] ) ? wp_kses_post( $_POST['cpc_qualifications'] ) : '';
	}

	/**
	 * Get or create the user, handling consultant-specific fields.
	 */
	protected function get_or_create_user() {
		$name     = isset( $_POST['cpc_name'] ) ? sanitize_text_field( $_POST['cpc_name'] ) : '';
		$email    = isset( $_POST['cpc_email'] ) ? sanitize_email( $_POST['cpc_email'] ) : '';
		$bio      = isset( $_POST['cpc_qualifications'] ) ? sanitize_textarea_field( $_POST['cpc_qualifications'] ) : '';
		$password = isset( $_POST['cpc_password'] ) ? sanitize_text_field( $_POST['cpc_password'] ) : '';

		if ( ! is_user_logged_in() ) {
			$user_id = $this->user_creator->create_user( $name, $email, $bio, $password );
			if ( is_wp_error( $user_id ) ) {
				$error_code    = $user_id->get_error_code();
				$error_message = $user_id->get_error_message( $error_code );
				$errors = array();
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
						$errors['user_creation'] = $error_message;
						break;
				}
				$this->errors         = $errors;
				$this->submitted_data = $_POST;
				$this->handle_form_errors();
				return $user_id;
			}
			wp_set_current_user( $user_id );
			wp_set_auth_cookie( $user_id );
			return $user_id;
		}
		return get_current_user_id();
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