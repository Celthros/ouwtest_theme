<?php
$custom_type = get_taxonomy( $post['taxonomy_type'] );
if ( empty( $custom_type ) ) {
	$custom_type                        = new stdClass();
	$custom_type->labels                = new stdClass();
	$custom_type->labels->name          = __( 'Taxonomy Terms', 'wp-all-import-pro' );
	$custom_type->labels->singular_name = __( 'Taxonomy Term', 'wp-all-import-pro' );
}
?>
<div class="wpallimport-collapsed wpallimport-section">
	<script type="text/javascript">
		__META_KEYS = <?php echo json_encode( $existing_meta_keys ) ?>;
	</script>
	<div class="wpallimport-content-section">
		<div class="wpallimport-collapsed-header">
			<?php
			if ( "new" == $post['wizard_type'] ): ?>
			<?php
			if ( ! $this->isWizard ) {
				?>
				<h3><?php _e( 'Record Matching', 'wp-all-import-pro' ); ?></h3>
				<?php
			} else {
				if ( ! empty( PMXI_Plugin::$session->deligate ) and PMXI_Plugin::$session->deligate == 'wpallexport' ) {
					?>
					<h3 style="padding-left:0;"><?php _e( 'Choose how exported data will be re-imported.', 'wp-all-import-pro' ); ?></h3>
					<?php
				} else {
					?>
					<h3 style="padding-left:0;"><?php printf( __( 'WP All Import will create new %s for each unique record in this import file.', 'wp-all-import-pro' ), $custom_type->labels->singular_name ); ?></h3>
					<?php
				}
			}
			?>
		</div>
		<div class="wpallimport-collapsed-content" style="padding: 0;">
			<div class="wpallimport-collapsed-content-inner">
				<table class="form-table" style="max-width:none;">
					<tr>
						<td>
							<input type="hidden" name="duplicate_matching" value="auto" />
							<?php if ( ! $this->isWizard ): ?>
								<h4><?php printf( __( 'WP All Import will associate records in this import file with %s it has already created from previous runs of this import based on the Unique Identifier.', 'wp-all-import-pro' ), $custom_type->labels->name ); ?></h4>
							<?php endif; ?>
							<div class="wpallimport-unique-key-wrapper"
							     <?php if ( ! empty( PMXI_Plugin::$session->deligate ) ): ?>style="display:none;"<?php endif; ?>>
								<label
									style="font-weight: bold;"><?php _e( "Unique Identifier", "wp-all-import-pro" ); ?></label>

								<input type="text" class="smaller-text" name="unique_key" style="width:300px;"
								       value="<?php if ( ! $this->isWizard ) {
									       echo esc_attr( $post['unique_key'] );
								       } elseif ( $post['tmp_unique_key'] ) {
									       echo esc_attr( $post['unique_key'] );
								       } ?>" <?php echo ( ! $isWizard and ! empty( $post['unique_key'] ) ) ? 'disabled="disabled"' : '' ?>/>

								<?php if ( $this->isWizard ): ?>
									<input type="hidden" name="tmp_unique_key"
									       value="<?php echo ( $post['unique_key'] ) ? esc_attr( $post['unique_key'] ) : esc_attr( $post['tmp_unique_key'] ); ?>" />
									<a href="javascript:void(0);"
									   class="wpallimport-auto-detect-unique-key"><?php _e( 'Auto-detect', 'wp-all-import-pro' ); ?></a>
								<?php else: ?>
									<?php if ( ! empty( $post['unique_key'] ) ): ?>
										<a href="javascript:void(0);"
										   class="wpallimport-change-unique-key"><?php _e( 'Edit', 'wp-all-import-pro' ); ?></a>
										<div id="dialog-confirm"
										     title="<?php _e( 'Warning: Are you sure you want to edit the Unique Identifier?', 'wp-all-import-pro' ); ?>"
										     style="display:none;">
											<p><?php printf( __( 'It is recommended you delete all %s associated with this import before editing the unique identifier.', 'wp-all-import-pro' ), strtolower( $custom_type->labels->name ) ); ?></p>
											<p><?php printf( __( 'Editing the unique identifier will dissociate all existing %s linked to this import. Future runs of the import will result in duplicates, as WP All Import will no longer be able to update these %s.', 'wp-all-import-pro' ), strtolower( $custom_type->labels->name ), strtolower( $custom_type->labels->name ) ); ?></p>
											<p><?php _e( 'You really should just re-create your import, and pick the right unique identifier to start with.', 'wp-all-import-pro' ); ?></p>
										</div>
									<?php else: ?>
										<input type="hidden" name="tmp_unique_key"
										       value="<?php echo ( $post['unique_key'] ) ? esc_attr( $post['unique_key'] ) : esc_attr( $post['tmp_unique_key'] ); ?>" />
										<a href="javascript:void(0);"
										   class="wpallimport-auto-detect-unique-key"><?php _e( 'Auto-detect', 'wp-all-import-pro' ); ?></a>
									<?php endif; ?>
								<?php endif; ?>

								<?php if ( $this->isWizard ): ?>
									<p>&nbsp;</p>
									<p class="drag_an_element_ico"><?php _e( 'Drag an element, or combo of elements, to the box above. The Unique Identifier should be unique for each record in this import file, and should stay the same even if this import file is updated. Things like product IDs, titles, and SKUs are good Unique Identifiers because they probably won\'t change. Don\'t use a description or price, since that might be changed.', 'wp-all-import-pro' ); ?></p>
									<p class="info_ico"><?php printf( __( 'If you run this import again with an updated file, the Unique Identifier allows WP All Import to correctly link the records in your updated file with the %s it will create right now. If multiple records in this import file have the same Unique Identifier, only the first will be created. The others will be detected as duplicates.', 'wp-all-import-pro' ), $custom_type->labels->name ); ?></p>
								<?php endif; ?>
							</div>

							<?php include( '_reimport_taxonomies_options.php' ); ?>

						</td>
					</tr>
				</table>
			</div>
		</div>
		<?php else: ?>
		<?php if ( ! $this->isWizard ): ?>
			<h3><?php _e( 'Record Matching', 'wp-all-import-pro' ); ?></h3>
		<?php else: ?>
			<h3 style="padding-left:0;"><?php printf( __( 'WP All Import will merge data into existing %s.', 'wp-all-import-pro' ), $custom_type->labels->name ); ?></h3>
		<?php endif; ?>
	</div>
	<div class="wpallimport-collapsed-content" style="padding:0;">
		<div class="wpallimport-collapsed-content-inner">
			<table class="form-table" style="max-width:none;">
				<tr>
					<td>
						<div class="input" style="margin-bottom:15px; position:relative;">
							<input type="hidden" name="duplicate_matching" value="manual" />
							<h4><?php printf( __( 'Records in this import file will be matched with %s on your site based on...', 'wp-all-import-pro' ), $custom_type->labels->singular_name ); ?></h4>
							<div style="margin-left: -4px;">
								<div class="input">
									<div class="input">
										<input type="radio" id="duplicate_indicator_title" class="switcher"
										       name="duplicate_indicator"
										       value="title" <?php echo 'title' == $post['duplicate_indicator'] ? 'checked="checked"' : '' ?>/>
										<label
											for="duplicate_indicator_title"><?php _e( 'Name', 'wp-all-import-pro' ) ?></label>
									</div>
									<div class="switcher-target-duplicate_indicator_title" style="padding-left:17px;">
										<input type="text" name="title_xpath"
										       value="<?php echo empty( $post['title_xpath'] ) ? esc_attr( $post['title'] ) : esc_attr( $post['title_xpath'] ) ?>" />
									</div>
									<div class="input">
										<input type="radio" id="duplicate_indicator_slug" class="switcher"
										       name="duplicate_indicator"
										       value="slug" <?php echo 'slug' == $post['duplicate_indicator'] ? 'checked="checked"' : '' ?>/>
										<label
											for="duplicate_indicator_slug"><?php _e( 'Slug', 'wp-all-import-pro' ) ?></label>
									</div>
									<div class="switcher-target-duplicate_indicator_slug" style="padding-left:17px;">
										<input type="text" name="slug_xpath"
										       value="<?php echo empty( $post['slug_xpath'] ) ? esc_attr( $post['post_slug'] ) : esc_attr( $post['slug_xpath'] ); ?>" />
									</div>
									<div class="input">
										<input type="radio" id="duplicate_indicator_custom_field" class="switcher"
										       name="duplicate_indicator"
										       value="custom field" <?php echo 'custom field' == $post['duplicate_indicator'] ? 'checked="checked"' : '' ?>/>
										<label
											for="duplicate_indicator_custom_field"><?php _e( 'Custom field', 'wp-all-import-pro' ) ?></label>
									</div>
									<div class="switcher-target-duplicate_indicator_custom_field"
									     style="padding-left:17px;">
										<?php _e( 'Name', 'wp-all-import-pro' ) ?>
										<input type="text" name="custom_duplicate_name"
										       value="<?php echo esc_attr( $post['custom_duplicate_name'] ) ?>" />
										<?php _e( 'Value', 'wp-all-import-pro' ) ?>
										<input type="text" name="custom_duplicate_value"
										       value="<?php echo esc_attr( $post['custom_duplicate_value'] ) ?>" />
									</div>
									<div class="input">
										<input type="radio" id="duplicate_indicator_pid" class="switcher"
										       name="duplicate_indicator"
										       value="pid" <?php echo 'pid' == $post['duplicate_indicator'] ? 'checked="checked"' : '' ?>/>
										<label
											for="duplicate_indicator_pid"><?php _e( 'Term ID', 'wp-all-import-pro' ) ?></label>
									</div>
									<div class="switcher-target-duplicate_indicator_pid" style="padding-left:17px;">
										<input type="text" name="pid_xpath"
										       value="<?php echo esc_attr( $post['pid_xpath'] ) ?>" />
									</div>
								</div>
							</div>
						</div>
						<?php include( '_reimport_taxonomies_options.php' ); ?>
					</td>
				</tr>
			</table>
		</div>
	</div>
	<?php endif; ?>
</div>
</div>
