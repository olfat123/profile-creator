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
	 * Validation errors.
	 *
	 * @var array
	 */
	private $errors = array();

	/**
	 * Submitted form data.
	 *
	 * @var array
	 */
	private $submitted_data = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Enqueue scripts and styles for the form.
	 */
	public function enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_script( 'jquery' );
		// Bootstrap CSS and JS.
		wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0' );
		wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '5.3.0', true );
		// Select2.
		wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', array( 'jquery' ), '4.0.13', true );
		wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', array(), '4.0.13' );
		// Custom script.
		wp_enqueue_script( 'cpc-consultant-form', PROFILE_CREATOR_PLUGIN_DIR_URL . '/assets/js/consultant-form.js', array( 'jquery', 'bootstrap' ), '1.0', true );
		wp_enqueue_style( 'cpc-custom', PROFILE_CREATOR_PLUGIN_DIR_URL . '/assets/css/style.css' );
	}

    /**
	 * Get the form type.
	 *
	 * @return string The type of the form.
	 */
	public function get_type() {
		return $this->type;
	}
    
	/**
	 * Render the consultant form.
	 *
	 * @param array $errors         Validation errors to display.
	 * @param array $submitted_data Submitted form data to repopulate fields.
	 * @return string HTML form markup.
	 */
	public function render_form( array $errors = array(), array $submitted_data = array() ): string {

        $errors_to_use         = ! empty( $this->errors ) ? $this->errors : $errors;
		$submitted_data_to_use = ! empty( $this->submitted_data ) ? $this->submitted_data : $submitted_data;

		$this->errors         = $errors_to_use;
		$this->submitted_data = $submitted_data_to_use;
		
		if ( is_user_logged_in() ) {
			return '<div class="alert alert-info">' . esc_html__( 'You already have an account.', 'profile-creator' ) . '</div>';
		}

		// Start output buffering and include the template.
		ob_start();
		include PROFILE_CREATOR_PLUGIN_DIR_PATH . '/templates/consultant-form-template.php';
		return ob_get_clean();
	}

	/**
	 * Render service checkboxes with hierarchical structure.
	 */
	private function render_service_checkboxes() {
		$services             = $this->get_hierarchical_services();
		$selected_services    = ( array ) ( $this->submitted_data['cpc_services'] ?? array() );
		$selected_subservices = ( array ) ( $this->submitted_data['cpc_subservices'] ?? array() );

		foreach ( $services as $service ) {
			$collapse_id = "service-collapse-{$service['id']}";
			?>
			<div class="form-check">
				<?php if ( ! empty( $service['children'] ) ) : ?>
					<button class="btn p-0 me-2 toggle-btn" 
							type="button" 
							data-bs-toggle="collapse" 
							data-bs-target="#<?php echo esc_attr( $collapse_id ); ?>" 
							aria-expanded="false" 
							aria-controls="<?php echo esc_attr( $collapse_id ); ?>">
						<span class="toggle-icon">+</span>
					</button>
				<?php endif; ?>
				<input class="form-check-input service-checkbox" 
					type="checkbox" 
					name="cpc_services[]" 
					value="<?php echo esc_attr( $service['id'] ); ?>"
					id="service-<?php echo esc_attr( $service['id'] ); ?>"
					data-parent="<?php echo esc_attr( $service['parent_id'] ); ?>"
					<?php echo in_array( $service['id'], $selected_services ) ? 'checked' : ''; ?>>
				<label class="form-check-label" for="service-<?php echo esc_attr( $service['id'] ); ?>">
					<?php echo esc_html( $service['service'] ); ?>
				</label>
				<?php if ( ! empty( $service['children'] ) ) : ?>
					<div class="collapse ms-4 w-100" id="<?php echo esc_attr( $collapse_id ); ?>">
						<?php foreach ( $service['children'] as $child ) : ?>
							<div class="form-check">
								<input class="form-check-input service-checkbox" 
									type="checkbox" 
									name="cpc_subservices[]" 
									value="<?php echo esc_attr( $child['id'] ); ?>"
									id="service-<?php echo esc_attr( $child['id'] ); ?>"
									data-parent="<?php echo esc_attr( $child['parent_id'] ); ?>"
									<?php echo in_array( $child['id'], $selected_subservices ) ? 'checked' : ''; ?>>
								<label class="form-check-label" for="service-<?php echo esc_attr( $child['id'] ); ?>">
									<?php echo esc_html( $child['service'] ); ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Render sector checkboxes with hierarchical structure.
	 */
	private function render_sector_checkboxes() {
		$sectors             = $this->get_hierarchical_sectors();
		$selected_sectors    = ( array ) ( $this->submitted_data['cpc_sectors'] ?? array() );
		$selected_subsectors = ( array ) ( $this->submitted_data['cpc_subsectors'] ?? array() );

		foreach ( $sectors as $sector ) {
			$collapse_id = "sector-collapse-{$sector['ID']}";
			?>
			<div class="form-check">
				<?php if ( ! empty( $sector['children'] ) ) : ?>
					<button class="btn p-0 me-2 toggle-btn" 
							type="button" 
							data-bs-toggle="collapse" 
							data-bs-target="#<?php echo esc_attr( $collapse_id ); ?>" 
							aria-expanded="false" 
							aria-controls="<?php echo esc_attr( $collapse_id ); ?>">
						<span class="toggle-icon">+</span>
					</button>
				<?php else : ?>
					<span class="me-2 placeholder-icon"> </span>
				<?php endif; ?>
				<input class="form-check-input sector-checkbox me-2" 
					type="checkbox" 
					name="cpc_sectors[]" 
					value="<?php echo esc_attr( $sector['ID'] ); ?>"
					id="sector-<?php echo esc_attr( $sector['ID'] ); ?>"
					data-parent="0"
					<?php echo in_array( $sector['ID'], $selected_sectors ) ? 'checked' : ''; ?>>
				<label class="form-check-label" for="sector-<?php echo esc_attr( $sector['ID'] ); ?>">
					<?php echo esc_html( $sector['sector'] ); ?>
				</label>
				<?php if ( ! empty( $sector['children'] ) ) : ?>
					<div class="collapse ms-4 w-100" id="<?php echo esc_attr( $collapse_id ); ?>">
						<?php foreach ( $sector['children'] as $child ) : ?>
							<div class="form-check d-flex align-items-center">
								<span class="me-2 placeholder-icon"> </span>
								<input class="form-check-input sector-checkbox me-2" 
									type="checkbox" 
									name="cpc_subsectors[]" 
									value="<?php echo esc_attr( $child['ID'] ); ?>"
									id="sector-<?php echo esc_attr( $child['ID'] ); ?>"
									data-parent="<?php echo esc_attr( $child['sector_id'] ); ?>"
									<?php echo in_array( $child['ID'], $selected_subsectors ) ? 'checked' : ''; ?>>
								<label class="form-check-label" for="sector-<?php echo esc_attr( $child['ID'] ); ?>">
									<?php echo esc_html( $child['subsector'] ); ?>
								</label>
							</div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}

	/**
	 * Get hierarchical services from the database.
	 *
	 * @return array Hierarchical array of services.
	 */
	private function get_hierarchical_services(): array {
		global $wpdb;
		$table_name = $wpdb->prefix . 'services';

		$results = $wpdb->get_results( "SELECT id, service, parent_id FROM $table_name ORDER BY parent_id, service", ARRAY_A );

		$services = array();
		$lookup   = array();

		foreach ( $results as $service ) {
			$service['children'] = array();
			$lookup[ $service['id'] ] = $service;
		}

		foreach ( $lookup as $id => $service ) {
			if ( 0 == $service['parent_id'] ) {
				$services[] = &$lookup[ $id ];
			} elseif ( isset( $lookup[ $service['parent_id'] ] ) ) {
				$lookup[ $service['parent_id'] ]['children'][] = &$lookup[ $id ];
			}
		}

		return $services;
	}

	/**
	 * Get hierarchical sectors from the database.
	 *
	 * @return array Hierarchical array of sectors and subsectors.
	 */
	private function get_hierarchical_sectors(): array {
		global $wpdb;
		$sector_table    = $wpdb->prefix . 'sector';
		$subsector_table = $wpdb->prefix . 'subsector';

		$sectors    = $wpdb->get_results( "SELECT ID, sector FROM $sector_table ORDER BY sector", ARRAY_A );
		$subsectors = $wpdb->get_results( "SELECT ID, subsector, sector_id FROM $subsector_table ORDER BY subsector", ARRAY_A );

		$lookup = array();
		foreach ( $subsectors as $subsector ) {
			$lookup[ $subsector['sector_id'] ][] = $subsector;
		}

		$result = array();
		foreach ( $sectors as $sector ) {
			$sector['children'] = $lookup[ $sector['ID'] ] ?? array();
			$result[]           = $sector;
		}

		return $result;
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

        $user_id  = $this->user_creator->create_user( $name, $email, $bio );
        if ( is_wp_error( $user_id ) ) {
            $error_code = $user_id->get_error_code();
            $error_message = $user_id->get_error_message( $error_code );
            error_log( 'User creation failed: ' . $error_message );
            error_log('submission_data'. print_r($submitted_data, true));
            // Handle specific error codes
            $this->errors = array(); 

            switch ( $error_code ) {
                case 'existing_user_email':
                    $errors['cpc_email'] = __( 'This email is already registered.', 'profile-creator ');
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
            
            $this->errors = $errors;
            $this->submitted_data = $submitted_data;
            
            echo $this->render_form( $errors, $submitted_data );
            return;
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

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        wp_safe_redirect( get_permalink( $post_id ) );
        exit;
    }

	/**
	 * Validate form data.
	 *
	 * @return array Associative array of validation errors.
	 */
	private function validate_form(): array {
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
			'cpc_telephone'      => 'consultant-telephone',
			'cpc_mobile'         => 'consultant-mobile',
			'cpc_email'          => 'consultant-email',
			'cpc_linkedin'       => 'consultant-linkedin',
			'cpc_experience'     => 'consultant-experience',
			'cpc_languages'      => 'consultant-langs',
			'cpc_citizenship'    => 'consultant-citizenship',
			'cpc_gender'         => 'consultant-gender',
			'cpc_qualifications' => 'overview',
			'cpc_clients'        => 'consultant-partners',
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
	private function sanitize_education( array $education ): array {
		$sanitized = array();
		foreach ( $education as $entry ) {
			$sanitized[] = array(
				'school'     => sanitize_text_field( $entry['school'] ?? '' ),
				'degree'     => sanitize_text_field( $entry['degree'] ?? '' ),
				'field'      => sanitize_text_field( $entry['field'] ?? '' ),
				'start_date' => sanitize_text_field( $entry['start_date'] ?? '' ),
				'end_date'   => sanitize_text_field( $entry['end_date'] ?? '' ),
			);
		}
		return $sanitized;
	}

	/**
	 * Get list of languages.
	 *
	 * @return array Array of language names.
	 */
	private function get_languages(): array {
		return array(
			'English',
			'Mandarin Chinese',
			'Hindi',
			'Spanish',
			'French',
			'Arabic',
			'Bengali',
			'Russian',
			'Portuguese',
			'Indonesian',
			'Japanese',
			'German',
			'Turkish',
			'Korean',
		);
	}

	/**
	 * Get list of countries.
	 *
	 * @return array Array of country names.
	 */
	private function get_countries(): array {
		$countries         = get_option( 'control_panel_countries' );
		$exploded_countries = array_unique( explode( ', ', $countries ) );
		sort( $exploded_countries );
		return $exploded_countries;
	}
}