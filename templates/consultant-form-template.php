<?php
/**
 * Consultant Form Template
 *
 * Template for rendering the consultant profile creation form.
 *
 * @package ProfileCreator
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! empty( $submitted_post ) && null !== $submitted_post ) {
	$submitted_data[ 'cpc_name' ]           = get_the_title( $submitted_post );
	$submitted_data[ 'cpc_email' ]          = get_post_meta( $submitted_post, 'consultant-email', true );
	$submitted_data[ 'cpc_telephone' ]      = get_post_meta( $submitted_post, 'consultant-telephone', true );
	$submitted_data[ 'cpc_mobile' ]         = get_post_meta( $submitted_post, 'consultant-mobile', true );
	$submitted_data[ 'cpc_linkedin' ]       = get_post_meta( $submitted_post, 'consultant-linkedin', true );
	$submitted_data[ 'cpc_experience' ]     = get_post_meta( $submitted_post, 'consultant-experience', true );
	$submitted_data[ 'cpc_languages' ]      = get_post_meta( $submitted_post, 'consultant-langs', true );
	$submitted_data[ 'cpc_citizenship' ]    = get_post_meta( $submitted_post, 'consultant-citizenship', true );
	$submitted_data[ 'cpc_country_of_exp' ] = get_post_meta( $submitted_post, 'consultant-working-country', true );
	$submitted_data[ 'cpc_gender' ]         = get_post_meta( $submitted_post, 'consultant-gender', true );
	$submitted_data[ 'cpc_qualifications' ] = get_post_meta( $submitted_post, 'overview', true );
	$submitted_data[ 'cpc_clients' ]        = get_post_meta( $submitted_post, 'partners', true );
	$submitted_data[ 'cpc_education' ]      = get_post_meta( $submitted_post, 'education', true );
	$submitted_data[ 'cpc_services' ]       = get_post_meta( $submitted_post, 'consultant-services', true );
	$submitted_data[ 'cpc_subservices' ]    = get_post_meta( $submitted_post, 'consultant-sub-service', true );
	$submitted_data[ 'cpc_sectors' ]        = get_post_meta( $submitted_post, 'consultant-sectors', true );
	$submitted_data[ 'cpc_subsectors' ]     = get_post_meta( $submitted_post, 'consultant-sub-sectors', true );

}

?>

<?php if ( ! empty( $errors ) ) : ?>
	<div class="alert alert-danger">
		<ul>
			<?php
			foreach ( $errors as $error ) {
				?>
				<li><?php echo esc_html( $error ); ?></li>
				<?php
			}
			?>
		</ul>
	</div>
<?php endif; ?>
<form method="post" class="cpc-profile-form container" enctype="multipart/form-data">
	<?php wp_nonce_field( 'cpc_create_consultant_profile', 'cpc_nonce' ); ?>

	<div class="row mb-3">
		<div class="col-md-12" style="text-align: center;">
			<h2><?php esc_html_e( 'Create Consultant Profile', 'profile-creator' ); ?></h2>
			<p><?php esc_html_e( 'Please fill out the form below to create your consultant profile.', 'profile-creator' ); ?></p>
		</div>
	</div>
	<div class="row mb-3">
		<?php if ( ! is_user_logged_in() ) : ?>
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'User Information', 'profile-creator' ); ?></h3>
			<div class="col-md-6">
				<label for="cpc_email" class="form-label"><?php esc_html_e( 'E-mail', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
				<input type="email" class="form-control <?php echo isset( $errors['cpc_email'] ) ? 'is-invalid' : ''; ?>" 
					id="cpc_email" name="cpc_email" value="<?php echo esc_attr( $submitted_data['cpc_email'] ?? '' ); ?>" required>
				<?php if ( isset( $errors['cpc_email'] ) ) : ?>
					<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_email'] ); ?></div>
				<?php endif; ?>
			</div>
			<div class="col-md-6">
				<label for="cpc_password" class="form-label"><?php esc_html_e( 'Password', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
				<input type="password" class="form-control <?php echo isset( $errors['cpc_password'] ) ? 'is-invalid' : ''; ?>" 
					id="cpc_password" name="cpc_password" value="<?php echo esc_attr( $submitted_data['cpc_password'] ?? '' ); ?>" required>
				<?php if ( isset( $errors['cpc_password'] ) ) : ?>
					<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_password'] ); ?></div>
				<?php endif; ?>
			</div>
		<?php endif ?>
	</div>
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
			<label for="dpc_photo" class="form-label"><?php esc_html_e( 'Upload Your Photo', 'profile-creator' ); ?></label>
			<input type="file" class="form-control" id="dpc_photo" name="dpc_photo" accept="image/*">
			<!-- Image Preview -->
			<div id="photo-preview-wrapper" class="mt-2">
				<?php if ( ! empty( $submitted_data['dpc_photo'] ) ) : ?>
					<img src="<?php echo esc_url( $submitted_data['dpc_photo'] ); ?>" alt="Photo Preview" id="photo-preview" class="img-thumbnail" style="max-width: 150px;">
				<?php else : ?>
					<img src="" alt="Photo Preview" id="photo-preview" class="img-thumbnail d-none" style="max-width: 150px;">
				<?php endif; ?>
			</div>
		</div>
	</div>

	<h3 class="mt-4 mb-3"><?php esc_html_e( 'Contact Information', 'profile-creator' ); ?></h3>
	<div class="row mb-3">
		<?php if ( is_user_logged_in() ) : ?>
			<div class="col-md-6">
				<label for="cpc_email" class="form-label"><?php esc_html_e( 'E-mail', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
				<input type="email" class="form-control <?php echo isset( $errors['cpc_email'] ) ? 'is-invalid' : ''; ?>" 
					id="cpc_email" name="cpc_email" value="<?php echo esc_attr( $submitted_data['cpc_email'] ?? '' ); ?>" required>
				<?php if ( isset( $errors['cpc_email'] ) ) : ?>
					<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_email'] ); ?></div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
		<div class="col-md-6">
			<label for="cpc_telephone" class="form-label"><?php esc_html_e( 'Telephone', 'profile-creator' ); ?></label>
			<input type="tel" class="form-control" id="cpc_telephone" name="cpc_telephone" 
				value="<?php echo esc_attr( $submitted_data['cpc_telephone'] ?? '' ); ?>">
		</div>
		<div class="col-md-6">
			<label for="cpc_mobile" class="form-label"><?php esc_html_e( 'Mobile', 'profile-creator' ); ?></label>
			<input type="tel" class="form-control" id="cpc_mobile" name="cpc_mobile" 
				value="<?php echo esc_attr( $submitted_data['cpc_mobile'] ?? '' ); ?>">
		</div>
		<div class="col-md-6">
			<label for="cpc_linkedin" class="form-label"><?php esc_html_e( 'LinkedIn Profile', 'profile-creator' ); ?></label>
			<input type="url" class="form-control" id="cpc_linkedin" name="cpc_linkedin" 
				value="<?php echo esc_attr( $submitted_data['cpc_linkedin'] ?? '' ); ?>">
		</div>

		<div class="col-md-6">
			<label for="cpc_experience" class="form-label"><?php esc_html_e( 'Years of Experience', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<input type="number" class="form-control <?php echo isset( $errors['cpc_experience'] ) ? 'is-invalid' : ''; ?>" 
				id="cpc_experience" name="cpc_experience" min="0" value="<?php echo esc_attr( $submitted_data['cpc_experience'] ?? '' ); ?>" required>
			<?php if ( isset( $errors['cpc_experience'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_experience'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_languages" class="form-label"><?php esc_html_e( 'Languages', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_languages" name="cpc_languages[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_languages'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_languages() as $lang ) : ?>
					<option value="<?php echo esc_attr( $lang ); ?>" 
							<?php echo in_array( $lang, (array) ( $submitted_data['cpc_languages'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $lang ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_languages'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_languages'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_citizenship" class="form-label"><?php esc_html_e( 'Citizenship', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_citizenship" name="cpc_citizenship[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_citizenship'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_countries() as $country ) : ?>
					<option value="<?php echo esc_attr( $country ); ?>" 
							<?php echo in_array( $country, (array) ( $submitted_data['cpc_citizenship'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $country ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_citizenship'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_citizenship'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_country_of_exp" class="form-label"><?php esc_html_e( 'Country of experience', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_country_of_exp" name="cpc_country_of_exp[]" class="form-control cpc-select2 <?php echo isset( $errors['cpc_country_of_exp'] ) ? 'is-invalid' : ''; ?>" multiple required>
				<?php foreach ( $this->get_countries() as $country ) : ?>
					<option value="<?php echo esc_attr( $country ); ?>" 
							<?php echo in_array( $country, (array) ( $submitted_data['cpc_country_of_exp'] ?? array() ) ) ? 'selected' : ''; ?>>
						<?php echo esc_html( $country ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php if ( isset( $errors['cpc_country_of_exp'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_country_of_exp'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_gender" class="form-label"><?php esc_html_e( 'Gender', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<select id="cpc_gender" name="cpc_gender" class="form-select <?php echo isset( $errors['cpc_gender'] ) ? 'is-invalid' : ''; ?>" required>
				<option value=""><?php esc_html_e( 'Select Gender', 'profile-creator' ); ?></option>
				<option value="Male" <?php echo ( $submitted_data['cpc_gender'] ?? '' ) === 'Male' ? 'selected' : ''; ?>><?php esc_html_e( 'Male', 'profile-creator' ); ?></option>
				<option value="Female" <?php echo ( $submitted_data['cpc_gender'] ?? '' ) === 'Female' ? 'selected' : ''; ?>><?php esc_html_e( 'Female', 'profile-creator' ); ?></option>
			</select>
			<?php if ( isset( $errors['cpc_gender'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_gender'] ); ?></div>
			<?php endif; ?>
		</div>
		<div class="col-md-6">
			<label for="cpc_cv" class="form-label"><?php esc_html_e( 'Upload Your CV', 'profile-creator' ); ?> <span class="text-danger">*</span></label>
			<input type="file" class="form-control <?php echo isset( $errors['cpc_cv'] ) ? 'is-invalid' : ''; ?>" id="cpc_cv" name="cpc_cv" accept=".pdf,.doc,.docx">
			<?php if ( isset( $errors['cpc_cv'] ) ) : ?>
				<div class="invalid-feedback"><?php echo esc_html( $errors['cpc_cv'] ); ?></div>
			<?php endif; ?>
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
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Client(s) Worked With', 'profile-creator' ); ?> <span class="text-danger">*</span></h3>
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

	<h3 class="mt-4 mb-3"><?php esc_html_e( 'Education', 'profile-creator' ); ?></h3>
	<div class="cpc-education-repeater card p-3 mb-3">
		<?php
		$education_entries = $submitted_data['cpc_education'] ?? array( array() );
		foreach ( $education_entries as $index => $entry ) :
			?>
			<div class="education-entry mb-3">
				<div class="row">
					<div class="col-md-6 mb-2">
						<label class="form-label"><?php esc_html_e( 'School', 'profile-creator' ); ?></label>
						<input type="text" class="form-control" name="cpc_education[<?php echo esc_attr( $index ); ?>][school]" 
							value="<?php echo esc_attr( $entry['school'] ?? '' ); ?>">
					</div>
					<div class="col-md-6 mb-2">
						<label class="form-label"><?php esc_html_e( 'Degree', 'profile-creator' ); ?></label>
						<input type="text" class="form-control" name="cpc_education[<?php echo esc_attr( $index ); ?>][degree]" 
							value="<?php echo esc_attr( $entry['degree'] ?? '' ); ?>">
					</div>
					<div class="col-md-6 mb-2">
						<label class="form-label"><?php esc_html_e( 'Field of Study', 'profile-creator' ); ?></label>
						<input type="text" class="form-control" name="cpc_education[<?php echo esc_attr( $index ); ?>][field]" 
							value="<?php echo esc_attr( $entry['field'] ?? '' ); ?>">
					</div>
					<div class="col-md-3 mb-2">
						<label class="form-label"><?php esc_html_e( 'Start Date', 'profile-creator' ); ?></label>						
						<select class="form-select" name="cpc_education[<?php echo esc_attr( $index ); ?>][start_date]" style="padding: 7px 20px;">
							<option>Select Year</option>
							<?php
							for ( $i = date( "Y" ) - 70; $i <= date( "Y" ) + 7; $i++ ) {
								?>
								<option value="<?php echo $i ;?>" <?php echo isset( $entry['start_date'] ) && $i == $entry['start_date'] ? 'selected' : ''; ?> ><?php echo $i ;?></option>
								<?php
							}
							?>
						</select>	
					</div>
					<div class="col-md-3 mb-2">
						<label class="form-label"><?php esc_html_e( 'End Date  (or expected)', 'profile-creator' ); ?></label>
						<select class="form-select" name="cpc_education[<?php echo esc_attr( $index ); ?>][end_date]" style="padding: 7px 20px;" >
							<option>Select Year</option>
							<?php
							for ( $i = date( "Y" ) - 70; $i <= date( "Y" ) + 7; $i++ ) {
								?>
								<option value="<?php echo $i ;?>" <?php echo isset( $entry['end_date'] ) && $i == $entry['end_date'] ? 'selected' : ''; ?> ><?php echo $i ;?></option>
								<?php
							}
							?>
						</select>	
					</div>
				</div>
				<button type="button" class="btn btn-danger btn-sm remove-education mt-2"><?php esc_html_e( 'Remove', 'profile-creator' ); ?></button>
			</div>
		<?php endforeach; ?>
	</div>
	<button type="button" class="btn-secondary add-education mb-3"><?php esc_html_e( 'Add Education', 'profile-creator' ); ?></button>

	<div class="row mb-3">
		<div class="col-md-6">
			<h3 class="mt-4 mb-3"><?php esc_html_e( 'Services', 'profile-creator' ); ?></h3>
			<div class="mb-3 cpc-services card p-3 <?php echo isset( $errors['cpc_services'] ) ? 'is-invalid' : ''; ?>">
				<div class="cpc-parent-list">
					<?php $this->render_service_checkboxes( isset( $submitted_data['cpc_services'] ) ? $submitted_data[ 'cpc_services' ] : array(), isset( $submitted_data['cpc_subservices'] ) && ! empty( $submitted_data['cpc_subservices'] ) ? $submitted_data[ 'cpc_subservices' ] : array() ); ?>
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
					<?php $this->render_sector_checkboxes( isset( $submitted_data['cpc_sectors'] ) ? $submitted_data[ 'cpc_sectors' ] : array(), isset( $submitted_data['cpc_subsectors'] ) && ! empty( $submitted_data['cpc_subsectors'] ) ? $submitted_data[ 'cpc_subsectors' ] : array() ); ?>
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

	<input type="hidden" name="cpc_type" value="consultant">
	<button type="submit" name="cpc_submit" class="btn btn-primary"><?php printf( esc_html__( 'Create %s Profile', 'profile-creator' ), 'consultant' ); ?></button>
</form>
