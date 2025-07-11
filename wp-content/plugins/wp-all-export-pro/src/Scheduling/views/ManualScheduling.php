<?php
if ( ! defined( 'ABSPATH' ) ) {
	die();
}
?>
<?php
$exportOptions         = ( isset( $post ) ) ? $post : $schedulingExportOptions;
$is_rapid_addon_export = PMXE_Helper::is_rapid_export_addon( $exportOptions['cpt'] );
?>
<div style="padding-top:11px">
	<label>
		<input type="radio" name="scheduling_enable"
		       value="2" <?php if ( isset( $exportOptions['scheduling_enable'] ) && $exportOptions['scheduling_enable'] == 2 ) { ?> checked="checked" <?php } ?>/>
		<h4 style="margin: 0;display: inline-block;"><?php esc_html_e( 'Manual Scheduling', 'wp_all_export_plugin' ); ?></h4>
	</label>
	<div
		style="margin-left: 26px; margin-bottom: 10px; font-size: 13px; margin-top: -3px;"><?php esc_html_e( 'Run this export using cron jobs.', 'wp_all_export_plugin' ); ?></div>
	<div
		style="<?php if ( isset( $exportOptions['scheduling_enable'] ) && $exportOptions['scheduling_enable'] != 2 ) { ?> display: none; <?php } ?>"
		class="manual-scheduling">
		<p style="margin:0;">
		<h5 style="margin-bottom: 10px; margin-top: 10px; font-size: 14px;"><?php esc_html_e( 'Trigger URL', 'wp_all_export_plugin' ); ?></h5>
		<code style="padding: 10px; border: 1px solid #ccc; display: block; width: 90%;">
			<?php echo esc_url( site_url() . '/wp-load.php?export_key=' . $cron_job_key . '&export_id=' . $export_id . '&action=trigger' ); ?>
		</code>
		</p>
		<p style="margin: 0 0 15px;">
		<h5 style="margin-bottom: 10px; margin-top: 10px; font-size: 14px;"><?php esc_html_e( 'Processing URL' ); ?></h5>
		<code style="padding: 10px; border: 1px solid #ccc; display: block; width: 90%;">
			<?php echo esc_url( site_url() . '/wp-load.php?export_key=' . $cron_job_key . '&export_id=' . $export_id . '&action=processing' ); ?>
		</code>
		</p>
		<p style="margin: 0 0 15px;">
		<h5 style="margin-bottom: 10px; margin-top: 10px; font-size: 14px;"><?php esc_html_e( 'File URL' ); ?></h5>
		<code style="padding: 10px; border: 1px solid #ccc; display: block; width: 90%;">
			<?php echo esc_url( site_url() . '/wp-load.php?security_token=' . substr( md5( $cron_job_key . $export_id ), 0, 16 ) . '&export_id=' . $export_id . '&action=get_data' ); ?>
		</code>
		</p>
		<?php if ( ! $is_rapid_addon_export ) { ?>
			<p style="margin: 0 0 15px;">
			<h5 style="margin-bottom: 10px; margin-top: 10px; font-size: 14px;"><?php esc_html_e( 'Bundle URL' ); ?></h5>
			<code style="padding: 10px; border: 1px solid #ccc; display: block; width: 90%;">
				<?php echo esc_url( site_url() . '/wp-load.php?security_token=' . substr( md5( $cron_job_key . $export_id ), 0, 16 ) . '&export_id=' . $export_id . '&action=get_bundle' ); ?>
			</code>
			</p>
		<?php } ?>
		<p style="margin:0; padding-left: 0;"><?php esc_html_e( 'Read more about manual scheduling' ); ?>: <a
				target="_blank"
				href="http://www.wpallimport.com/documentation/recurring/cron/">
				http://www.wpallimport.com/documentation/recurring/cron/</a>
		</p>
	</div>
</div>