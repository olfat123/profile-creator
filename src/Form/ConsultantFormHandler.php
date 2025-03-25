<?php
namespace ProfileCreator\Form;

use ProfileCreator\User\UserCreator;

class ConsultantFormHandler extends FormHandler {
    protected $type = 'consultant';

    public function __construct() {
        parent::__construct();
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    public function enqueue_scripts() {
        wp_enqueue_media();
        wp_enqueue_script( 'jquery' );
        // Bootstrap CSS and JS
        wp_enqueue_style( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css', [], '5.3.0' );
        wp_enqueue_script( 'bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', [ 'jquery' ], '5.3.0', true );
        // Select2
        wp_enqueue_script( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', [ 'jquery' ], '4.0.13', true );
        wp_enqueue_style( 'select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css', [], '4.0.13' );
        // Custom script
        wp_enqueue_script( 'cpc-consultant-form', plugin_dir_url( __DIR__ ) . '../assets/js/consultant-form.js', [ 'jquery', 'bootstrap' ], '1.0', true );
        wp_enqueue_style( 'cpc-custom', plugin_dir_url( __DIR__ ) . '../assets/css/style.css' );
    }

    public function render_form(): string {
        if ( is_user_logged_in() ) {
            return '<div class="alert alert-info">' . __( 'You already have an account.', 'profile-creator' ) . '</div>';
        }

        ob_start();
        ?>
        <form method="post" class="cpc-profile-form container" enctype="multipart/form-data">
            <?php wp_nonce_field( "cpc_create_{$this->type}_profile", 'cpc_nonce' ); ?>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cpc_name" class="form-label"><?php _e( 'Name', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="cpc_name" name="cpc_name" required>
                </div>
                <div class="col-md-6">
                    <label for="cpc_photo" class="form-label"><?php _e( 'Upload Your Photo', 'profile-creator' ); ?></label>
                    <input type="file" class="form-control" id="cpc_photo" name="cpc_photo" accept="image/*">
                </div>
            </div>

            <h3 class="mt-4 mb-3"><?php _e( 'Contact Information', 'profile-creator' ); ?></h3>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cpc_email" class="form-label"><?php _e( 'E-mail', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="cpc_email" name="cpc_email" required>
                </div>
                <div class="col-md-6">
                    <label for="cpc_telephone" class="form-label"><?php _e( 'Telephone', 'profile-creator' ); ?></label>
                    <input type="tel" class="form-control" id="cpc_telephone" name="cpc_telephone">
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cpc_mobile" class="form-label"><?php _e( 'Mobile', 'profile-creator' ); ?></label>
                    <input type="tel" class="form-control" id="cpc_mobile" name="cpc_mobile">
                </div>
                <div class="col-md-6">
                    <label for="cpc_linkedin" class="form-label"><?php _e( 'LinkedIn Profile', 'profile-creator' ); ?></label>
                    <input type="url" class="form-control" id="cpc_linkedin" name="cpc_linkedin">
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cpc_experience" class="form-label"><?php _e( 'Years of Experience', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="cpc_experience" name="cpc_experience" min="0" required>
                </div>
                <div class="col-md-6">
                    <label for="cpc_languages" class="form-label"><?php _e( 'Languages', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <select id="cpc_languages" name="cpc_languages[]" class="form-control cpc-select2" multiple required>
                        <?php foreach ( $this->get_languages() as $lang ) : ?>
                            <option value="<?php echo esc_attr( $lang ); ?>"><?php echo esc_html( $lang ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="cpc_citizenship" class="form-label"><?php _e( 'Citizenship', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <select id="cpc_citizenship" name="cpc_citizenship[]" class="form-control cpc-select2" multiple required>
                        <?php foreach ( $this->get_countries() as $country ) : ?>
                            <option value="<?php echo esc_attr( $country ); ?>"><?php echo esc_html( $country ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="cpc_country_of_exp" class="form-label"><?php _e( 'Country of experience', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <select id="cpc_country_of_exp" name="cpc_country_of_exp[]" class="form-control cpc-select2" multiple required>
                        <?php foreach ( $this->get_countries() as $country ) : ?>
                            <option value="<?php echo esc_attr( $country ); ?>"><?php echo esc_html( $country ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="cpc_gender" class="form-label"><?php _e( 'Gender', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <select id="cpc_gender" name="cpc_gender" class="form-select" required>
                        <option value=""><?php _e( 'Select Gender', 'profile-creator' ); ?></option>
                        <option value="male"><?php _e( 'Male', 'profile-creator' ); ?></option>
                        <option value="female"><?php _e( 'Female', 'profile-creator' ); ?></option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="cpc_cv" class="form-label"><?php _e( 'Upload Your CV', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="cpc_cv" name="cpc_cv" accept=".pdf,.doc,.docx" required>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-6">
                    <h3 class="mt-4 mb-3"><?php _e( 'Key Qualification Summary', 'profile-creator' ); ?></h3>
                    <div class="mb-3">
                        <?php wp_editor( '', 'cpc_qualifications', array( 'textarea_name' => 'cpc_qualifications', 'media_buttons' => false, 'textarea_rows' => 5 ) ); ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="mt-4 mb-3"><?php _e( 'Client(s) Worked With', 'profile-creator' ); ?></h3>
                    <div class="mb-3">
                        <?php wp_editor( '', 'cpc_clients', array( 'textarea_name' => 'cpc_clients', 'media_buttons' => false, 'textarea_rows' => 5 ) ); ?>
                    </div>
                </div>
            </div>

            <h3 class="mt-4 mb-3"><?php _e( 'Education', 'profile-creator' ); ?></h3>
            <div class="cpc-education-repeater card p-3 mb-3">
                <div class="education-entry mb-3">
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <label class="form-label"><?php _e( 'School', 'profile-creator' ); ?></label>
                            <input type="text" class="form-control" name="cpc_education[0][school]">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label"><?php _e( 'Degree', 'profile-creator' ); ?></label>
                            <input type="text" class="form-control" name="cpc_education[0][degree]">
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="form-label"><?php _e( 'Field of Study', 'profile-creator' ); ?></label>
                            <input type="text" class="form-control" name="cpc_education[0][field]">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label"><?php _e( 'Start Date', 'profile-creator' ); ?></label>
                            <input type="date" class="form-control" name="cpc_education[0][start_date]">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="form-label"><?php _e( 'End Date', 'profile-creator' ); ?></label>
                            <input type="date" class="form-control" name="cpc_education[0][end_date]">
                        </div>
                    </div>
                    <button type="button" class="btn btn-danger btn-sm remove-education mt-2"><?php _e( 'Remove', 'profile-creator' ); ?></button>
                </div>
            </div>
            <button type="button" class="btn btn-secondary add-education mb-3"><?php _e( 'Add Education', 'profile-creator' ); ?></button>

            <div class="row mb-3">
                <div class="col-md-6">
                <h3 class="mt-4 mb-3"><?php _e( 'Services', 'profile-creator' ); ?></h3>
                    <div class="mb-3 cpc-services card p-3">
                        <div class="cpc-parent-list">
                            <?php $this->render_service_checkboxes(); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h3 class="mt-4 mb-3"><?php _e( 'Sectors', 'profile-creator' ); ?></h3>
                    <div class="mb-3 cpc-sectors card p-3">
                        <div class="cpc-parent-list">
                            <?php $this->render_sector_checkboxes(); ?>
                        </div>
                    </div>
                </div>
            </div>
            <input type="checkbox" class="form-check-input" required><?php echo get_option('dap_statement'); ?>

            <input type="hidden" name="cpc_type" value="<?php echo esc_attr( $this->type ); ?>">
            <button type="submit" name="cpc_submit" class="btn btn-primary"><?php printf( __( 'Create %s Profile', 'profile-creator' ), ucfirst( $this->type ) ); ?></button>
        </form>
        <?php
        return ob_get_clean();
    }

    private function render_service_checkboxes() {
        $services = $this->get_hierarchical_services();
        
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
                       data-parent="<?php echo esc_attr( $service['parent_id'] ); ?>">
                <label class="form-check-label" for="service-<?php echo esc_attr( $service['id'] ); ?>">
                    
                    <?php echo esc_html( $service['service'] ); ?>
                    
                </label>
                <?php if ( ! empty( $service['children'] ) ) : ?>
                    <div class="collapse ms-4 w-100" id="<?php echo esc_attr( $collapse_id ); ?>">
                        <?php foreach ( $service['children'] as $child ) : ?>
                            <div class="form-check">
                                <input class="form-check-input service-checkbox" 
                                       type="checkbox" 
                                       name="cpc_services[]" 
                                       value="<?php echo esc_attr( $child['id'] ); ?>"
                                       id="service-<?php echo esc_attr( $child['id'] ); ?>"
                                       data-parent="<?php echo esc_attr( $child['parent_id'] ); ?>">
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

    private function render_sector_checkboxes() {
        $sectors = $this->get_hierarchical_sectors();
        
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
                    <span class="me-2 placeholder-icon"> </span>
                <?php endif; ?>
                <input class="form-check-input sector-checkbox me-2" 
                       type="checkbox" 
                       name="cpc_sectors[]" 
                       value="<?php echo esc_attr( $sector['ID'] ); ?>"
                       id="sector-<?php echo esc_attr( $sector['ID'] ); ?>"
                       data-parent="0">
                <label class="form-check-label" for="sector-<?php echo esc_attr( $sector['ID'] ); ?>">
                    <?php echo esc_html( $sector['sector'] ); ?>
                </label>
                <?php if ( ! empty( $sector['children'] ) ) : ?>
                    <div class="collapse ms-4 w-100" id="<?php echo esc_attr( $collapse_id ); ?>">
                        <?php foreach ( $sector['children'] as $child ) : ?>
                            <div class="form-check d-flex align-items-center">
                                <span class="me-2 placeholder-icon"> </span>
                                <input class="form-check-input sector-checkbox me-2" 
                                       type="checkbox" 
                                       name="cpc_sectors[]" 
                                       value="<?php echo esc_attr( $child['ID'] ); ?>"
                                       id="sector-<?php echo esc_attr( $child['ID'] ); ?>"
                                       data-parent="<?php echo esc_attr( $child['sector_id'] ); ?>">
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

    private function get_hierarchical_services(): array {
        global $wpdb;
        $table_name = $wpdb->prefix . 'services';
        
        $results = $wpdb->get_results( "SELECT id, service, parent_id FROM $table_name ORDER BY parent_id, service", ARRAY_A );
        
        $services = [];
        $lookup = [];
        
        foreach ( $results as $service ) {
            $service['children'] = [];
            $lookup[$service['id']] = $service;
        }
        
        foreach ( $lookup as $id => $service ) {
            if ( $service['parent_id'] == 0 ) {
                $services[] = &$lookup[$id];
            } else if ( isset( $lookup[$service['parent_id']] ) ) {
                $lookup[$service['parent_id']]['children'][] = &$lookup[$id];
            }
        }
        
        return $services;
    }

    private function get_hierarchical_sectors(): array {
        global $wpdb;
        $sector_table = $wpdb->prefix . 'sector';
        $subsector_table = $wpdb->prefix . 'subsector';

        $sectors = $wpdb->get_results( "SELECT ID, sector FROM $sector_table ORDER BY sector", ARRAY_A );
        $subsectors = $wpdb->get_results( "SELECT ID, subsector, sector_id FROM $subsector_table ORDER BY subsector", ARRAY_A );

        $lookup = [];
        foreach ( $subsectors as $subsector ) {
            $lookup[$subsector['sector_id']][] = $subsector;
        }

        $result = [];
        foreach ( $sectors as $sector ) {
            $sector['children'] = $lookup[$sector['ID']] ?? [];
            $result[] = $sector;
        }

        return $result;
    }

    public function process_form(): void {
        if ( ! isset( $_POST['cpc_submit'] ) || 
             ! isset( $_POST['cpc_type'] ) || 
             $_POST['cpc_type'] !== $this->type ||
             ! wp_verify_nonce( $_POST['cpc_nonce'], "cpc_create_{$this->type}_profile" ) ) {
            return;
        }

        $errors = $this->validate_form();
        if ( ! empty( $errors ) ) {
            wp_die( implode( '<br>', $errors ) );
        }

        $name = sanitize_text_field( $_POST['cpc_name'] );
        $email = sanitize_email( $_POST['cpc_email'] );
        $bio = sanitize_textarea_field( $_POST['cpc_qualifications'] );

        $user_id = $this->user_creator->create_user( $name, $email, $bio );
        $photo_id = $this->handle_file_upload( 'cpc_photo', $user_id );
        $cv_id = $this->handle_file_upload( 'cpc_cv', $user_id );

        $post_id = wp_insert_post([
            'post_title' => $name,
            'post_content' => wp_kses_post( $_POST['cpc_qualifications'] ),
            'post_status' => 'publish',
            'post_type' => "{$this->type}-entries",
            'post_author' => $user_id,
        ]);

        if ( $photo_id ) {
            set_post_thumbnail( $post_id, $photo_id );
        }

        $this->save_meta_data( $user_id, $post_id );

        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );
        wp_safe_redirect( get_permalink( $post_id ) );
        exit;
    }

    private function validate_form(): array {
        $errors = [];
        
        if ( empty( $_POST['cpc_name'] ) ) $errors[] = __( 'Name is required', 'profile-creator' );
        if ( empty( $_POST['cpc_email'] ) || ! is_email( $_POST['cpc_email'] ) ) $errors[] = __( 'Valid email is required', 'profile-creator' );
        if ( empty( $_POST['cpc_experience'] ) || ! is_numeric( $_POST['cpc_experience'] ) || $_POST['cpc_experience'] < 0 ) $errors[] = __( 'Valid years of experience is required', 'profile-creator' );
        if ( empty( $_POST['cpc_languages'] ) ) $errors[] = __( 'Languages are required', 'profile-creator' );
        if ( empty( $_POST['cpc_citizenship'] ) ) $errors[] = __( 'Citizenship is required', 'profile-creator' );
        if ( empty( $_POST['cpc_country_of_exp'] ) ) $errors[] = __( 'Country of experience is required', 'profile-creator' );
        if ( empty( $_POST['cpc_gender'] ) ) $errors[] = __( 'Gender is required', 'profile-creator' );
        if ( empty( $_FILES['cpc_cv']['name'] ) ) $errors[] = __( 'CV upload is required', 'profile-creator' );
        if ( empty( $_POST['cpc_clients'] ) ) $errors[] = __( 'Clients worked with is required', 'profile-creator' );
        if ( empty( $_POST['cpc_services'] ) ) $errors[] = __( 'At least one service must be selected', 'profile-creator' );
        if ( empty( $_POST['cpc_sectors'] ) ) $errors[] = __( 'At least one sector must be selected', 'profile-creator' );
        return $errors;
    }

    private function handle_file_upload( string $field_name, int $user_id ): ?int {
        if ( ! empty( $_FILES[ $field_name ]['name'] ) ) {
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            $upload = wp_handle_upload( $_FILES[ $field_name ], [ 'test_form' => false ] );
            
            if ( $upload && ! isset( $upload['error'] ) ) {
                $attachment = array(
                    'post_mime_type' => $upload['type'],
                    'post_title'     => sanitize_file_name( $_FILES[ $field_name ]['name'] ),
                    'post_content'   => '',
                    'post_status'    => 'inherit',
                    'post_author'    => $user_id,
                );
                
                $attach_id = wp_insert_attachment( $attachment, $upload['file'] );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
                $attach_data = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
                wp_update_attachment_metadata( $attach_id, $attach_data );
                
                return $attach_id;
            }
        }
        return null;
    }

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
            'cpc_subservices'    => 'consultant-sub-services',
            'cpc_sectors'        => 'consultant-sectors',
            'cpc_subsectors'     => 'consultant-sub-sectors',
            'cpc_country_of_exp' => 'consultant-working-country'
        );

        foreach ( $fields as $key => $meta_key ) {
            if ( isset( $_POST[ $key ] ) ) {
                $value = $key === 'cpc_qualifications' || $key === 'cpc_clients' 
                    ? wp_kses_post( $_POST[$key] )
                    : ( $key === 'cpc_education' ? $this->sanitize_education( $_POST[ $key ] ) 
                        : ( $key === 'cpc_services' ? array_map( 'intval', $_POST[ $key ] ) 
                            : sanitize_text_field( $_POST[ $key ] ) ) );
                update_post_meta( $post_id, "{$meta_key}", $value );
            }
        }

        if ( $cv_id = get_post_meta( $post_id, 'cpc_cv_id', true ) ) {
            update_post_meta( $post_id, 'cpc_cv_id', $cv_id );
        }
    }

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

    private function get_languages(): array {
        return array( 'English', 'Mandarin Chinese', 'Hindi', 'Spanish', 'French', 'Arabic', 'Bengali', 'Russian', 'Portuguese', 'Indonesian',
        'Japanese', 'German', 'Turkish', 'Korean' );
    }

    private function get_countries(): array {
        $countries = get_option( 'control_panel_countries' );
        $exploded_countries = array_unique( explode( ', ', $countries ) );
        sort( $exploded_countries );
        return $exploded_countries;
    }
}