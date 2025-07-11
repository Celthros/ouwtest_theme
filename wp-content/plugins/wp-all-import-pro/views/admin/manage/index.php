<script type="text/javascript">
	( function ( $, ajaxurl, wp_all_import_security ) {

		$( document ).ready( function () {
			$( '.open_cron_scheduling' ).on( 'click', function () {

				var itemId = $( this ).data( 'itemid' );
				openSchedulingDialog( itemId, $( this ), '<?php echo WP_ALL_IMPORT_ROOT_URL; ?>/static/img/preloader.gif' );
			} );
		} );
	} )( jQuery, ajaxurl, wp_all_import_security );

	window.pmxiHasSchedulingSubscription = <?php echo PMXI_Plugin::hasActiveSchedulingLicense() ? 'true' : 'false';  ?>;
</script>

<h2></h2> <!-- Do not remove -->

<div class="wpallimport-header" style="overflow:hidden; height: 70px; padding-top: 10px; margin-bottom: -15px;">
	<div class="wpallimport-logo"></div>
	<div class="wpallimport-title">
		<h3><?php _e( 'Manage Imports', 'wp-all-import-pro' ); ?></h3>
	</div>
</div>

<?php if ( $this->errors->get_error_codes() ): ?>
	<?php $this->error() ?>
<?php endif ?>

<form method="get">
	<input type="hidden" name="page" value="<?php echo esc_attr( $this->input->get( 'page' ) ) ?>" />
	<p class="search-box">
		<label for="search-input" class="screen-reader-text"><?php _e( 'Search Imports', 'wp-all-import-pro' ) ?>
			:</label>
		<input id="search-input" type="text" name="s" value="<?php echo esc_attr( $s ) ?>" />
		<input type="submit" class="button" value="<?php _e( 'Search Imports', 'wp-all-import-pro' ) ?>">
	</p>
</form>

<?php
// define the columns to display, the syntax is 'internal name' => 'display name'
$columns = array(
	'id'      => __( 'ID', 'wp-all-import-pro' ),
	'name'    => __( 'File', 'wp-all-import-pro' ),
	'actions' => '',
	'summary' => __( 'Summary', 'wp-all-import-pro' ),
	'info'    => __( 'Info & Options', 'wp-all-import-pro' ),
);

$columns = apply_filters( 'pmxi_manage_imports_columns', $columns );

