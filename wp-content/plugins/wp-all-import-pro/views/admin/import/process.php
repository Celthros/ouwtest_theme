<h2 class="wpallimport-wp-notices"></h2>

<div class="inner-content wpallimport-step-6 wpallimport-wrapper">

	<div class="wpallimport-header">
		<div class="wpallimport-logo"></div>
		<div class="wpallimport-title">
			<h2><?php _e( 'Confirm & Run', 'wp-all-import-pro' ); ?></h2>
		</div>
		<div class="wpallimport-links">
			<a href="https://www.wpallimport.com/support/"
			   target="_blank"><?php _e( 'Support', 'wp-all-import-pro' ); ?></a> | <a
				href="https://www.wpallimport.com/documentation/"
				target="_blank"><?php _e( 'Documentation', 'wp-all-import-pro' ); ?></a>
		</div>

		<div class="clear"></div>
		<div class="processing_step_1">

			<div class="clear"></div>

			<div class="step_description">
				<h2><?php _e( 'Import <span id="status">in Progress</span>', 'wp-all-import-pro' ) ?></h2>
				<h3 id="process_notice"><?php _e( 'Importing may take some time. Please do not close your browser or refresh the page until the process is complete.', 'wp-all-import-pro' ); ?></h3>
			</div>
			<div id="processbar" class="rad30">
				<div class="rad30"></div>
				<span id="center_progress"><span id="percents_count">0</span>%</span>
			</div>
			<div id="import_progress">
				<span id="left_progress"><?php _e( 'Time Elapsed', 'wp-all-import-pro' ); ?> <span
						id="then">00:00:00</span></span>
				<span id="right_progress">
                    <div class="progress_processed">
                        <span><?php _e( 'Processed', 'wp-all-import-pro' ); ?> <span
		                        class="processed_count"><?php echo( $update_previous->created + $update_previous->updated + $update_previous->skipped ); ?></span> <?php _e( 'of', 'wp-all-import-pro' ); ?> <span
		                        id="of"><?php echo $update_previous->count; ?></span> <?php _e( 'records', 'wp-all-import-pro' ); ?></span>
                    </div>
                    <div class="progress_details">
                        <span class="progress_details_item created_count"
                              <?php if ( empty( $update_previous->created ) ): ?>style="display:none;"<?php endif; ?>>
                            <?php _e( 'Created', 'wp-all-import-pro' ); ?> <span
		                        class="created_records_count"><?php echo $update_previous->created; ?></span>
                        </span>
                        <span class="progress_details_item deleted_count"
                              <?php if ( empty( $update_previous->created ) ): ?>style="display:none;"<?php endif; ?>>
                            <?php _e( 'Deleted', 'wp-all-import-pro' ); ?> <span
		                        class="deleted_records_count"><?php echo $update_previous->deleted; ?></span>
                        </span>
                        <span class="progress_details_item changed_count"
                              <?php if ( empty( $update_previous->created ) ): ?>style="display:none;"<?php endif; ?>>
                            <?php _e( 'Changed missing', 'wp-all-import-pro' ); ?> <span
		                        class="changed_records_count"><?php echo $update_previous->changed_missing; ?></span>
                        </span>
                        <span class="progress_details_item updated_count"
                              <?php if ( empty( $update_previous->created ) ): ?>style="display:none;"<?php endif; ?>>
                            <?php _e( 'Updated', 'wp-all-import-pro' ); ?> <span
		                        class="updated_records_count"><?php echo $update_previous->updated; ?></span>
                        </span>
                        <span class="progress_details_item skipped_count"
                              <?php if ( empty( $update_previous->skipped ) ): ?>style="display:none;"<?php endif; ?>>
                            <?php _e( 'Skipped', 'wp-all-import-pro' ); ?> <span
		                        class="skipped_records_count"><?php echo $update_previous->skipped; ?></span>
                        </span>
                    </div>
                </span>
			</div>
		</div>

		<?php
		$custom_type = wp_all_import_custom_type_labels( PMXI_Plugin::$session->options['custom_type'] );
		?>
		<div id="import_finished">
			<h1><?php _e( 'Import Complete!', 'wp-all-import-pro' ); ?></h1>
			<div class="wpallimport-content-section wpallimport-complete-statistics">
				<p><?php printf( __( 'All <b>%s</b> records from <b>%s</b> were successfully processed.', 'wp-all-import-pro' ), '<span class="processed_count"></span>', ( PMXI_Plugin::$session->source['type'] != 'url' ) ? basename( PMXI_Plugin::$session->source['path'] ) : PMXI_Plugin::$session->source['path'] ); ?></p>
				<p class="wpallimport-complete-details">
					<?php _e( 'WP All Import', 'wp-all-import-pro' ); ?>
					<span class="created_count complete-details-item"
					      style="display: none;"><?php printf( __( 'created <b>%s</b> new records', 'wp-all-import-pro' ), '<span class="created_records_count"></span>' ); ?></span><span
						class="updated_count complete-details-item"
						style="display: none;"><?php printf( __( 'updated <b>%s</b> records', 'wp-all-import-pro' ), '<span class="updated_records_count"></span>' ); ?></span><span
						class="deleted_count complete-details-item"
						style="display: none;"><?php printf( __( 'deleted <b>%s</b> records', 'wp-all-import-pro' ), '<span class="deleted_records_count"></span>' ); ?></span><span
						class="changed_count complete-details-item"
						style="display: none;"><?php printf( __( 'changed <b>%s</b> missing records', 'wp-all-import-pro' ), '<span class="changed_records_count"></span>' ); ?></span><span
						class="skipped_count complete-details-item"
						style="display: none;"><?php printf( __( 'skipped <b>%s</b> records', 'wp-all-import-pro' ), '<span class="skipped_records_count"></span>' ); ?></span>
				</p>
				<?php if ( ! empty( $update_previous->options['is_selective_hashing'] ) ): ?>
					<p class="wpallimport-skipped-notice">
						<b><span
								class="skipped_by_hash_records_count"></span></b> <?php printf( __( 'records were skipped because their data in <b>%s</b> hasn\'t changed.', 'wp-all-import-pro' ), ( PMXI_Plugin::$session->source['type'] != 'url' ) ? basename( PMXI_Plugin::$session->source['path'] ) : PMXI_Plugin::$session->source['path'] ); ?>
						<br /><a href="<?php echo esc_url( add_query_arg( array(
							'id'     => $update_previous->id,
							'page'   => 'pmxi-admin-manage',
							'action' => 'disable_skip_posts',
						), $this->baseUrl ) ); ?>"><?php _e( 'Run this import again without skipping records ›', 'wp-all-import-pro' ); ?></a>
					</p>
				<?php endif; ?>
			</div>
			<div class="wpallimport-content-section wpallimport-console wpallimport-complete-warning">
				<h3><?php _e( 'Duplicate records detected during import', 'wp-all-import-pro' ); ?><a href="#help"
				                                                                                      class="wpallimport-help"
				                                                                                      title="<?php _e( 'The unique identifier is how WP All Import tells two items in your import file apart. If it is the same for two items, then the first item will be overwritten when the second is imported.', 'wp-all-import-pro' ) ?>">?</a>
				</h3>
				<h4>
					<?php printf( __( 'The file you are importing has %s records, but WP All Import only created <span class="inserted_count"></span> %s. It detected the other records in this import file as duplicates. This could be because they actually are duplicates or it could be because your Unique Identifier is not unique for each record.<br><br>If your import file has no duplicates and you want to import all %s records, you should delete everything that was just imported and then edit your Unique Identifier so it\'s unique for each item.', 'wp-all-import-pro' ), $update_previous->count, $custom_type->labels->name, $update_previous->count ); ?>
				</h4>
				<input type="button"
				       class="button button-primary button-hero wpallimport-large-button wpallimport-delete-and-edit"
				       rel="<?php echo esc_url( add_query_arg( array(
					       'id'                       => $update_previous->id,
					       'page'                     => 'pmxi-admin-manage',
					       'action'                   => 'delete_and_edit',
					       '_wpnonce_delete-and-edit' => wp_create_nonce( 'delete-and-edit' ),
				       ), $this->baseUrl ) ); ?>" value="<?php _e( 'Delete & Edit', 'wp-all-import-pro' ); ?>" />
			</div>
			<div class="wpallimport-content-section wpallimport-console wpallimport-orders-complete-warning">
				<h3><?php printf( __( '<span class="skipped_records_count">%s</span> orders were skipped during this import', 'wp-all-import-pro' ), $update_previous->skipped ); ?></h3>
				<h4>
					<?php printf( __( 'WP All Import is unable to import an order when it cannot match the products or customer specified. <a href="%s" style="margin: 0;">See the import log</a> for a list of which orders were skipped and why.', 'wp-all-import-pro' ), esc_url( add_query_arg( array(
						'id'         => $update_previous->id,
						'page'       => 'pmxi-admin-history',
						'action'     => 'log',
						'history_id' => PMXI_Plugin::$session->history_id,
						'_wpnonce'   => wp_create_nonce( '_wpnonce-download_log' ),
					), $this->baseUrl ) ) ); ?>
				</h4>
				<input type="button"
				       class="button button-primary button-hero wpallimport-large-button wpallimport-delete-and-edit"
				       rel="<?php echo esc_url( add_query_arg( array(
					       'id'                       => $update_previous->id,
					       'page'                     => 'pmxi-admin-manage',
					       'action'                   => 'delete_and_edit',
					       '_wpnonce_delete-and-edit' => wp_create_nonce( 'delete-and-edit' ),
				       ), $this->baseUrl ) ); ?>" value="<?php _e( 'Delete & Edit', 'wp-all-import-pro' ); ?>" />
			</div>
			<!--			<h3 class="wpallimport-complete-success">-->
			<?php //printf(__('WP All Import successfully imported your file <span>%s</span> into your WordPress installation!','wp-all-import-pro'), (PMXI_Plugin::$session->source['type'] != 'url') ? basename(PMXI_Plugin::$session->source['path']) : PMXI_Plugin::$session->source['path'])?><!--</h3>						-->
			<?php if ( $ajax_processing ): ?>
				<p class="wpallimport-log-details"><?php printf( __( 'There were <span class="wpallimport-errors-count">%s</span> errors and <span class="wpallimport-warnings-count">%s</span> warnings in this import. You can see these in the import log.', 'wp-all-import-pro' ), 0, 0 ); ?></p>
			<?php elseif ( (int) PMXI_Plugin::$session->errors or (int) PMXI_Plugin::$session->warnings ): ?>
				<p class="wpallimport-log-details"
				   style="display:block;"><?php printf( __( 'There were <span class="wpallimport-errors-count">%s</span> errors and <span class="wpallimport-warnings-count">%s</span> warnings in this import. You can see these in the import log.', 'wp-all-import-pro' ), PMXI_Plugin::$session->errors, PMXI_Plugin::$session->warnings ); ?></p>
			<?php endif; ?>
			<hr>
			<a href="<?php echo esc_url( add_query_arg( array(
				'id'   => $update_previous->id,
				'page' => 'pmxi-admin-history',
			), $this->baseUrl ) ); ?>" id="download_log"><?php _e( 'View Logs', 'wp-all-import-pro' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'page' => 'pmxi-admin-manage' ), remove_query_arg( array(
				'id',
				'page',
			), $this->baseUrl ) ) ); ?>" id="manage_imports"><?php _e( 'Manage Imports', 'wp-all-import-pro' ) ?></a>
			<a href="<?php echo esc_url( add_query_arg( array(
				'page'   => 'pmxi-admin-manage',
				'id'     => $update_previous->id,
				'action' => 'update',
			), remove_query_arg( 'pagenum', $this->baseUrl ) ) ); ?>"
			   id="re_run_import"><?php _e( 'Re-Run Import', 'wp-all-import-pro' ); ?></a>
		</div>

	</div>

	<div class="wpallimport-content-section wpallimport-speed-up-notify">
		<button class="notice-dismiss dismiss-speed-up-notify" type="button">
			<span class="screen-reader-text"><?php _e( 'Hide this notice.', 'wp-all-import-pro' ); ?></span>
		</button>
		<div class="wpallimport-notify-wrapper">
			<div class="found_records speedup">
				<h3><?php _e( 'Want to speed up your import?', 'wp-all-import-pro' ); ?></h3>
				<h4><?php _e( "Check out our guide on increasing import speed.", "wp-all-import-pro" ); ?></h4>
			</div>
		</div>
		<a class="button button-primary button-hero wpallimport-large-button wpallimport-speed-up-notify-read-more"
		   href="http://www.wpallimport.com/documentation/troubleshooting/slow-imports/"
		   target="_blank"><?php _e( 'Read More', 'wp-all-import-pro' ); ?></a>
		<span><?php _e( 'opens in new tab', 'wp-all-import-pro' ); ?></span>
	</div>

	<div class="wpallimport-modal-message rad4">

		<div class="wpallimport-content-section" style="display:block; position: relative;">
			<div class="wpallimport-notify-wrapper">
				<div class="found_records terminated">
					<h3><?php _e( 'Your server terminated the import process', 'wp_all_import_plugin' ); ?></h3>
					<?php
					if ( ! empty( PMXI_Plugin::getInstance()->getOption( 'pmxi_auto_retry_import' ) ) ) {
						$auto_retry_enabled = true;
					} else {
						$auto_retry_enabled = false;
					}

					if ( $auto_retry_enabled === true ) :
						?>
						<h4 style="width: 77%; line-height: 25px;"><?php printf( __( "<a href='%s' target='_blank'>Read more</a> about how to prevent this from happening again. Since auto-retry is enabled in WP All Import's settings, the import will auto-continue in 10 seconds.", "wp_all_import_plugin" ), "https://www.wpallimport.com/documentation/problems-with-import-files/" ); ?></h4>
					<?php else: ?>
						<h4 style="width: 77%; line-height: 25px;"><?php printf( __( "<a href='%s' target='_blank'>Read more</a> about how to prevent this from happening again. If you'd like the import to auto-retry, enable the \"Enable auto-retry\" option via <a href='%s'>Settings</a>.", "wp_all_import_plugin" ), "https://www.wpallimport.com/documentation/problems-with-import-files/", esc_url( menu_page_url( 'pmxi-admin-settings', false ) ) ); ?></h4>
					<?php endif; ?>
				</div>
			</div>
			<input type="submit" id="wpallimport-try-again"
			       style="position: absolute; top: 30%; right: 10px; display: block; padding-top: 1px;"
			       value="<?php _e( 'Continue Import', 'wp-all-import-pro' ); ?>"
			       class="button button-primary button-hero wpallimport-large-button">
			<span
				class="wp_all_import_restart_import"><?php printf( __( "with <span id='wpallimport-new-records-per-iteration'>%s</span> records per iteration", 'wp-all-import-pro' ), ( ( ceil( $update_previous->options['records_per_request'] / 2 ) ) ? ceil( $update_previous->options['records_per_request'] / 2 ) : 1 ) ); ?></span>
		</div>
	</div>

	<fieldset id="logwrapper">
		<legend><?php _e( 'Log', 'wp-all-import-pro' ); ?></legend>
		<div id="loglist"></div>
	</fieldset>

	<input type="hidden" class="count_failures" value="0" />
	<input type="hidden" class="records_per_request"
	       value="<?php echo $update_previous->options['records_per_request']; ?>" />
	<span id="wpallimport-error-terminated" style="display:none;">
		<div class="wpallimport-content-section" style="display:block; position: relative;">
			<div class="wpallimport-notify-wrapper">
				<div class="found_records terminated" style="background-position: 0px 50% !important;">
					<h3><?php _e( 'Your server terminated the import process', 'wp-all-import-pro' ); ?></h3>
					<h4 style="width: 78%; line-height: 25px;"><?php _e( "Ask your host to check your server's error log. They will be able to determine why your server is terminating the import process.", "wp-all-import-pro" ); ?></h4>
				</div>
			</div>
			<a style="position: absolute; top: 35%; right: 10px; display: block; padding-top: 1px;"
			   class="button button-primary button-hero wpallimport-large-button"
			   href="http://www.wpallimport.com/documentation/troubleshooting/terminated-imports/"
			   target="_blank"><?php _e( 'Read More', 'wp-all-import-pro' ); ?></a>
		</div>
	</span>
	<a href="http://soflyy.com/" target="_blank"
	   class="wpallimport-created-by"><?php _e( 'Created by', 'wp-all-import-pro' ); ?> <span></span></a>

