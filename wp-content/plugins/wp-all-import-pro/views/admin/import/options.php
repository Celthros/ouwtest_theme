<?php
$isWizard = $this->isWizard;
$baseUrl  = $this->baseUrl;
?>

<input type="hidden" id="selected_post_type"
       value="<?php echo ( ! empty( $post['custom_type'] ) ) ? $post['custom_type'] : ''; ?>">
<input type="hidden" id="selected_type" value="<?php echo ( ! empty( $post['type'] ) ) ? $post['type'] : ''; ?>">

<div class="wpallimport-step-4">

	<h2 class="wpallimport-wp-notices"></h2>

	<div class="wpallimport-wrapper">
		<h2 class="wpallimport-wp-notices"></h2>
		<div class="wpallimport-header">
			<div class="wpallimport-logo"></div>
			<div class="wpallimport-title">
				<h2><?php _e( 'Import Settings', 'wp-all-import-pro' ); ?></h2>
			</div>
			<div class="wpallimport-links">
				<a href="https://www.wpallimport.com/support/"
				   target="_blank"><?php _e( 'Support', 'wp-all-import-pro' ); ?></a> | <a
					href="https://www.wpallimport.com/documentation/"
					target="_blank"><?php _e( 'Documentation', 'wp-all-import-pro' ); ?></a>
			</div>
		</div>
		<div class="clear"></div>
	</div>

	<?php $visible_sections = apply_filters( 'pmxi_visible_options_sections', array(
		'reimport',
		'settings',
	), $post['custom_type'] ); ?>

	<table class="wpallimport-layout">
		<tr>
			<td class="left">

				<?php do_action( 'pmxi_options_header', $isWizard, $post ); ?>

				<?php
				$is_valid_root_element = true;
				$error_codes           = $this->warnings->get_error_codes();
				if ( ! empty( $error_codes ) and is_array( $error_codes ) and in_array( 'root-element-validation', $error_codes ) ) {
					$is_valid_root_element = false;
				}
				?>

				<div class="ajax-console">
					<?php if ( $this->errors->get_error_codes() ): ?>
						<?php $this->error() ?>
					<?php endif ?>
					<?php if ( $this->warnings->get_error_codes() ): ?>
						<?php $this->warning() ?>
					<?php endif ?>

					<?php
					wp_all_import_template_notifications( $post );
					?>
				</div>

				<div class="rad4 first-step-errors error-no-root-element"
				     <?php if ( $is_valid_root_element === false ): ?>style="display:block;"<?php endif; ?>>
					<div class="wpallimport-notify-wrapper">
						<div class="error-headers exclamation">
							<?php if ( isset( $is_404 ) && $is_404 && $update_previous->type == 'url' ): ?>
								<h3><?php _e( 'This URL no longer returns an import file', 'wp-all-import-pro' ); ?></h3>
								<h4 style="font-size:18px;"><?php _e( "You must provide a URL that returns a valid import file.", "wp-all-import-pro" ); ?></h4>
							<?php else: ?>
								<h3><?php _e( 'There\'s a problem with your import file', 'wp-all-import-pro' ); ?></h3>
								<h4 style="font-size:18px;"><?php _e( "It has changed and is not compatible with this import template.", "wp-all-import-pro" ); ?></h4>
							<?php endif; ?>
						</div>
					</div>
					<a class="button button-primary button-hero wpallimport-large-button wpallimport-notify-read-more"
					   href="https://www.wpallimport.com/documentation/problems-with-import-files/"
					   target="_blank"><?php _e( 'Read More', 'wp-all-import-pro' ); ?></a>
				</div>

				<form class="<?php echo ! $isWizard ? 'edit' : 'options' ?>" method="post" enctype="multipart/form-data"
				      autocomplete="off" <?php echo ! $isWizard ? 'style="overflow:visible;"' : '' ?>
				      id="wpai-submit-confirm-form">

					<?php $post_type = $post['custom_type']; ?>

					<?php if ( ! $this->isWizard ): ?>

						<?php include( 'options/_import_file.php' ); ?>

					<?php endif; ?>

					<div class="options">
						<?php

						if ( in_array( 'reimport', $visible_sections ) ) {
							if ( $post_type == 'taxonomies' ) {
								include( 'options/_reimport_taxonomies_template.php' );
							} elseif ( in_array( $post_type, [ 'comments', 'woo_reviews' ] ) ) {
								include( 'options/_reimport_comments_template.php' );
							} else {
								include( 'options/_reimport_template.php' );
							}
						}

						do_action( 'pmxi_options_tab', $isWizard, $post );

						if ( ! isset( $import ) ) {
							$import = $update_previous;
						}
						?>
						<div class="wpallimport-collapsed closed wpallimport-section scheduling">
							<div class="wpallimport-content-section">
								<div
									class="wpallimport-collapsed-header <?php if ( ! $import->canBeScheduled() ) { ?> disabled<?php } ?>"
									<?php if ( ! $import->canBeScheduled() ) { ?> title="<?php _e( "To run this import on a schedule you must use the 'Download from URL' or 'Use existing file' options in Step 1.", 'wp-all-import-pro' ); ?>" <?php } ?>>
									<h3 id="scheduling-title"><?php _e( 'Scheduling Options', 'wp-all-import-pro' ); ?>
										<?php if ( ! $import->canBeScheduled() ) { ?>
											<a href="#help" class="wpallimport-help"
											   style="position: relative; top: -2px; margin-left: 0; width: 20px; height: 20px;"
											   title="<?php _e( "To run this import on a schedule you must use the 'Download from URL' or 'Use existing file' option on the Import Settings page.", 'wp-all-import-pro' ); ?>">?</a>
										<?php } ?>
									</h3>
								</div>
								<div class="wpallimport-collapsed-content" style="padding: 0;">
									<div class="wpallimport-collapsed-content-inner">

										<?php
										include( 'options/scheduling/_scheduling_ui.php' );
										?>

									</div>
								</div>
							</div>
						</div>
						<?php
						if ( in_array( 'settings', $visible_sections ) ) {
							include( 'options/_settings_template.php' );
						}

						?>
						<?php if ( $import->type !== 'upload' ): ?>
							<div
								style="color: #425F9A; font-size: 14px; font-weight: bold; margin: 0 0 15px; line-height: 25px; text-align: center;">
								<div id="no-subscription" style="display: none;">
									<?php _e( "Looks like you're trying out Automatic Scheduling!", 'wp-all-import-pro' ); ?>
									<br />
									<?php _e( "Your Automatic Scheduling settings won't be saved without a subscription.", 'wp-all-import-pro' ); ?>
								</div>
							</div>

						<?php endif; ?>
						<input type="hidden" id="scheduling_import_id" value="<?php echo $import->id; ?>" />

						<?php
						include( 'options/_buttons_template.php' );
						?>
					</div>

				</form>
				<a href="http://soflyy.com/" target="_blank"
				   class="wpallimport-created-by"><?php _e( 'Created by', 'wp-all-import-pro' ); ?> <span></span></a>

			</td>
			<td class="right template-sidebar ">
				<div style="position:relative;">
					<?php $this->tag( false ); ?>
				</div>
			</td>
		</tr>
	</table>

