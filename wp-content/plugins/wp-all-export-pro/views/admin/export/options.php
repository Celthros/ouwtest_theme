<?php
if(!defined('ABSPATH')) {
    die();
}
?>
<?php
$l10n = array(
		'confirm_and_run'    => esc_html__('Confirm & Run Export', 'wp_all_export_plugin'),
		'save_configuration' => esc_html__('Save Export Configuration', 'wp_all_export_plugin'),
	);
?>
<script type="text/javascript">	
	var wp_all_export_L10n = <?php echo json_encode($l10n); ?>;
</script>

<div class="wpallexport-step-4 wpallexport-export-options">
	
	<h2 class="wpallexport-wp-notices"></h2>

	<div class="wpallexport-wrapper">
		<h2 class="wpallexport-wp-notices"></h2>
		<div class="wpallexport-header">
			<div class="wpallexport-logo"></div>
			<div class="wpallexport-title">
				<h2><?php esc_html_e('Export Settings', 'wp_all_export_plugin'); ?></h2>
			</div>
			<div class="wpallexport-links">
				<a href="http://www.wpallimport.com/support/" target="_blank"><?php esc_html_e('Support', 'wp_all_export_plugin'); ?></a> | <a href="http://www.wpallimport.com/documentation/" target="_blank"><?php esc_html_e('Documentation', 'wp_all_export_plugin'); ?></a>
			</div>
		</div>
		<div class="clear"></div>		
	</div>			

	<table class="wpallexport-layout">
		<tr>
			<td class="left" style="width: 100%;">		
	
				<?php do_action('pmxe_options_header', $this->isWizard, $post); ?>
				
				<div class="ajax-console">					
					<?php if ($this->errors->get_error_codes()): ?>
						<?php $this->error() ?>
					<?php endif ?>					
				</div>				
										
				<div class="wpallexport-content-section" style="padding: 0 30px 0 0; overflow: hidden; margin-bottom: 0;">

					<div id="filtering_result" class="wpallexport-ready-to-go">																		
						<h3> &nbsp; </h3>
						<div class="wp_all_export_preloader"></div>
					</div>	
					<?php if ($this->isWizard): ?>
					<form class="confirm <?php echo ! $this->isWizard ? 'edit' : '' ?>" method="post" style="float:right;">
                        <div style="position: relative;" class="wpae-scheduling-status">

                            <div class="easing-spinner" style="position: absolute; top: 7px; left: 35px; display: none;">
                                <div class="double-bounce1"></div>
                                <div class="double-bounce2"></div>
                            </div>

                            <svg width="30" height="30" viewBox="0 0 1792 1792"
                                 xmlns="http://www.w3.org/2000/svg"
                                 style="fill: white; position: absolute; top: 14px; left: 15px; display: none;">
                                <path
                                        d="M1671 566q0 40-28 68l-724 724-136 136q-28 28-68 28t-68-28l-136-136-362-362q-28-28-28-68t28-68l136-136q28-28 68-28t68 28l294 295 656-657q28-28 68-28t68 28l136 136q28 28 28 68z"
                                        fill="white"/>
                            </svg>
                        </div>
						<?php wp_nonce_field('options', '_wpnonce_options') ?>
						<input type="hidden" name="is_submitted" value="1" />
                        <input type="hidden" name="record-count" class="wpae-record-count" value="0" />

                        <input style="padding:20px 50px 20px 50px;" type="submit" class="rad10 wp_all_export_confirm_and_run" value="<?php esc_html_e('Confirm & Run Export', 'wp_all_export_plugin') ?>" />
                    </form>
					<?php endif; ?>
				</div>					

				<div class="clear"></div>

				<form class="<?php echo ! $this->isWizard ? 'edit' : 'options' ?> choose-export-options" method="post" enctype="multipart/form-data" autocomplete="off" <?php echo ! $this->isWizard ? 'style="overflow:visible;"' : '' ?> id="wpae-options-form">

					<input type="hidden" class="hierarhy-output" name="filter_rules_hierarhy" value="<?php echo esc_html($post['filter_rules_hierarhy']);?>"/>
					
					<?php
					$selected_post_type = '';
					$addons = new \Wpae\App\Service\Addons\AddonService();

					if ($addons->isUserAddonActiveAndIsUserExport()):
						$selected_post_type = empty($post['cpt'][0]) ? 'users' : $post['cpt'][0];
					endif;
					if (XmlExportComment::$is_active):
						$selected_post_type = 'comments';
					endif;

					if (XmlExportEngine::get_addons_service()->isWooCommerceAddonActive() && XmlExportWooCommerceReview::$is_active):
                        $selected_post_type = 'shop_review';
					endif;

					if (empty($selected_post_type) and ! empty($post['cpt'][0]))
					{
						$selected_post_type = $post['cpt'][0];
					}				
					?>

					<input type="hidden" name="selected_post_type" value="<?php echo esc_attr($selected_post_type); ?>"/>
					<input type="hidden" name="export_type" value="<?php echo esc_attr($post['export_type']); ?>"/>
					<input type="hidden" name="taxonomy_to_export" value="<?php echo esc_attr($post['taxonomy_to_export']);?>">
					<input type="hidden" name="sub_post_type_to_export" value="<?php echo esc_attr($post['sub_post_type_to_export']);?>">
					<input type="hidden" name="wpml_lang" value="<?php echo empty(PMXE_Plugin::$session->wpml_lang) ? esc_attr($post['wpml_lang']) : esc_attr(PMXE_Plugin::$session->wpml_lang);?>" />
					<input type="hidden" id="export_variations" name="export_variations" value="<?php echo esc_attr(XmlExportEngine::getProductVariationMode());?>" />
                    <input type="hidden" name="record-count" class="wpae-record-count" value="0" />

					<?php \Wpae\Pro\Filtering\FilteringFactory::render_filtering_block( $engine, $this->isWizard, $post ); ?>
                    <?php
                    if(current_user_can(PMXE_Plugin::$capabilities)) {
                    ?>
                    <div class="wpallexport-collapsed wpallexport-section wpallexport-file-options closed wpallexport-scheduling" style="margin-top: -10px; margin-bottom: 10px; <?php if($post['enable_real_time_exports']) { ?> display: none; <?php } ?>">
                        <div id="scheduling-form-container">

                            <div class="wpallexport-content-section" style="padding-bottom: 15px; margin-bottom: 10px;">
                                <div class="wpallexport-collapsed-header" id="scheduling-options-header" style="padding-left: 25px;">
                                    <h3 id="scheduling-title" style="position: relative;">
										<?php esc_html_e('Scheduling Options', 'wp_all_export_plugin'); ?>
                                    </h3>
                                </div>

                                <div class="wpallexport-collapsed-content" style="padding: 0; height: auto; display: none;">
                                    <div class="wpallexport-collapsed-content-inner" style="padding-bottom: 0; overflow: auto;">
                        <?php
                        include(__DIR__ . "/../../../src/Scheduling/views/SchedulingUI.php");
                        ?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                        <?php
                    } else {
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function(){
                                jQuery('.wpae-save-button').on('click', function (e) {
                                    jQuery('#wpae-options-form').trigger('submit');
                                });
                            });
                        </script>
                    <?php
                    }
                    ?>
                    <?php include_once 'options/settings.php'; ?>
                    <?php wp_nonce_field('options', '_wpnonce_options') ?>
                    <input type="hidden" name="is_submitted" value="1" />
                </form>
                <div style="color: #425F9A; font-size: 14px; font-weight: bold; margin: 0 0 15px; line-height: 25px; text-align: center;">
                    <div id="no-subscription" style="display: none;">
                        <?php esc_html_e("Looks like you're trying out Automatic Scheduling!");?><br/>
                        <?php esc_html_e("Your Automatic Scheduling settings won't be saved without a subscription.");?>
                    </div>
                </div>
					<div class="wpallexport-submit-buttons" style="text-align: center; <?php if ($this->isWizard) { ?> height: 60px; <?php } ?> ">

						<?php if ($this->isWizard): ?>
                            <a href="<?php echo esc_url(apply_filters('pmxi_options_back_link', add_query_arg(['action'=>'template','_wpnonce_template' => wp_create_nonce('template')], $this->baseUrl), $this->isWizard)); ?>" class="back rad3"><?php esc_html_e('Back', 'wp_all_export_plugin') ?></a>
                            <?php include(__DIR__ . "/../../../src/Scheduling/views/SaveSchedulingButton.php"); ?>
						<?php else: ?>		
							<a href="<?php echo esc_url(apply_filters('pmxi_options_back_link', esc_url_raw(remove_query_arg('id', remove_query_arg('action', $this->baseUrl)), $this->isWizard))); ?>" class="back rad3"><?php esc_html_e('Back to Manage Exports', 'wp_all_export_plugin') ?></a>
                            <?php include(__DIR__ . "/../../../src/Scheduling/views/SaveSchedulingButton.php"); ?>
						<?php endif ?>
					</div>
                <div style="clear: both;"></div>
                <a href="http://soflyy.com/" target="_blank" class="wpallexport-created-by"><?php esc_html_e('Created by', 'wp_all_export_plugin'); ?> <span></span></a>
			</td>
		</tr>
	</table>


</div>

<div class="wpallexport-overlay"></div>