?>
<form method="post" id="import-list" action="<?php echo esc_url( remove_query_arg( 'pmxi_nt' ) ) ?>">
	<input type="hidden" name="action" value="bulk" />
	<?php wp_nonce_field( 'bulk-imports', '_wpnonce_bulk-imports' ) ?>

	<div class="tablenav">
		<div class="alignleft actions">
			<select name="bulk-action">
				<option value="" selected="selected"><?php _e( 'Bulk Actions', 'wp-all-import-pro' ) ?></option>
				<option value="delete"><?php _e( 'Delete', 'wp-all-import-pro' ) ?></option>
			</select>
			<input type="submit" value="<?php esc_attr_e( 'Apply', 'wp-all-import-pro' ) ?>" name="doaction"
			       id="doaction" class="button-secondary action" />
		</div>

		<?php if ( $page_links ): ?>
			<div class="tablenav-pages">
				<?php echo $page_links_html = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'wp-all-import-pro' ) . '</span>%s', number_format_i18n( ( $pagenum - 1 ) * $perPage + 1 ), number_format_i18n( min( $pagenum * $perPage, $list->total() ) ), number_format_i18n( $list->total() ), $page_links ) ?>
			</div>
		<?php endif ?>
	</div>
	<div class="clear"></div>

	<table class="widefat pmxi-admin-imports">
		<thead>
		<tr>
			<th class="manage-column column-cb check-column" scope="col">
				<input type="checkbox" />
			</th>
			<?php
			$col_html = '';
			foreach ( $columns as $column_id => $column_display_name ) {
				if ( in_array( $column_id, array( 'id', 'name' ) ) ) {
					$column_link = "<a href='";
					$order2      = 'ASC';
					if ( $order_by == $column_id ) {
						$order2 = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					}

					$column_link .= esc_url( add_query_arg( array(
						'order'    => $order2,
						'order_by' => $column_id,
					), $this->baseUrl ) );
					$column_link .= "'>{$column_display_name}</a>";
					$col_html    .= '<th scope="col" class="column-' . $column_id . ' ' . ( $order_by == $column_id ? $order : '' ) . '">' . $column_link . '</th>';
				} else {
					$col_html .= '<th scope="col" class="column-' . $column_id . '">' . $column_display_name . '</th>';
				}
			}
			echo $col_html;
			?>
		</tr>
		</thead>
		<tfoot>
		<tr>
			<th class="manage-column column-cb check-column" scope="col">
				<input type="checkbox" />
			</th>
			<?php echo $col_html; ?>
		</tr>
		</tfoot>
		<tbody id="the-pmxi-admin-import-list" class="list:pmxi-admin-imports">
		<?php if ( $list->isEmpty() ): ?>
			<tr>
				<td colspan="<?php echo count( $columns ) + 1 ?>"><?php printf( __( 'No previous imports found. <a href="%s">Start a new import...</a>', 'wp-all-import-pro' ), esc_url( add_query_arg( array( 'page' => 'pmxi-admin-import' ), admin_url( 'admin.php' ) ) ) ); ?></td>
			</tr>
		<?php else: ?>
			<?php
			$class = '';
			?>
			<?php foreach ( $list as $item ): ?>
				<?php $class = ( 'alternate' == $class ) ? '' : 'alternate'; ?>
				<tr class="<?php echo $class; ?>" valign="middle">
					<th scope="row" class="check-column">
						<input type="checkbox" id="item_<?php echo $item['id'] ?>" name="items[]"
						       value="<?php echo esc_attr( $item['id'] ) ?>" />
					</th>
					<?php foreach ( $columns as $column_id => $column_display_name ): ?>
						<?php
						switch ( $column_id ):
							case 'id':
								?>
								<th valign="top" scope="row">
									<?php echo $item['id'] ?>
								</th>
								<?php
								break;
							case 'first_import':
								?>
								<td>
									<?php if ( '0000-00-00 00:00:00' == $item['first_import'] ): ?>
										<em>never</em>
									<?php else: ?>
										<?php echo get_date_from_gmt( $item['first_import'], 'Y/m/d g:i a' ); ?>
									<?php endif ?>
								</td>
								<?php
								break;
							case 'registered_on':
								?>
								<td>
									<?php if ( '0000-00-00 00:00:00' == $item['registered_on'] ): ?>
										<em>never</em>
									<?php else: ?>
										<?php echo get_date_from_gmt( $item['registered_on'], 'Y/m/d g:i a' ); ?>
									<?php endif ?>
								</td>
								<?php
								break;
							case 'name':
								?>
								<td>
									<strong><?php echo apply_filters( "pmxi_import_name", ( ! empty( $item['friendly_name'] ) ) ? $item['friendly_name'] : $item['name'], $item['id'] ); ?></strong><br>

									<?php if ( $item['path'] ): ?>
										<?php if ( in_array( $item['type'], array( 'upload' ) ) ): ?>
											<?php $item['path'] = wp_all_import_get_absolute_path( $item['path'] ); ?>
											<?php
											$path       = $item['path'];
											$path_parts = pathinfo( $item['path'] );
											if ( ! empty( $path_parts['dirname'] ) ) {
												$path_all_parts = explode( '/', $path_parts['dirname'] );
												$dirname        = array_pop( $path_all_parts );
												if ( wp_all_import_isValidMd5( $dirname ) ) {
													$path = str_replace( $dirname, preg_replace( '%^(.{3}).*(.{3})$%', '$1***$2', $dirname ), str_replace( 'temp/', '', $item['path'] ) );
												}
											}
											?>
											<em><a href="<?php echo esc_url( add_query_arg( array(
													'id'       => $item['id'],
													'action'   => 'feed',
													'_wpnonce' => wp_create_nonce( '_wpnonce-download_feed' ),
												), $this->baseUrl ) ); ?>" class="wp_all_import_show_path"
											       rel="<?php echo $item['path']; ?>"><?php echo preg_replace( '%.*wp-content/%', 'wp-content/', $path ); ?></a></em>
										<?php elseif ( in_array( $item['type'], array( 'ftp' ) ) ): ?>
											<em><?php echo $item['options']['ftp_username'] . '@' . preg_replace( '%^ftps?://%i', '', $item['options']['ftp_host'] ) . '/' . $item['options']['ftp_path']; ?></em>
										<?php elseif ( in_array( $item['type'], array( 'file' ) ) ): ?>
											<?php $item['path'] = wp_all_import_get_absolute_path( $item['path'] ); ?>
											<em><a href="<?php echo esc_url( add_query_arg( array(
													'id'       => $item['id'],
													'action'   => 'feed',
													'_wpnonce' => wp_create_nonce( '_wpnonce-download_feed' ),
												), $this->baseUrl ) ); ?>" class="wp_all_import_show_path"
											       rel="<?php echo $item['path']; ?>"><?php echo preg_replace( '%.*wp-content/%', 'wp-content/', $item['path'] ); ?></a></em>
										<?php else: ?>
											<em><?php echo str_replace( "\\", '/', preg_replace( '%^(\w+://[^:]+:)[^@]+@%', '$1*****@', $item['path'] ) ); ?></em>
										<?php endif; ?>
									<?php endif ?>

									<?php $delete_missing_notice = wp_all_import_delete_missing_notice( $item['options'] ); ?>
									<?php if ( ! empty( $delete_missing_notice ) ): ?>
										<div class="wpai-row-notice" style="padding-top:10px;">
											<p><?php echo $delete_missing_notice; ?></p></div>
									<?php endif; ?>

									<div class="row-actions">
										<?php do_action( 'pmxi_import_menu', $item['id'], $this->baseUrl ); ?>
										<?php
										$import_actions = array(
											'import_template' => array(
												'url'   => ( ! $item['processing'] and ! $item['executing'] ) ? esc_url( add_query_arg( array(
													'id'     => $item['id'],
													'action' => 'edit',
												), $this->baseUrl ) ) : '',
												'title' => __( 'Edit Template', 'wp-all-import-pro' ),
												'class' => 'edit',
											),
											'import_settings' => array(
												'url'   => ( ! $item['processing'] and ! $item['executing'] ) ? esc_url( add_query_arg( array(
													'id'     => $item['id'],
													'action' => 'options',
												), $this->baseUrl ) ) : '',
												'title' => __( 'Settings', 'wp-all-import-pro' ),
												'class' => 'edit',
											),
											'delete'          => array(
												'url'   => esc_url( add_query_arg( array(
													'id'     => $item['id'],
													'action' => 'delete',
												), $this->baseUrl ) ),
												'title' => __( 'Delete', 'wp-all-import-pro' ),
												'class' => 'delete',
											),
										);

										$import_actions = apply_filters( 'pmxi_import_actions', $import_actions, $item );

										$ai = 1;
										foreach ( $import_actions as $key => $action ) {
											switch ( $key ) {
												default:
													?>
													<span class="<?php echo $action['class']; ?>">
															<?php if ( ! empty( $action['url'] ) ): ?>
																<a class="<?php echo $action['class']; ?>"
																   href="<?php echo esc_url( $action['url'] ); ?>"><?php echo $action['title']; ?></a>
															<?php else: ?>
																<span
																	class="wpallimport-disabled"><?php echo $action['title']; ?></span>
															<?php endif; ?>
														</span> <?php if ( $ai != count( $import_actions ) ): ?>|<?php endif; ?>
													<?php
													break;
											}
											$ai ++;
										}
										?>

									</div>
								</td>
								<?php
								break;
							case 'summary':
								?>
								<td>
									<?php
									if ( $item['triggered'] and ! $item['processing'] ) {
										_e( 'triggered with cron', 'wp-all-import-pro' );
										if ( $item['last_activity'] != '0000-00-00 00:00:00' ) {
											$diff = ceil( ( time() - strtotime( $item['last_activity'] ) ) / 60 );
											?>
											<br>
											<span <?php if ( $diff >= 10 ) {
												echo 'style="color:red;"';
											} ?>>
											<?php
											printf( __( 'last activity %s ago', 'wp-all-import-pro' ), human_time_diff( strtotime( $item['last_activity'] ), time() ) );
											?>
											</span>
											<?php
										}
									} elseif ( $item['processing'] ) {
										_e( 'currently processing with cron', 'wp-all-import-pro' );
										echo '<br/>';
										printf( 'Records Processed %s', $item['imported'] );
										if ( $item['last_activity'] != '0000-00-00 00:00:00' ) {
											$diff = ceil( ( time() - strtotime( $item['last_activity'] ) ) / 60 );
											?>
											<br>
											<span <?php if ( $diff >= 10 ) {
												echo 'style="color:red;"';
											} ?>>
											<?php
											printf( __( 'last activity %s ago', 'wp-all-import-pro' ), human_time_diff( strtotime( $item['last_activity'] ), time() ) );
											?>
											</span>
											<?php
										}
									} elseif ( $item['executing'] ) {
										_e( 'Import currently in progress', 'wp-all-import-pro' );
										if ( $item['last_activity'] != '0000-00-00 00:00:00' ) {
											$diff = ceil( ( time() - strtotime( $item['last_activity'] ) ) / 60 );
											?>
											<br>
											<span <?php if ( $diff >= 10 ) {
												echo 'style="color:red;"';
											} ?>>
											<?php
											printf( __( 'last activity %s ago', 'wp-all-import-pro' ), human_time_diff( strtotime( $item['last_activity'] ), time() ) );
											?>
											</span>
											<?php
										}
									} elseif ( $item['canceled'] and $item['canceled_on'] != '0000-00-00 00:00:00' ) {
										printf( __( 'Import Attempt at %s', 'wp-all-import-pro' ), get_date_from_gmt( $item['canceled_on'], "m/d/Y g:i a" ) );
										echo '<br/>';
										_e( 'Import canceled', 'wp-all-import-pro' );
									} elseif ( $item['failed'] and $item['failed_on'] != '0000-00-00 00:00:00' ) {
										printf( __( 'Import Attempt at %s', 'wp-all-import-pro' ), get_date_from_gmt( $item['failed_on'], "m/d/Y g:i a" ) );
										echo '<br/>';
										_e( 'Import failed, please check logs', 'wp-all-import-pro' );
									} else {
										if ( ! empty( $item['options']['custom_type'] ) ) {
											switch ( $item['options']['custom_type'] ) {
												case 'taxonomies':
													$tx                          = get_taxonomy( $item['options']['taxonomy_type'] );
													$custom_type                 = new stdClass();
													$custom_type->label          = empty( $tx->labels->name ) ? __( 'Taxonomy Terms', 'wp-all-import-pro' ) : $tx->labels->name;
													$custom_type->singular_label = empty( $tx->labels->singular_name ) ? __( 'Taxonomy Term', 'wp-all-import-pro' ) : $tx->labels->singular_name;
													break;
												case 'comments':
													$custom_type                 = new stdClass();
													$custom_type->label          = __( 'Comments', 'wp-all-import-pro' );
													$custom_type->singular_label = __( 'Comment', 'wp-all-import-pro' );
													break;
												case 'woo_reviews':
													$custom_type                 = new stdClass();
													$custom_type->label          = __( 'WooCommerce Reviews', 'wp-all-import-pro' );
													$custom_type->singular_label = __( 'Review', 'wp-all-import-pro' );
													break;
												case 'import_users':
													$custom_type                 = new stdClass();
													$custom_type->label          = __( 'Users', 'wp-all-import-pro' );
													$custom_type->singular_label = __( 'User', 'wp-all-import-pro' );
													break;
												case 'shop_customer':
													$custom_type                 = new stdClass();
													$custom_type->label          = __( 'WooCommerce Customers', 'wp-all-import-pro' );
													$custom_type->singular_label = __( 'WooCommerce Customer', 'wp-all-import-pro' );
													break;
												default:
													$custom_type = wp_all_import_custom_type( $item['options']['custom_type'] );
													if ( ! empty( $custom_type ) ) {
														$custom_type->label          = $custom_type->labels->name;
														$custom_type->singular_label = $custom_type->labels->singular_name;
													}
													break;
											}
											$cpt_name = ( ! empty( $custom_type ) ) ? ( ( $item['created'] == 1 ) ? $custom_type->singular_label : $custom_type->label ) : '';
										} else {
											$cpt_name = '';
										}
										printf( __( 'Last run: %s', 'wp-all-import-pro' ), ( $item['registered_on'] == '0000-00-00 00:00:00' ) ? __( 'never', 'wp-all-import-pro' ) : get_date_from_gmt( $item['registered_on'], "m/d/Y g:i a" ) );
										echo '<br/>';
										printf( __( '%d %s created', 'wp-all-import-pro' ), $item['created'], $cpt_name );
										echo '<br/>';
										//printf(__('%d updated, %d skipped, %d deleted'), $item['updated'], $item['skipped'], $item['deleted']);

										$log_msg = sprintf( __( '%d updated, %d skipped', 'wp-all-import-pro' ), $item['updated'], $item['skipped'] );
										if ( $item['options']['is_delete_missing'] ) {
											if ( empty( $item['options']['delete_missing_action'] ) || $item['options']['delete_missing_action'] != 'remove' ) {
												$log_msg = sprintf( __( '%d updated, %d skipped, %d missing', 'wp-all-import-pro' ), $item['updated'], $item['skipped'], $item['changed_missing'] );
											} else {
												$log_msg = sprintf( __( '%d updated, %d skipped, %d deleted', 'wp-all-import-pro' ), $item['updated'], $item['skipped'], $item['deleted'] );
											}
										}
										printf( $log_msg );

										//printf(__('%d records', 'wp-all-import-pro'), $item['post_count']);
									}

									if ( $item['settings_update_on'] != '0000-00-00 00:00:00' and $item['last_activity'] != '0000-00-00 00:00:00' and strtotime( $item['settings_update_on'] ) > strtotime( $item['last_activity'] ) ) {
										echo '<br/>';
										?>
										<strong><?php _e( 'settings edited since last run', 'wp-all-import-pro' ); ?></strong>
										<?php
									}

									?>
								</td>
								<?php
								break;
							case 'info':
								?>
								<td>

									<?php if ( ! in_array( $item['type'], array( 'url', 'ftp', 'file' ) ) ) { ?>
										<a href="#"
										   class="scheduling-disabled"><?php _e( 'Scheduling Options', 'wp-all-import-pro' ); ?></a>
										<a href="#help" class="wpallimport-help"
										   style="position: relative; top: -2px; margin-left: 0;"
										   title="<?php _e( "To run this import on a schedule you must use the 'Download from URL' or 'Use existing file' option on the Import Settings page.", 'wp-all-import-pro' ); ?>">?</a>
										<br />
									<?php } else { ?>
										<a href="javascript:void(0);"
										   class="open_cron_scheduling"
										   data-itemid="<?php echo $item['id']; ?>"><?php _e( 'Scheduling Options', 'wp-all-import-pro' ); ?></a>
										<br />

									<?php } ?>

									<a href="<?php echo esc_url( add_query_arg( array(
										'page' => 'pmxi-admin-history',
										'id'   => $item['id'],
									), remove_query_arg( 'pagenum', $this->baseUrl ) ) ); ?>"><?php _e( 'History Logs', 'wp-all-import-pro' ); ?></a>

								</td>
								<?php
								break;
							case 'actions':
								?>
								<td style="width: 130px;">
									<?php if ( ! $item['processing'] and ! $item['executing'] ): ?>
										<h2 style="float:left;"><a class="add-new-h2"
										                           href="<?php echo esc_url( add_query_arg( array(
											                           'id'     => $item['id'],
											                           'action' => 'update',
										                           ), remove_query_arg( 'pagenum', $this->baseUrl ) ) ); ?>"><?php _e( 'Run Import', 'wp-all-import-pro' ); ?></a>
										</h2>
									<?php elseif ( $item['processing'] ) : ?>
										<h2 style="float:left;"><a class="add-new-h2"
										                           href="<?php echo esc_url( add_query_arg( array(
											                           'id'       => $item['id'],
											                           'action'   => 'cancel',
											                           '_wpnonce' => wp_create_nonce( '_wpnonce-cancel_import' ),
										                           ), remove_query_arg( 'pagenum', $this->baseUrl ) ) ); ?>"><?php _e( 'Cancel Cron', 'wp-all-import-pro' ); ?></a>
										</h2>
									<?php elseif ( $item['executing'] ) : ?>
										<h2 style="float:left;"><a class="add-new-h2"
										                           href="<?php echo esc_url( add_query_arg( array(
											                           'id'       => $item['id'],
											                           'action'   => 'cancel',
											                           '_wpnonce' => wp_create_nonce( '_wpnonce-cancel_import' ),
										                           ), remove_query_arg( 'pagenum', $this->baseUrl ) ) ); ?>"><?php _e( 'Cancel', 'wp-all-import-pro' ); ?></a>
										</h2>
									<?php endif; ?>
								</td>
								<?php
								break;
							default:
								?>
								<td>
									<?php do_action( 'pmxi_manage_imports_column', $column_id, $item ); ?>
								</td>
								<?php
								break;
						endswitch;
						?>
					<?php endforeach; ?>
				</tr>
				<?php do_action( 'pmxi_manage_imports', $item, $class ); ?>
			<?php endforeach; ?>
		<?php endif ?>
		</tbody>
	</table>

	<div class="tablenav">
		<?php if ( $page_links ): ?>
			<div class="tablenav-pages"><?php echo $page_links_html ?></div><?php endif ?>

		<div class="alignleft actions">
			<select name="bulk-action2">
				<option value="" selected="selected"><?php _e( 'Bulk Actions', 'wp-all-import-pro' ) ?></option>
				<?php if ( empty( $type ) or 'trash' != $type ): ?>
					<option value="delete"><?php _e( 'Delete', 'wp-all-import-pro' ) ?></option>
				<?php else: ?>
					<option value="restore"><?php _e( 'Restore', 'wp-all-import-pro' ) ?></option>
					<option value="delete"><?php _e( 'Delete Permanently', 'wp-all-import-pro' ) ?></option>
				<?php endif ?>
			</select>
			<input type="submit" value="<?php esc_attr_e( 'Apply', 'wp-all-import-pro' ) ?>" name="doaction2"
			       id="doaction2" class="button-secondary action" />
		</div>
	</div>
	<div class="clear"></div>
	<a href="http://soflyy.com/" target="_blank"
	   class="wpallimport-created-by"><?php _e( 'Created by', 'wp-all-import-pro' ); ?> <span></span></a>

</form>
<div class="wpallimport-overlay"></div>
<div class="wpallimport-super-overlay"></div>

<div class="wpallimport-loader"
     style="border-radius: 5px; z-index: 999999; display:none; position: fixed;top: 200px;    left: 50%; width: 100px;height: 100px;background-color: #fff; text-align: center;">
	<img style="margin-top: 45%;" src="<?php echo WP_ALL_IMPORT_ROOT_URL; ?>/static/img/preloader.gif" />
</div>