</div>

<div id="record_matching_pointer" style="display:none;">

	<h3><?php _e( "Record Matching", 'wp-all-import-pro' ); ?></h3>

	<p>
		<b><?php _e( "Record Matching is how WP All Import matches records in this import file with posts that already exist in WordPress.", 'wp-all-import-pro' ); ?></b>
	</p>

	<p>
		<?php _e( "Record Matching is most commonly used to tell WP All Import how to match up records in this import file with posts WP All Import has already created on your site, so that if this import file is updated with new data, WP All Import can update your posts accordingly.", 'wp-all-import-pro' ); ?>
	</p>

	<hr />

	<p><?php _e( "AUTOMATIC RECORD MATCHING", 'wp-all-import-pro' ); ?></p>

	<p>
		<?php _e( "Automatic Record Matching allows WP All Import to update records that were imported or updated during the last run of this same import.", 'wp-all-import-pro' ); ?>
	</p>

	<p>
		<?php _e( "Your unique key must be UNIQUE for each record in your feed. Make sure you get it right - you can't change it later. You'll have to re-create your import.", 'wp-all-import-pro' ); ?>
	</p>

	<hr />

	<p><?php _e( "MANUAL RECORD MATCHING", 'wp-all-import-pro' ); ?></p>

	<p>
		<?php _e( "Manual record matching allows WP All Import to update any records, even records that were not imported with WP All Import, or are part of a different import.", 'wp-all-import-pro' ); ?>
	</p>

</div>
