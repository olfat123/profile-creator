<?php
namespace ProfileCreator\Form;

use ProfileCreator\User\UserCreator;

/**
 * Abstract class FormHandler
 *
 * Base class for form handlers in the Profile Creator plugin.
 * Provides common functionality for form handling and validation.
 *
 * @package ProfileCreator\Form
 */
abstract class FormHandler implements FormHandlerInterface {
    /**
     * Form type identifier.
     *
     * @var string
     */
    protected $type;

    /**
     * User creator instance.
     *
     * @var UserCreator
     */
    protected $user_creator;

    /**
     * Validation errors.
     *
     * @var array
     */
    protected $errors = array();

    /**
     * Submitted form data.
     *
     * @var array
     */
    protected $submitted_data = array();

    /**
     * Submitted post ID.
     *
     * @var int|null
     */
    protected $submitted_post = null;

    /**
     * Constructor.
     */
    public function __construct() {
        $this->user_creator = new UserCreator();
        $this->init();
    }

    /**
     * Initialize hooks or other setup.
     */
    protected function init() {
        // Child classes can override to add hooks.
    }

    /**
     * Get the form type.
     *
     * @return string The type of the form.
     */
    public function get_type(): string {
        return $this->type;
    }

    /**
     * Get the template path for the form.
     *
     * @return string Path to the form template file.
     */
    abstract protected function get_template_path(): string;

    /**
     * Get the user meta key for submitted posts.
     *
     * @return string Meta key for storing submitted post IDs.
     */
    abstract protected function get_submitted_posts_meta_key(): string;

    /**
     * Validate form data.
     *
     * @return array Associative array of validation errors.
     */
    abstract protected function validate_form(): array;

    /**
     * Render the form.
     *
     * @param array $errors         Validation errors to display.
     * @param array $submitted_data Submitted form data to repopulate fields.
     * @return string HTML form markup.
     */
    public function render_form( $errors = array(), $submitted_data = array() ): string {
        // Enqueue scripts/styles only when form is rendered
        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
        wp_enqueue_style( 'bootstrap5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', array(), '5.3.0' );
        wp_enqueue_script( 'bootstrap5', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array( 'jquery' ), '5.3.0', true );
        wp_enqueue_script( 'cpc-consultant-form', PROFILE_CREATOR_PLUGIN_DIR_URL . '/assets/js/consultant-form.js', array( 'jquery', 'bootstrap' ), '1.0', true );
        wp_enqueue_style( 'cpc-custom', PROFILE_CREATOR_PLUGIN_DIR_URL . '/assets/css/style.css' );

        $errors_to_use         = ! empty( $this->errors ) ? $this->errors : $errors;
        $submitted_data_to_use = ! empty( $this->submitted_data ) ? $this->submitted_data : $submitted_data;

        $this->errors         = $errors_to_use;
        $this->submitted_data = $submitted_data_to_use;

        if ( is_user_logged_in() && empty( $submitted_data_to_use ) ) {
            $current_user = wp_get_current_user();
            $submitted_posts = get_user_meta( $current_user->ID, $this->get_submitted_posts_meta_key() );
            if ( is_array( $submitted_posts ) ) {
                $submitted_post = end( $submitted_posts );
            }
            if ( ! empty( $submitted_post ) && ! is_null( get_post( $submitted_post ) ) ) {
                $this->submitted_post = $submitted_post;
            }
        }

        // Start output buffering and include the template.
        ob_start();
        $template_path = $this->get_template_path();
        if ( file_exists( $template_path ) ) {
            include $template_path;
        } else {
            echo '<p>' . esc_html__( 'Error: Form template not found.', 'profile-creator' ) . '</p>';
        }
        return ob_get_clean();
    }

