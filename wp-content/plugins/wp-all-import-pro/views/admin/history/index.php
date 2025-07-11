<h4>
	<?php if ( $import->friendly_name ): ?>
		<em><?php printf( __( '%s - ID: %s Import History', 'wp-all-import-pro' ), $import->friendly_name, $import->id ); ?></em>
	<?php else: ?>
		<em><?php printf( __( '%s - ID: %s Import History', 'wp-all-import-pro' ), $import->name, $import->id ); ?></em>
	<?php endif ?>
</h4>

<?php if ( $this->errors->get_error_codes() ): ?>
	<?php $this->error() ?>
<?php endif ?>

<?php
// define the columns to display, the syntax is 'internal name' => 'display name'
$columns = array(
	'id'       => __( 'ID', 'wp-all-import-pro' ),
	'date'     => __( 'Date', 'wp-all-import-pro' ),
	'time_run' => __( 'Run Time', 'wp-all-import-pro' ),
	'type'     => __( 'Type', 'wp-all-import-pro' ),
	'summary'  => __( 'Summary', 'wp-all-import-pro' ),
	'download' => '',
);
?>

<?php if ( $import->triggered ): ?>
	<p><strong><?php _e( 'Scheduling Status', 'wp-all-import-pro' ); ?>
			:</strong> <?php _e( 'triggered', 'wp-all-import-pro' ); ?> <?php if ( $import->processing ) {
			_e( 'and processing', 'wp-all-import-pro' );
		} ?>...</p>
<?php endif; ?>

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
				<input type="checkbox" style="margin-top:1px;" />
			</th>
			<?php
			$col_html = '';
			foreach ( $columns as $column_id => $column_display_name ) {
				if ( ! in_array( $column_id, array( 'download' ) ) ) {
					$column_link = "<a href='";
					$order2      = 'ASC';
					if ( $order_by == $column_id ) {
						$order2 = ( $order == 'DESC' ) ? 'ASC' : 'DESC';
					}

					$column_link .= esc_url( add_query_arg( array( 'id'       => $id,
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
				<td colspan="<?php echo count( $columns ) + 1 ?>"><?php _e( 'No previous history found.', 'wp-all-import-pro' ) ?></td>
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
								<th valign="top" scope="row" style="vertical-align: middle;">
									<?php echo $item['id'] ?>
								</th>
								<?php
								break;
							case 'date':
								?>
								<td style="vertical-align: middle;">
									<?php if ( '0000-00-00 00:00:00' == $item['date'] ): ?>
										<em>never</em>
									<?php else: ?>
										<?php echo get_date_from_gmt( $item['date'], "m/d/Y g:i a" ); ?>
									<?php endif ?>
								</td>
								<?php
								break;
							case 'time_run':
								?>
								<td style="vertical-align: middle;">
									<?php echo ( $item['time_run'] and is_numeric( $item['time_run'] ) ) ? gmdate( "H:i:s", $item['time_run'] ) : '-'; ?>
								</td>
								<?php
								break;
							case 'summary':
								?>
								<td style="vertical-align: middle;">
									<?php echo $item['summary']; ?>
								</td>
								<?php
								break;
							case 'type':
								?>
								<td style="vertical-align: middle;">
									<?php
									switch ( $item['type'] ) {
										case 'manual':
											_e( 'manual run', 'wp-all-import-pro' );
											break;
										case 'continue':
											_e( 'continue run', 'wp-all-import-pro' );
											break;
										case 'processing':
											_e( 'cron processing', 'wp-all-import-pro' );
											break;
										case 'trigger':
											_e( 'triggered by cron', 'wp-all-import-pro' );
											break;
										case 'cli':
											_e( 'cli', 'wp-all-import-pro' );
											break;
										default:
											# code...
											break;
									}
									?>
								</td>
								<?php
								break;
							case 'download':
								?>
								<td style="vertical-align: middle;">
									<?php
									if ( ! in_array( $item['type'], array( 'trigger' ) ) ) {
										$wp_uploads = wp_upload_dir();
										$log_file   = wp_all_import_secure_file( $wp_uploads['basedir'] . DIRECTORY_SEPARATOR . PMXI_Plugin::LOGS_DIRECTORY, $item['id'], false, false ) . DIRECTORY_SEPARATOR . $item['id'] . '.html';
										if ( file_exists( $log_file ) ) {
											?>
											<a href="<?php echo esc_url( add_query_arg( array( 'id'         => $import->id,
											                                                   'action'     => 'log',
											                                                   'history_id' => $item['id'],
											                                                   '_wpnonce'   => wp_create_nonce( '_wpnonce-download_log' ),
											), $this->baseUrl ) ); ?>"><?php _e( 'Download Log', 'wp-all-import-pro' ); ?></a>
											<?php
										} else {
											_e( 'Log Unavailable', 'wp-all-import-pro' );
										}
									} else {
										?>
										&nbsp;
										<?php
									};
									?>
								</td>
								<?php
								break;
							default:
								?>
								<td>
									<?php echo $item[ $column_id ]; ?>
								</td>
								<?php
								break;
						endswitch;
						?>
					<?php endforeach; ?>
				</tr>
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