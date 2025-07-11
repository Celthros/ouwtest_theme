<h2>
	<?php _e( 'Cron Scheduling', 'wp-all-import-pro' ) ?>
</h2>

<?php if ( in_array( $item['type'], array( 'url', 'ftp', 'file' ) ) ): ?>

	<p>
		<?php _e( 'To schedule an import, you must create two cron jobs in your web hosting control panel. One cron job will be used to run the Trigger script, the other to run the Execution script.', 'wp-all-import-pro' ); ?>
	</p>

	<p>
		Trigger Script URL<br /><small>Run the trigger script when you want to update your import. Once per 24 hours is
			recommended.</small><br /><input style='width: 700px;' type='text'
		                                     value='<?php echo home_url() . '/wp-load.php?import_key=' . $cron_job_key . '&import_id=' . $id . '&action=trigger'; ?>' />
		<br /><br />

		Execution Script URL<br /><small>Run the execution script frequently. Once per two minutes is
			recommended.</small><br /><input style='width: 700px;' type='text'
		                                     value='<?php echo home_url() . '/wp-load.php?import_key=' . $cron_job_key . '&import_id=' . $id . '&action=processing'; ?>' /><br /><br />
	</p>


	<p><strong><?php _e( 'Trigger Script', 'wp-all-import-pro' ); ?></strong></p>

	<p><?php _e( 'Every time you want to schedule the import, run the trigger script.', 'wp-all-import-pro' ); ?></p>

	<p><?php _e( 'To schedule the import to run once every 24 hours, run the trigger script every 24 hours. Most hosts require you to use “wget” to access a URL. Ask your host for details.', 'wp-all-import-pro' ); ?></p>

	<p><i><?php _e( 'Example:', 'wp-all-import-pro' ); ?></i></p>

	<p>wget -q -O /dev/null
		"<?php echo home_url() . '/wp-load.php?import_key=' . $cron_job_key . '&import_id=' . $id . '&action=trigger'; ?>
		"</p>

	<p><strong><?php _e( 'Execution Script', 'wp-all-import-pro' ); ?></strong></p>

	<p><?php _e( 'The Execution script actually executes the import, once it has been triggered with the Trigger script.', 'wp-all-import-pro' ); ?></p>

	<p><?php _e( 'It processes in iteration (only importing a few records each time it runs) to optimize server load. It is recommended you run the execution script every 2 minutes.', 'wp-all-import-pro' ); ?></p>

	<p><?php _e( 'It also operates this way in case of unexpected crashes by your web host. If it crashes before the import is finished, the next run of the cron job two minutes later will continue it where it left off, ensuring reliability.', 'wp-all-import-pro' ); ?></p>

	<p><i><?php _e( 'Example:', 'wp-all-import-pro' ); ?></i></p>

	<p>wget -q -O /dev/null
		"<?php echo home_url() . '/wp-load.php?import_key=' . $cron_job_key . '&import_id=' . $id . '&action=processing'; ?>
		"</p>

	<p><strong><?php _e( 'Notes', 'wp-all-import-pro' ); ?></strong></p>

	<p>
		<?php _e( 'Your web host may require you to use a command other than wget, although wget is most common. In this case, you must asking your web hosting provider for help.', 'wp-all-import-pro' ); ?>
	</p>

	<p>
		See the <a href='https://www.wpallimport.com/documentation/cron/'>documentation</a> for more details.
	</p>

<?php else: ?>

	<p>
		<?php _e( 'To schedule this import with a cron job, you must use the "Download from URL" or "Use existing file" option on the Import Settings screen of WP All Import.', 'wp-all-import-pro' ); ?>
	</p>
	<p>
		<a href="<?php echo esc_url( add_query_arg( array( 'id'     => $item['id'],
		                                                   'action' => 'options',
		), $this->baseUrl ) ); ?>"><?php _e( 'Go to Import Settings now...', 'wp-all-import-pro' ); ?></a>
	</p>

<?php endif; ?>

<a href="http://soflyy.com/" target="_blank"
   class="wpallimport-created-by"><?php _e( 'Created by', 'wp-all-import-pro' ); ?> <span></span></a>