	/**
	 * Handle file uploads.
	 *
	 * @param string $field_name The name of the file input field.
	 * @param int    $user_id    The ID of the user uploading the file.
	 * @return int|null Attachment ID on success, null on failure.
	 */
	protected function handle_file_upload( string $field_name, int $user_id ): ?int {
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
     * Template method for processing the form.
     */
    public function process_form(): void {
        if ( ! $this->should_process_form() ) {
            return;
        }
        $this->errors = $this->validate_form();
        $this->submitted_data = $_POST;

        if ( ! empty( $this->errors ) ) {
            $this->handle_form_errors();
            return;
        }

        $user_id = $this->get_or_create_user();
        if ( is_wp_error( $user_id ) ) {
            $this->handle_user_error( $user_id );
            return;
        }

        $post_id = $this->create_or_update_post( $user_id );
        if ( is_wp_error( $post_id ) ) {
            $this->handle_post_error( $post_id );
            return;
        }

        $this->handle_file_uploads( $user_id, $post_id );
        $this->save_meta_data( $user_id, $post_id );
        $this->after_successful_submission( $user_id, $post_id );
    }

    /**
     * Check if the form should be processed.
     */
    protected function should_process_form() {
        return isset( $_POST['cpc_submit'] )
            && isset( $_POST['cpc_type'] )
            && $_POST['cpc_type'] === $this->type
            && isset( $_POST['cpc_nonce'] )
            && wp_verify_nonce( $_POST['cpc_nonce'], "cpc_create_{$this->type}_profile" );
    }

    /**
     * Handle form errors (default: re-render form).
     */
    protected function handle_form_errors() {
        echo $this->render_form( $this->errors, $this->submitted_data );
    }

    /**
     * Get or create the user (default: create new).
     */
    protected function get_or_create_user() {
        $name  = sanitize_text_field( $_POST['cpc_name'] );
        $email = sanitize_email( $_POST['cpc_email'] );
        $bio   = sanitize_textarea_field( isset( $_POST['cpc_bio'] ) ? $_POST['cpc_bio'] : '' );
        $password = isset( $_POST['cpc_password'] ) ? sanitize_text_field( $_POST['cpc_password'] ) : '';

        if ( ! is_user_logged_in() ) {
            return $this->user_creator->create_user( $name, $email, $bio, $password );
        }
        return get_current_user_id();
    }

    /**
     * Handle user creation errors.
     */
    protected function handle_user_error( $user_id ) {
        $this->errors['user_creation'] = $user_id->get_error_message();
        $this->handle_form_errors();
    }

    /**
     * Create or update the post for the user.
     * Subclasses can override get_post_title and get_post_content for custom fields.
     */
    protected function create_or_update_post( $user_id ) {
        $post_data = array(
            'post_title'   => $this->get_post_title(),
            'post_content' => $this->get_post_content(),
            'post_status'  => 'draft',
            'post_type'    => $this->type . '-entries',
            'post_author'  => $user_id,
        );

        $existing_post_id = null;
        $submitted_posts = get_user_meta( $user_id, $this->get_submitted_posts_meta_key() );
        if ( is_array( $submitted_posts ) && ! empty( $submitted_posts ) ) {
            $existing_post_id = end( $submitted_posts );
            if ( ! get_post( $existing_post_id ) ) {
                $existing_post_id = null;
            }
        }

        if ( $existing_post_id ) {
            $post_data['ID'] = $existing_post_id;
            $post_id = wp_update_post( $post_data );
        } else {
            $post_id = wp_insert_post( $post_data );
            add_user_meta( $user_id, $this->get_submitted_posts_meta_key(), $post_id );
        }

        return $post_id;
    }

    /**
     * Get the post title for the form submission. Subclasses can override.
     */
    protected function get_post_title() {
        return isset( $_POST['cpc_name'] ) ? sanitize_text_field( $_POST['cpc_name'] ) : '';
    }

    /**
     * Get the post content for the form submission. Subclasses can override.
     */
    protected function get_post_content() {
        return isset( $_POST['cpc_bio'] ) ? sanitize_textarea_field( $_POST['cpc_bio'] ) : '';
    }

    /**
     * Handle post creation errors.
     */
    protected function handle_post_error( $post_id ) {
        $this->errors['post_creation'] = $post_id->get_error_message();
        $this->handle_form_errors();
    }

    /**
     * Handle file uploads (default: do nothing).
     */
    protected function handle_file_uploads( $user_id, $post_id ) {
        // Subclasses can override if needed.
    }

    /**
     * Save meta data (default: do nothing).
     */
	protected function save_meta_data( int $user_id, int $post_id ): void {
        // Subclasses can override if needed.
    }

    /**
     * After successful submission (default: redirect).
     */
    protected function after_successful_submission( $user_id, $post_id ) {
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        wp_safe_redirect( get_permalink( '331671' ) );
        exit;
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
		$countries          = get_option( 'control_panel_countries' );
		$exploded_countries = array_unique( explode( ', ', $countries ) );
		sort( $exploded_countries );
		return $exploded_countries;
	}

    /**
	 * Get list of headquarters.
	 *
	 * @return array Array of country names.
	 */
	private function get_headquarters(): array {
		$countries          = get_option( 'control_panel_citizenship' );
		$exploded_countries = array_unique( explode( ', ', $countries ) );
		sort( $exploded_countries );
		return $exploded_countries;
	}

    /**
	 * Get list of categories.
	 *
	 * @return array Array of category names.
	 */
	private function get_dip_categories(): array {
		$countries          = get_option( 'control_panel_dip_categories' );
		$exploded_countries = array_unique( explode( ', ', $countries ) );
		sort( $exploded_countries );
		return $exploded_countries;
	}

    /**
     * Render service checkboxes with hierarchical structure.
     *
     * @param array $saved_services    Saved service IDs.
     * @param array $saved_subservices Saved subservice IDs.
     */
    protected function render_service_checkboxes( array $saved_services = array(), array $saved_subservices = array() ) {
        $services             = $this->get_hierarchical_services();
        $selected_services    = (array) ( $this->submitted_data['cpc_services'] ?? $saved_services );
        $selected_subservices = (array) ( $this->submitted_data['cpc_subservices'] ?? $saved_subservices );

        foreach ( $services as $service ) {
            $collapse_id = "service-collapse-{$service['id']}";
            ?>
            <div class="form-check">
                <?php if ( ! empty( $service['children'] ) ) : ?>
                    <button class="p-0 me-2 toggle-btn" 
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
     *
     * @param array $saved_sectors    Saved sector IDs.
     * @param array $saved_subsectors Saved subsector IDs.
     */
    protected function render_sector_checkboxes( array $saved_sectors = array(), array $saved_subsectors = array() ) {
        $sectors             = $this->get_hierarchical_sectors();
        $selected_sectors    = (array) ( $this->submitted_data['cpc_sectors'] ?? $saved_sectors );
        $selected_subsectors = (array) ( $this->submitted_data['cpc_subsectors'] ?? $saved_subsectors );

        foreach ( $sectors as $sector ) {
            $collapse_id = "sector-collapse-{$sector['ID']}";
            ?>
            <div class="form-check">
                <?php if ( ! empty( $sector['children'] ) ) : ?>
                    <button class="p-0 me-2 toggle-btn" 
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
    protected function get_hierarchical_services() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'services';

        $results = $wpdb->get_results( "SELECT id, service, parent_id FROM $table_name ORDER BY parent_id, service", ARRAY_A );

        $services = array();
        $lookup   = array();

        foreach ( $results as $service ) {
            $service['children']      = array();
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
    protected function get_hierarchical_sectors() {
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
}