</div>

<script type="text/javascript">
	//<![CDATA[
	( function ( $ ) {

		window.onbeforeunload = function () {
			return 'WARNING:\nImport process in under way, leaving the page will interrupt\nthe operation and most likely to cause leftovers in posts.';
		};

		var odd = false;
		var interval;

		function write_log() {

			$( '.progress-msg' ).each( function ( i ) {

				if ( $( '#loglist' ).find( 'p' ).length > 350 ) $( '#loglist' ).html( '' );

				<?php if ( ! $ajax_processing ): ?>
				if ( $( this ).find( '.processing_info' ).length ) {
					$( '.created_records_count' ).html( $( this ).find( '.created_count' ).html() );
					$( '.updated_records_count' ).html( $( this ).find( '.updated_count' ).html() );
					$( '#percents_count' ).html( $( this ).find( '.percents_count' ).html() );
				}
				<?php endif; ?>

				if ( ! $( this ).find( '.processing_info' ).length ) {
					$( '#loglist' ).append( '<p ' + ( ( odd ) ? 'class="odd"' : 'class="even"' ) + '>' + $( this ).html() + '</p>' );
					odd = ! odd;
				}
				$( this ).remove();
			} );
		}

		$( '.dismiss-speed-up-notify' ).on( 'click', function ( e ) {
			e.preventDefault();
			$.post( 'admin.php?page=pmxi-admin-settings&action=dismiss_speed_up', { dismiss : true }, function ( data ) {
			}, 'html' );
			$( '.wpallimport-speed-up-notify' ).addClass( 'dont_show_again' ).slideUp();
		} );

		$( '.wpallimport-speed-up-notify-read-more' ).on( 'click', function ( e ) {
			e.preventDefault();
			$.post( 'admin.php?page=pmxi-admin-settings&action=dismiss_speed_up', { dismiss : true }, function ( data ) {
			}, 'html' );
			$( '.wpallimport-speed-up-notify' ).addClass( 'dont_show_again' ).slideUp();
			window.open( $( this ).attr( 'href' ), '_blank' );
		} );

		$( '#status' ).each( function () {

			var then = $( '#then' );
			let start_date = new Date().getTime(),
				elapsed = '0.0';

			update = function () {

				let offset = new Date().getTime() - start_date;

				elapsed = Math.floor( offset / 100 ) / 10;

				// Format seconds into elapsed time string.
				let fm = [
					/*Math.floor(elapsed / 60 / 60 / 24), // DAYS*/
					Math.floor( elapsed / 60 / 60 ) % 24, // HOURS
					Math.floor( elapsed / 60 ) % 60, // MINUTES
					Math.floor( elapsed % 60 ) // SECONDS
				];
				elapsed = $.map( fm, function ( v, i ) {
					return ( ( v < 10 ) ? '0' : '' ) + v;
				} ).join( ':' );

				/*var duration = wpai_moment.duration({'seconds' : 1});
				 start_date.add(duration);*/

				if ( $( '#process_notice' ).is( ':visible' ) && ! $( '.wpallimport-modal-message' ).is( ':visible' ) ) {
					then.html( elapsed );
				}
			};
			update();
			setInterval( update, 1000 );

			var records_per_request = $( '.records_per_request' ).val();
			var execution_time = 0;

			var $this = $( this );
			interval = setInterval( function () {

				write_log();

				var percents = $( '#percents_count' ).html();
				$( '#processbar div' ).css( { 'width' : ( ( parseInt( percents ) > 100 || percents == undefined ) ? 100 : percents ) + '%' } );

				execution_time ++;

				if ( execution_time == 300 && parseInt( percents ) < 10 && ! $( '.wpallimport-speed-up-notify' ).hasClass( 'dont_show_again' ) && ! $( '.wpallimport-modal-message' ).is( ':visible' ) ) {
					$( '.wpallimport-speed-up-notify' ).show();
				}

			}, 1000 );

			$( '#processbar' ).css( { 'visibility' : 'visible' } );

			<?php if ( $ajax_processing ): ?>

			var import_id = '<?php echo $update_previous->id; ?>';

			function parse_element( failures ) {

				$.get( 'admin.php?page=pmxi-admin-import&action=process&id=' + import_id + '&failures=' + failures + '&_wpnonce=' + wp_all_import_security, {}, function ( data ) {

					// response with error
					if ( data != null && typeof data.created != "undefined" ) {

						$( '.wpallimport-modal-message' ).hide();
						$( '.created_records_count' ).html( data.created );
						if ( parseInt( data.created ) ) {
							$( '.created_count' ).show();
						}
						$( '.inserted_count' ).html( data.created );
						$( '.updated_records_count' ).html( data.updated );
						if ( parseInt( data.updated ) ) {
							$( '.updated_count' ).show();
						}
						$( '.skipped_records_count' ).html( data.skipped );
						$( '.skipped_by_hash_records_count' ).html( data.skipped_by_hash );
						if ( parseInt( data.skipped ) ) {
							$( '.skipped_count' ).show();
						}
						$( '.deleted_records_count' ).html( data.deleted );
						if ( parseInt( data.deleted ) ) {
							$( '.deleted_count' ).show();
						}
						$( '.changed_records_count' ).html( data.changed_missing );
						if ( parseInt( data.changed_missing ) ) {
							$( '.changed_count' ).show();
						}
						$( '.processed_count' ).html( parseInt( data.created ) + parseInt( data.updated ) + parseInt( data.skipped ) );
						$( '#warnings' ).html( data.warnings );
						$( '#errors' ).html( data.errors );
						$( '#percents_count' ).html( data.percentage );
						$( '#processbar div' ).css( { 'width' : data.percentage + '%' } );

						records_per_request = data.records_per_request;

						if ( data.done ) {
							clearInterval( update );
							clearInterval( interval );

							setTimeout( function () {

								$( '#loglist' ).append( data.log );
								$( '#process_notice' ).hide();
								$( '.processing_step_1' ).hide();

								// detect broken auto-created Unique ID and notify user
								<?php if ( $this->isWizard and $update_previous->options['wizard_type'] == 'new' and ! $update_previous->options['deligate']): ?>
								if ( data.imported != data.created ) {
									$( '.wpallimport-complete-warning' ).show();
								}
								<?php endif; ?>

								<?php if ( ! $update_previous->options['deligate'] and ! empty( $update_previous->options['custom_type'] ) and $update_previous->options['custom_type'] == 'shop_order' and empty( $update_previous->options['is_import_specified'] )): ?>
								if ( data.skipped > 0 ) {
									$( '.wpallimport-orders-complete-warning' ).show();
								}
								<?php endif; ?>

								if ( ! parseInt( data.created ) && ! parseInt( data.updated ) && ! parseInt( data.skipped ) && ! parseInt( data.deleted ) ) {
									$( '.wpallimport-complete-details' ).hide();
								}
								if ( parseInt( data.skipped_by_hash ) > 0 ) {
									$( '.wpallimport-skipped-notice' ).show();
								}

								$( '#import_finished' ).show( 'fast', function () {
									let items = $( '.wpallimport-complete-details .complete-details-item:visible' );
									if ( items.length > 1 ) {
										for ( let i = 0; i < items.length - 2; i ++ ) {
											items[ i ].append( ', ' );
										}
										items.last().prepend( ', and ' );
									}
									items.last().append( '.' );
								} );

								if ( parseInt( data.errors ) || parseInt( data.warnings ) ) {
									$( '.wpallimport-log-details' ).find( '.wpallimport-errors-count' ).html( data.errors );
									$( '.wpallimport-log-details' ).find( '.wpallimport-warnings-count' ).html( data.warnings );
									$( '.wpallimport-log-details' ).show();
								}

							}, 1000 );
						} else {
							$( '#loglist' ).append( data.log );
							parse_element( 0 );
						}

						write_log();

					} else {
						var count_failures = parseInt( $( '.count_failures' ).val() );
						count_failures ++;
						$( '.count_failures' ).val( count_failures );

						if ( data != null && typeof data != 'undefined' && typeof data.log != 'undefined' ) {
							$( '#loglist' ).append( data.log );
							write_log();
						}

						if ( data != null && typeof data != 'undefined' && parseInt( data.records_per_request ) ) {
							records_per_request = data.records_per_request;
						}

						if ( count_failures > 4 || records_per_request < 2 ) {
							$( '#process_notice' ).hide();
							$( '.wpallimport-modal-message' ).html( $( '#wpallimport-error-terminated' ).html() ).show();
							var errorMessage = "Import failed, please check logs";
							if ( data != null && typeof data != 'undefined' && typeof data.responseText != 'undefined' ) {
								errorMessage = data.responseText;
							}
							$( '#status' ).html( 'Error ' + '<span class="pmxi_error_msg">' + errorMessage + '</span>' );

							clearInterval( update );
							window.onbeforeunload = false;

							var request = {
								action : 'import_failed',
								id : '<?php echo $update_previous->id; ?>',
								security : wp_all_import_security
							};

							$.ajax( {
								type : 'POST',
								url : ajaxurl,
								data : request,
								success : function ( response ) {

								},
								error : function ( request ) {

								},
								dataType : "json"
							} );

						} else {
							$( '#wpallimport-records-per-iteration' ).html( records_per_request );
							$( '#wpallimport-new-records-per-iteration' ).html( Math.ceil( parseInt( records_per_request ) / 2 ) );
							records_per_request = Math.ceil( parseInt( records_per_request ) / 2 );
							$( '.wpallimport-modal-message' ).show();
							//parse_element(1);
						}
						return;
					}

				}, 'json' ).fail( function ( data ) {

					var count_failures = parseInt( $( '.count_failures' ).val() );
					count_failures ++;
					$( '.count_failures' ).val( count_failures );

					if ( count_failures > 4 || records_per_request < 2 ) {
						$( '#process_notice' ).hide();
						$( '.wpallimport-modal-message' ).html( $( '#wpallimport-error-terminated' ).html() ).show();

						if ( data != null && typeof data != 'undefined' ) {
							$( '#status' ).html( 'Error ' + '<span class="pmxi_error_msg">' + data.responseText + '</span>' );
						} else {
							$( '#status' ).html( 'Error' );
						}
						clearInterval( update );
						window.onbeforeunload = false;

						var request = {
							action : 'import_failed',
							id : '<?php echo $update_previous->id; ?>',
							security : wp_all_import_security
						};

						$.ajax( {
							type : 'POST',
							url : ajaxurl,
							data : request,
							success : function ( response ) {

							},
							error : function ( request ) {

							},
							dataType : "json"
						} );
					} else {
						$( '#wpallimport-records-per-iteration' ).html( records_per_request );
						$( '#wpallimport-new-records-per-iteration' ).html( Math.ceil( parseInt( records_per_request ) / 2 ) );
						records_per_request = Math.ceil( parseInt( records_per_request ) / 2 );
						$( '.wpallimport-modal-message' ).show();
						<?php
						if ( $auto_retry_enabled === true ):
						?>
						setTimeout( function () {
							// Auto-retry import.
							$( '#wpallimport-try-again' ).click();
						}, 10000 );
						<?php
						endif;
						?>
					}
				} );
			}

			$( '#wpallimport-try-again' ).on( 'click', function ( e ) {
				e.preventDefault();
				parse_element( 1 );
				$( '.wpallimport-modal-message' ).hide();
			} );

			$( '#processbar' ).css( { 'visibility' : 'visible' } );

			parse_element( 0 );

			<?php else: ?>

			complete = function () {
				if ( $( '#status' ).html() == 'Complete' ) {
					setTimeout( function () {
						$( '#process_notice' ).hide();
						$( '.processing_step_1' ).hide();
						$( '#import_finished' ).fadeIn();
					}, 1000 );
					clearInterval( update );
					clearInterval( complete );
				}
			};
			setInterval( complete, 1000 );
			complete();

			<?php endif; ?>

		} );

	} )( jQuery );

	//]]>
</script>
