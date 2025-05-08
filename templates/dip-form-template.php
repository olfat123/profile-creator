<?php
/**
 * DIP Form Template
 *
 * Template for rendering the dip profile creation form.
 *
 * @package ProfileCreator
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! empty( $dip_submitted_post ) && null !== $dip_submitted_post ) {
	$submitted_data[ 'cpc_name' ]           = get_the_title( $dip_submitted_post );
	$submitted_data[ 'cpc_email' ]          = get_post_meta( $dip_submitted_post, 'dip-email', true );
	$submitted_data[ 'cpc_telephone' ]      = get_post_meta( $dip_submitted_post, 'dip-telephone', true );
	$submitted_data[ 'cpc_mobile' ]         = get_post_meta( $dip_submitted_post, 'dip-mobile', true );
	$submitted_data[ 'cpc_linkedin' ]       = get_post_meta( $dip_submitted_post, 'dip-linkedin', true );
	$submitted_data[ 'cpc_experience' ]     = get_post_meta( $dip_submitted_post, 'dip-experience', true );
	$submitted_data[ 'cpc_languages' ]      = get_post_meta( $dip_submitted_post, 'dip-langs', true );
	$submitted_data[ 'cpc_citizenship' ]    = get_post_meta( $dip_submitted_post, 'dip-citizenship', true );
	$submitted_data[ 'cpc_country_of_exp' ] = get_post_meta( $dip_submitted_post, 'dip-working-country', true );
	$submitted_data[ 'cpc_gender' ]         = get_post_meta( $dip_submitted_post, 'dip-gender', true );
	$submitted_data[ 'cpc_qualifications' ] = get_post_meta( $dip_submitted_post, 'overview', true );
	$submitted_data[ 'cpc_clients' ]        = get_post_meta( $dip_submitted_post, 'partners', true );
	$submitted_data[ 'cpc_education' ]      = get_post_meta( $dip_submitted_post, 'education', true );
	$submitted_data[ 'cpc_services' ]       = get_post_meta( $dip_submitted_post, 'dip-services', true );
	$submitted_data[ 'cpc_subservices' ]    = get_post_meta( $dip_submitted_post, 'dip-sub-service', true );
	$submitted_data[ 'cpc_sectors' ]        = get_post_meta( $dip_submitted_post, 'dip-sectors', true );
	$submitted_data[ 'cpc_subsectors' ]     = get_post_meta( $dip_submitted_post, 'dip-sub-sectors', true );

}

?>

<?php if ( ! empty( $errors ) ) : ?>
    <div class="alert alert-danger"><?php echo esc_html( print_r( $errors, true ) ); ?></div>
<?php endif; ?>
<form method="post" class="cpc-profile-form container" enctype="multipart/form-data">
	<?php wp_nonce_field( 'cpc_create_dip_profile', 'cpc_nonce' ); ?>
	
	<div class="row mb-3">
		<div class="col-md-6">
			<label for="cpc_name" class="form-label"><?php esc_html_e( 'Name', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<input type="text" class="form-control <?php echo isset( $errors['cpc_name'] ) ? 'is-invalid' : ''; ?>" 
				id="cpc_name" name="cpc_name" value="<?php echo esc_attr( $submitted_data['cpc_name'] ?? '' ); ?>" required>
			<?php if ( isset( $errors['cpc_name'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_name'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_photo" class="form-label"><?php esc_html_e( 'Upload Your Photo', 'profile-creator' ); ?></label>
			<input type="file" class="form-control" id="cpc_photo" name="cpc_photo" accept="image/*">
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Key Qualification Summary', 'profile-creator' ); ?></h3>
			<div class="mb-3">
				<?php
				wp_editor(
					$submitted_data['cpc_qualifications'] ?? '',
					'cpc_qualifications',
					array(
						'textarea_name' => 'cpc_qualifications',
						'media_buttons' => false,
						'textarea_rows' => 5,
					)
				);
				?>
			</div>
		</div>
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Partner(s) Worked With', 'profile-creator' ); ?></h3>
			<div class="mb-3 <?php echo isset( $errors['cpc_clients'] ) ? 'is-invalid' : ''; ?>">
				<?php
				wp_editor(
					$submitted_data['cpc_clients'] ?? '',
					'cpc_clients',
					array(
						'textarea_name' => 'cpc_clients',
						'media_buttons' => false,
						'textarea_rows' => 5,
					)
				);
				?>
				<?php if ( isset( $errors['cpc_clients'] ) ) : ?>
					<div class="invalid-feedback d-block"><?php echo esc_html( $errors['cpc_clients'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'General Contact Information', 'profile-creator' ); ?></h3>
			<h4 class="mt-4 mb-3"><?php esc_html_e( 'Websites', 'profile-creator' ); ?></h4>
			<div class="cpc-website-repeater card p-3 mb-3">
				<?php
				$websites = $submitted_data['dip_website'] ?? array( '' );
				foreach ( $websites as $index => $website ) :
				?>
				<div class="website-entry mb-3">
					<div class="row">
						<div class="col-md-10">
							<input type="url" class="form-control" name="dip_website[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $website ); ?>" placeholder="Website">
						</div>
						<div class="col-md-2 text-end">
							<button type="button" class="btn btn-danger btn-sm remove-website"><i class="fas fa-trash-alt"></i></button>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="btn btn-secondary add-website mb-3"><?php esc_html_e( 'Add Website', 'profile-creator' ); ?></button>

			<h4 class="mt-4 mb-3"><?php esc_html_e( 'Emails', 'profile-creator' ); ?></h3>
			<div class="cpc-email-repeater card p-3 mb-3">
				<?php
				$emails = $submitted_data['dip_email'] ?? array( '' );
				foreach ( $emails as $index => $email ) :
				?>
				<div class="email-entry mb-3">
					<div class="row">
						<div class="col-md-10">
							<input type="email" class="form-control" name="dip_email[<?php echo esc_attr( $index ); ?>]" value="<?php echo esc_attr( $email ); ?>" placeholder="Email">
						</div>
						<div class="col-md-2 text-end">
							<button type="button" class="btn btn-danger btn-sm remove-email"><i class="fas fa-trash-alt"></i></button>
						</div>
					</div>
				</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="btn btn-secondary add-email mb-3"><?php esc_html_e( 'Add Email', 'profile-creator' ); ?></button>
		</div>
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Specific Contact Information', 'profile-creator' ); ?></h3>
			<div class="cpc-contact-repeater card p-3 mb-3">
				<?php
				$contacts = $submitted_data['specific_contact'] ?? array( array() );
				foreach ( $contacts as $index => $contact ) :
				?>
					<div class="contact-entry mb-3">
						<div class="row g-2">
							<div class="col-md-6">
								<input type="text" class="form-control" name="specific_contact[<?php echo $index; ?>][name]" placeholder="Contact Name" value="<?php echo esc_attr( $contact['name'] ?? '' ); ?>">
							</div>
							<div class="col-md-6">
								<input type="text" class="form-control" name="specific_contact[<?php echo $index; ?>][title]" placeholder="Contact Position" value="<?php echo esc_attr( $contact['title'] ?? '' ); ?>">
							</div>
							<div class="col-md-6">
								<input type="text" class="form-control" name="specific_contact[<?php echo $index; ?>][country]" placeholder="Country" value="<?php echo esc_attr( $contact['country'] ?? '' ); ?>">
							</div>
							<div class="col-md-6">
								<input type="text" class="form-control" name="specific_contact[<?php echo $index; ?>][phone]" placeholder="Contact Phone Number" value="<?php echo esc_attr( $contact['phone'] ?? '' ); ?>">
							</div>
							<div class="col-md-6">
								<input type="email" class="form-control" name="specific_contact[<?php echo $index; ?>][email]" placeholder="Contact Email" value="<?php echo esc_attr( $contact['email'] ?? '' ); ?>">
							</div>
							<div class="col-md-12 text-end mt-2">
								<button type="button" class="btn btn-danger btn-sm remove-contact"><i class="fas fa-trash-alt"></i></button>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
			<button type="button" class="btn btn-secondary add-contact"><?php esc_html_e( 'Add Contact', 'profile-creator' ); ?></button>
		</div>
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<label for="cpc_telephone" class="form-label"><?php esc_html_e( 'Telephone', 'profile-creator' ); ?></label>
			<input type="tel" class="form-control" id="cpc_telephone" name="cpc_telephone" value="<?php echo esc_attr( $submitted_data['cpc_telephone'] ?? '' ); ?>">
		</div>
		<div class="col-md-6">
			<label for="cpc_cv" class="form-label"><?php esc_html_e( 'Upload Your Company profile', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<input type="file" class="form-control <?php echo isset( $errors['cpc_cv'] ) ? 'is-invalid' : ''; ?>" id="cpc_cv" name="cpc_cv" accept=".pdf,.doc,.docx" required>
			<?php if ( isset( $errors['cpc_cv'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_cv'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_categories" class="form-label"><?php esc_html_e( 'Categories', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_categories" name="cpc_categories[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_categories'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_dip_categories() as $category ) : ?>
					<option value="<?php echo esc_attr( $category ); ?>" 
							<?php echo in_array( $category, (array) ( $submitted_data['cpc_categories'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $category ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_categories'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_categories'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_countries" class="form-label"><?php esc_html_e( 'Country of coverage', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_countries" name="cpc_countries[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_countries'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_countries() as $country ) : ?>
					<option value="<?php echo esc_attr( $country ); ?>" 
							<?php echo in_array( $country, (array) ( $submitted_data['cpc_countries'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $country ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_countries'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_countries'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_headquarters" class="form-label"><?php esc_html_e( 'Headquarters', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_headquarters" name="cpc_headquarters[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_headquarters'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_headquarters() as $head ) : ?>
					<option value="<?php echo esc_attr( $head ); ?>" 
							<?php echo in_array( $head, (array) ( $submitted_data['cpc_headquarters'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $head ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_headquarters'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_headquarters'] ); ?></div>
			<?php endif; ?>
		</div>		
	</div>
	<div class="row mb-3">
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Services', 'profile-creator' ); ?></h3>
			<div class="mb-3 cpc-services card p-3 <?php echo isset( $errors['cpc_services'] ) ? 'is-invalid' : ''; ?>">
				<div class="cpc-parent-list">
					<?php $this->render_service_checkboxes( isset( $submitted_data['cpc_services'] ) ? $submitted_data[ 'cpc_services' ] : array(), isset( $submitted_data['cpc_subservices'] ) ? $submitted_data[ 'cpc_subservices' ] : array() ); ?>
				</div>
				<?php if ( isset( $errors['cpc_services'] ) ) : ?>
					<div class="invalid-feedback d-block"><?php echo esc_html( $errors['cpc_services'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Sectors', 'profile-creator' ); ?></h3>
			<div class="mb-3 cpc-sectors card p-3 <?php echo isset( $errors['cpc_sectors'] ) ? 'is-invalid' : ''; ?>">
				<div class="cpc-parent-list">
					<?php $this->render_sector_checkboxes( isset( $submitted_data['cpc_sectors'] ) ? $submitted_data[ 'cpc_sectors' ] : array(), isset( $submitted_data['cpc_subsectors'] ) ? $submitted_data[ 'cpc_subsectors' ] : array() ); ?>
				</div>
				<?php if ( isset( $errors['cpc_sectors'] ) ) : ?>
					<div class="invalid-feedback d-block"><?php echo esc_html( $errors['cpc_sectors'] ); ?></div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="form-check mb-3">
		<input type="checkbox" class="form-check-input" name="dap_statement" required>
		<label class="form-check-label"><?php echo esc_html( get_option( 'dap_statement' ) ); ?></label>
	</div>

	<input type="hidden" name="cpc_type" value="dip">
	<button type="submit" name="cpc_submit" class="btn-primary"><?php printf( esc_html__( 'Create %s Profile', 'profile-creator' ), 'dip' ); ?></button>
</form>
