<div id="post-preview" class="wpallimport-preview_prices">

	<div class="title">
		<div class="navigation">
			<?php if ( $tagno > 1 ): ?><a href="#prev" class="previous_element">&nbsp;</a><?php else: ?><span
				class="previous_element">&nbsp;</span><?php endif ?>
			<?php printf( __( '<strong><input type="text" value="%s" name="tagno" class="tagno"/></strong><span class="out_of"> of <strong class="pmxi_count">%s</strong></span>', 'wp-all-import-pro' ), $tagno, PMXI_Plugin::$session->count ); ?>
			<?php if ( $tagno < PMXI_Plugin::$session->count ): ?><a href="#next"
			                                                         class="next_element">&nbsp;</a><?php else: ?><span
				class="next_element">&nbsp;</span><?php endif ?>
		</div>
	</div>
	
	<div class="wpallimport-preview-content">
		<?php if ( $this->errors->get_error_codes() ): ?>
			<?php $this->error() ?>
		<?php endif ?>

		<h3><?php _e( 'Preview Prices', 'wp-all-import-pro' ); ?></h3>
		
		<p><?php _e( 'Regular Price', 'wp-all-import-pro' ); ?>
			: <?php echo empty( $product_regular_price ) ? '' : wp_all_import_filter_html_kses( $product_regular_price ); ?></p>
		<p><?php _e( 'Sale Price', 'wp-all-import-pro' ); ?>
			: <?php echo empty( $product_sale_price ) || ! empty( $product_regular_price ) && $product_sale_price > $product_regular_price ? '' : wp_all_import_filter_html_kses( $product_sale_price ); ?></p>

	</div>

</div>