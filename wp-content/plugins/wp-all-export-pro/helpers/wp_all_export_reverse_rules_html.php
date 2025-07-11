<?php

if ( ! function_exists( 'wp_all_export_reverse_rules_html' ) ) {
	function wp_all_export_reverse_rules_html( $filter_rules_hierarhy, $parent_rule, &$rulenumber, $condition_labels ) {
		$child_rules = array();
		foreach ( $filter_rules_hierarhy as $j => $rule ) {
			if ( $rule->parent_id == $parent_rule->item_id and $rule->item_id != $parent_rule->item_id ) {
				$child_rules[] = $rule;
			}
		}

		if ( ! empty( $child_rules ) ) {
			?>
			<ol>
				<?php
				foreach ( $child_rules as $rule ) {

					$condition_label = in_array( $rule->element, array(
						'post_date',
						'user_registered',
						'comment_date',
					) ) ? $condition_labels['date'][ $rule->condition ] : $condition_labels['default'][ $rule->condition ];

					$rulenumber ++;

					?>
					<li id="item_<?php echo intval( $rulenumber ); ?>" class="dragging">
						<div class="drag-element">
							<input type="hidden" value="<?php echo esc_attr( $rule->element ); ?>"
							       class="wp_all_export_xml_element"
							       name="wp_all_export_xml_element[<?php echo intval( $rulenumber ); ?>]" />
							<input type="hidden" value="<?php echo esc_attr( $rule->condition ); ?>"
							       class="wp_all_export_rule"
							       name="wp_all_export_rule[<?php echo intval( $rulenumber ); ?>]" />
							<input type="hidden" value="<?php echo esc_attr( $rule->value ); ?>"
							       class="wp_all_export_value"
							       name="wp_all_export_value[<?php echo intval( $rulenumber ); ?>]" />
							<span
								class="rule_element"><?php echo empty( $rule->title ) ? esc_html( $rule->element ) : esc_html( $rule->title ); ?></span>
							<span class="rule_as_is"><?php echo esc_html( $condition_label ); ?></span>
							<span class="rule_condition_value"><?php echo esc_html( $rule->value ); ?></span>
							<span
								class="condition <?php if ( $rulenumber == count( $filter_rules_hierarhy ) ): ?>last_condition<?php endif; ?>">
							<label for="rule_and_<?php echo intval( $rulenumber ); ?>">AND</label>
							<input id="rule_and_<?php echo intval( $rulenumber ); ?>" type="radio" value="and"
							       name="rule[<?php echo intval( $rulenumber ); ?>]"
							       <?php if ( $rule->clause == 'AND' ): ?>checked="checked"<?php endif; ?> class="rule_condition" />
							<label for="rule_or_<?php echo intval( $rulenumber ); ?>">OR</label>
							<input id="rule_or_<?php echo intval( $rulenumber ); ?>" type="radio" value="or"
							       name="rule[<?php echo intval( $rulenumber ); ?>]"
							       <?php if ( $rule->clause == 'OR' ): ?>checked="checked"<?php endif; ?> class="rule_condition" />
						</span>
						</div>
						<a href="javascript:void(0);" class="icon-item remove-ico"></a>
						<?php echo wp_all_export_reverse_rules_html( $filter_rules_hierarhy, $rule, $rulenumber, $condition_labels ); ?>
					</li>
					<?php
				}
				?>
			</ol>
			<?php
		}
	}
}