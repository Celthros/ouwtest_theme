/**
 * plugin admin area javascript
 */
(function ($, EventService) {
	$(function () {

		// Applies automatic formatting to the specified range
		wp.CodeMirror.defineExtension("autoFormatRange", function (from, to) {
			var cm = this;
			var outer = cm.getMode(), text = cm.getRange(from, to).split("\n");
			var state = CodeMirror.copyState(outer, cm.getTokenAt(from).state);
			var tabSize = cm.getOption("tabSize");

			var out = "", lines = 0, atSol = from.ch == 0;

			function newline() {
				out += "\n";
				atSol = true;
				++lines;
			}

			for (var i = 0; i < text.length; ++i) {
				var stream = new CodeMirror.StringStream(text[i], tabSize);
				while (!stream.eol()) {
					var inner = CodeMirror.innerMode(outer, state);
					var style = outer.token(stream, state), cur = stream.current();
					stream.start = stream.pos;
					if (!atSol || /\S/.test(cur)) {
						out += cur;
						atSol = false;
					}
					if (!atSol && inner.mode.newlineAfterToken &&
						inner.mode.newlineAfterToken(style, cur, stream.string.slice(stream.pos) || text[i + 1] || "", inner.state))
						newline();
				}
				if (!stream.pos && outer.blankLine) outer.blankLine(state);
				if (!atSol) newline();
			}

			cm.operation(function () {
				cm.replaceRange(out, from, to);
				for (var cur = from.line + 1, end = from.line + lines; cur <= end; ++cur)
					cm.indentLine(cur, "smart");
				cm.setSelection(from, cm.getCursor(false));
			});
		});

		// Applies automatic mode-aware indentation to the specified range
		wp.CodeMirror.defineExtension("autoIndentRange", function (from, to) {
			var cmInstance = this;
			this.operation(function () {
				for (var i = from.line; i <= to.line; i++) {
					cmInstance.indentLine(i, "smart");
				}
			});
		});

		var vm = {
			'preiviewText': '',
			'isGoogleMerchantsExport': false,
			'isWoocommerceOrderExport': function () {
				return $('#woo_commerce_order').length;
			},
			'isCSVExport': function () {
				return $('input[name=export_to]').val() === 'csv';
			},
			'isProductVariationsExport': function () {
				return this.hasVariations;
			},
			'hasVariations': false,
			'availableDataSelector': $('.right.template-sidebar .wpae_available_data'),
			'availableDataSelectorInModal': $('fieldset.optionsset .wpae_available_data')
		};

		function processElementName($element, $elementName) {
			if ($element.find('input[name^=cc_type]').val().indexOf('image_') !== -1) {
				$elementName = 'Image ' + $elementName;
			}
			if ($element.find('input[name^=cc_type]').val().indexOf('attachment_') !== -1) {
				$elementName = 'Attachment ' + $elementName;
			}
			return $elementName;
		}

		function selectSpreadsheet() {
			vm.isGoogleMerchantsExport = false;
			if (vm.availableDataSelector.css('position') == 'fixed') {
				$('.template-sidebar').find('.wpae_available_data').css({'position': 'static', 'top': '50px'});
			}
			resetDraggable();
			angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsDeselected');
			$('.wpallexport-custom-xml-template').slideUp();
			$('.wpallexport-simple-xml-template').slideDown();
			$('.wpallexport-csv-options').show();
			$('.wpallexport-xml-options').hide();

			$('.wpallexport-csv-advanced-options').css('display', 'block');
			$('.wpallexport-xml-advanced-options').css('display', 'none');

			if ($('#export_to_sheet').val() === 'csv') {
				$('.csv_delimiter').show();
			} else {
				$('.csv_delimiter').hide();
			}

			$('input[name=export_to]').val('csv');

			var isWooCommerceOrder = vm.isWoocommerceOrderExport();

			if ($('#export_to_sheet').val() !== 'csv') {
				if (isWooCommerceOrder || vm.isProductVariationsExport()) {
					$('.csv_delimiter').hide();
					$('.export_to_csv').show();
				} else {
					$('.export_to_csv').hide();
				}
			} else {
				/** isProductVariationsExport */
				if (isWooCommerceOrder) {
					$('.export_to_csv').show();
				} else {
					$('.export_to_csv').show();
					$('.csv_delimiter').show();
				}
			}
		}

		function selectFeed() {
			$('.wpallexport-csv-options').hide();
			$('.wpallexport-xml-options').show();
			$('input[name=export_to]').val('xml');
			$('.xml_template_type').trigger('change');

			$('.wpallexport-csv-advanced-options').css('display', 'none');
			$('.wpallexport-xml-advanced-options').css('display', 'block');
		}

		var currentLine = -1;

		var dragHelper = function (e, ui) {


			var isEditingField = $('#combine_multiple_fields_data').find(e.currentTarget).length;

			if (!vm.isGoogleMerchantsExport && !isEditingField) {
				return $(this).clone().css("pointer-events", "none").css('z-index', '99999999999999999').appendTo("body").show();
			}
			if (!$(this).find('.custom_column').length && !isEditingField) {
				return $(this).clone().css("pointer-events", "none").css('z-index', '999999999999999999').appendTo("body").show();
			}

			var elementName = $(this).find('.custom_column').find('input[name^=cc_name]').val();
			elementName = helpers.sanitizeElementName(elementName);
			elementName = processElementName($(this), elementName);

			return $('<div>{' + elementName + '}</div>').css("pointer-events", "none").css('z-index', 9999999999999999).appendTo("body").show();

		};

		var onDrag = function (e, ui) {
			var exportType = $('select.xml_template_type').val();

			if (exportType == 'custom' && isDraggingOverTextEditor(e)) {
				xml_editor.codemirror.focus();

				if (ui.helper.find('.custom_column').length) {
					var $elementName = ui.helper.find('.custom_column').find('input[name^=cc_name]').val();

					var $elementValue = $elementName;
					$elementName = helpers.sanitizeElementName($elementName);

					if (!ui.helper.find('.custom_column').hasClass('wp-all-export-custom-xml-drag-over')) ui.helper.find('.custom_column').addClass('wp-all-export-custom-xml-drag-over');
					ui.helper.find('.custom_column').find('.wpallexport-xml-element').html("&lt;" + $elementName.replace(/ /g, '') + "&gt;<span>{" + $elementValue + "}</span>&lt;/" + $elementName.replace(/ /g, '') + "&gt;");
				}
				if (ui.helper.find('.default_column').length) {
					var $elementName = ui.helper.find('.default_column').find('.wpallexport-element-label').html();
					if (!ui.helper.find('.default_column').hasClass('wp-all-export-custom-xml-drag-over')) ui.helper.find('.default_column').addClass('wp-all-export-custom-xml-drag-over');
				}

				var line = xml_editor.codemirror.lineAtHeight(ui.position.top, 'page');
				var ch = xml_editor.codemirror.coordsChar(ui.position, 'page');

				if (line == currentLine) {
					return;
				}

				if (currentLine != -1) {
					removeLine(currentLine);
				}

				currentLine = line;

				addLine("\n", line);

				xml_editor_doc.setCursor({line: line, ch: ch.ch});
			}

		};

		function isDraggingOverTextEditor(event) {
			var e = event.originalEvent.originalEvent.target;
			return $.contains(xml_editor.codemirror.display.scroller, e)
		}

		function addLine(str, line, ch) {
			if (typeof ch === 'undefined') {
				ch = 0;
			}
			xml_editor.codemirror.replaceRange(str, {line: line, ch: 0}, {line: line, ch: 0});
		}

		function removeLine(line) {
			xml_editor.codemirror.replaceRange("", {line: line, ch: 0}, {line: line + 1, ch: 0});
		}

		var initDraggable = function () {
			function initGeneralDraggable($element) {
				$element.find("li:not(.available_sub_section)").draggable({
					appendTo: "body",
					containment: "document",
					helper: dragHelper,
					drag: onDrag,
					start: function () {
						$('.google-merchants-droppable').css('cursor', 'copy');
						$('#columns').css('cursor', 'copy');
						$('.CodeMirror-lines').css('cursor', 'copy');
						$('#combine_multiple_fields_value').css('cursor', 'copy');
					},
					stop: function () {
						$('#columns').css('cursor', 'initial');
						$('.CodeMirror-lines').css('cursor', 'text');
						$('.google-merchants-droppable').css('cursor', 'initial');
						$('#combine_multiple_fields_value').css('cursor', 'initial');
					}
				});
			}

			initGeneralDraggable(vm.availableDataSelector);
			initGeneralDraggable(vm.availableDataSelectorInModal);
		};

		var resetDraggable = function () {

			var $draggableSelector = vm.availableDataSelector.find("li:not(.available_sub_section)");

			if ($draggableSelector.data('ui-draggable')) {
				$draggableSelector.draggable('destroy');
			}

			initDraggable();
		};

		initDraggable();

		$('.export_variations').on('change', function () {
			setTimeout(liveFiltering, 200);
			$('.wp-all-export-product-bundle-warning').hide();
			if ($(this).val() == 3) {
				$('.warning-only-export-parent-products').show();
			}
			if ($(this).val() == 2) {
				$('.warning-only-export-product-variations').show();
			}
		});

		var helpers = {
			'sanitizeElementName': function ($elementName) {
				if ($elementName.indexOf('(per tax)') !== false) {
					$elementName = $elementName.replace('(per tax)', 'PerTax');
					$elementName = $elementName.replace('(per coupon)', 'PerCoupon');
					$elementName = $elementName.replace('(per surcharge)', 'PerSurcharge');
					$elementName = $elementName.replace('/', '');
				}

				return $elementName;
			}
		};

		if (!$('body.wpallexport-plugin').length) return; // do not execute any code if we are not on plugin page

		$('.wp_all_export_send_to_codebox').on('click', async function (event) {

			$target = $(event.target);

			var isCodeBoxActive = $('input[name="is_wp_codebox_active"]').val();

			if (isCodeBoxActive === '0') {
				$('.cross-sale-notice.codebox').slideDown();
			} else {
				if ($target.hasClass('wp_all_export_code')) {
					var code = editor.codemirror.getValue();
					;
				} else {
					var code = main_editor.codemirror.getValue();
				}

				await wpae_save_functions(code);

				$('.wp_all_export_functions_preloader').show();

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpae_send_to_codebox',
						security: wp_all_export_security,
						code: code
					},
					dataType: 'json',
					success: function (response) {
						$('.functions_editor_container').slideUp(300, function () {
							$(this).html(response.html).fadeIn(300, function () {
								const $element = $(this);
								setTimeout(() => {
									$element.fadeOut(300);
								}, 3000);
							});
						});

						$('.wpae_function_editor_buttons').fadeOut(400);
						$('.wpae_go_to_codebox').fadeIn(400);
					},
					error: function () {
						alert('An error occurred while sending to CodeBox.');
					},
					complete: function () {
						$('.wp_all_export_functions_preloader').hide();
					}
				});
			}
		});

		$('.wp_all_export_revert_functions').on('click', function () {
			if (confirm('Are you sure you want to revert to the previous functions file?')) {

				$('.wp_all_export_functions_preloader').show();

				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'wpae_send_to_codebox',
						security: wp_all_export_security,
						codeboxaction: 'revert'
					},
					dataType: 'json',
					success: function (response) {
						alert(response.html);
						location.reload();
					},
					error: function () {
						alert('An error occurred while reverting the functions file.');
					},
					complete: function () {
						$('.wp_all_export_functions_preloader').hide();
					}
				});
			}
		});


		// fix layout position
		setTimeout(function () {
			$('table.wpallexport-layout').length && $('table.wpallexport-layout td.left h2:first-child').css('margin-top', $('.wrap').offset().top - $('table.wpallexport-layout').offset().top);
		}, 10);

		// help icons
		$('.wpallexport-help').parent().tipsy({
			gravity: function () {
				var ver = 'n';
				if ($(document).scrollTop() < $(this).offset().top - $('.tipsy').height() - 2) {
					ver = 's';
				}
				var hor = '';
				if ($(this).offset().left + $('.tipsy').width() < $(window).width() + $(document).scrollLeft()) {
					hor = 'w';
				} else if ($(this).offset().left - $('.tipsy').width() > $(document).scrollLeft()) {
					hor = 'e';
				}
				return ver + hor;
			},
			html: true,
			live: '.wpallexport-help',
			opacity: 1
		}).on('click', function () {
			return false;
		}).each(function () { // fix tipsy title for IE
			$(this).attr('original-title', $(this).attr('title'));
			$(this).removeAttr('title');
		});

		if ($('#wp_all_export_code').length) {

			var editor = wp.codeEditor.initialize($('#wp_all_export_code'), wpae_cm_settings);
			editor.codemirror.setCursor(1);

			$('.CodeMirror').resizable({
				resize: function () {
					editor.codemirror.setSize("100%", $(this).height());
				}
			});
		}

		if ($('#wp_all_export_custom_xml_template').length) {

			var xml_editor = wp.codeEditor.initialize(document.getElementById("wp_all_export_custom_xml_template"), {
				lineNumbers: true,
				matchBrackets: true,
				mode: "xml",
				indentUnit: 4,
				indentWithTabs: true,
				lineWrapping: true,
				autoRefresh: true
			});

			xml_editor.codemirror.setCursor(1);

			$('.CodeMirror').resizable({
				resize: function () {
					xml_editor.codemirror.setSize("100%", $(this).height());
				}
			});

			var xml_editor_doc = xml_editor.codemirror.getDoc();

		}

		if ($('#wp_all_export_main_code').length) {

			var main_editor = wp.codeEditor.initialize($('#wp_all_export_main_code'), wpae_cm_settings);
			main_editor.codemirror.setCursor(1);

			$('.CodeMirror').resizable({
				resize: function () {
					main_editor.codemirror.setSize("100%", $(this).height());
				}
			});
		}

		// swither show/hide logic
		$('input.switcher').on('change', function (e) {

			if ($(this).is(':radio:checked')) {
				$(this).parents('form').find('input.switcher:radio[name="' + $(this).attr('name') + '"]').not(this).trigger('change');
			}
			var $switcherID = $(this).attr('id');

			var $targets = $('.switcher-target-' + $switcherID);

			var is_show = $(this).is(':checked');
			if ($(this).is('.switcher-reversed')) is_show = !is_show;
			if (is_show) {
				$targets.fadeIn('fast', function () {
					if ($switcherID == 'coperate_php') {
						editor.codemirror.setCursor(1);
					}
				});
			} else {
				$targets.hide().find('.clear-on-switch').add($targets.filter('.clear-on-switch')).val('');
			}
		}).trigger('change');


		$('#enable_real_time_exports').on('change', function (e) {

			if ($(this).is(':checked')) {

				$('.wpallexport-scheduling').slideUp({queue: false});
				$('.wpallexport_realtime_show_bom').slideDown(function () {

				});
				$('.wpallexport-no-realtime-options').slideUp();

				//$('#filtering_result h4').html('WP All Export will export the most recent ' + $('#wpae-post-name').val() + '.');
				liveFiltering(false);

			} else {

				$('.wpallexport-scheduling').slideDown(function () {

				});
				$('.wpallexport_realtime_show_bom').slideUp();
				$('.wpallexport-no-realtime-options').slideDown({queue: false});

				liveFiltering(false);
			}

		});

		$('.wpae-switch-real-time').on('click', function () {

			var $link = $(this);

			var request = {
				action: 'wpae_realtime_export_status',
				data: $(this).data('item-id'),
				security: wp_all_export_security
			};

			$.ajax({
				type: 'POST',
				url: get_valid_ajaxurl(),
				data: request,
				success: function (response) {
					if ($link.html() === 'Enable Export') {
						$link.html('Disable Export');
						$link.parents('tr').find('.wpae-rte-enabled').show();
						$link.parents('tr').find('.wpae-rte-disabled').hide();

					} else {
						$link.html('Enable Export');
						$link.parents('tr').find('.wpae-rte-enabled').hide();
						$link.parents('tr').find('.wpae-rte-disabled').show();
					}

				},
				dataType: "json"
			});

			return false;
		});

		$('#wpae-generate-token').on('click', function () {


			if ($(this).find('span').html() === 'Generate') {
				var currentToken = $('#wpae-secure-url').val();

				if (currentToken) {
					$('#wpae-secure-url').val('');
				}

				$(this).css('background-color', '#F0F0F1');
				$(this).find('img').show();
				$(this).find('span').hide();

				var request = {
					action: 'wpae_generate_token',
					data: $(this).data('id'),
					security: wp_all_export_security
				};

				setTimeout(function () {

					$.ajax({
						type: 'POST',
						url: get_valid_ajaxurl(),
						data: request,
						success: function (response) {
							$('#wpae-generate-token').css('background-color', '#435F9A');
							$('#wpae-generate-token').find('img').hide();
							$('#wpae-generate-token').find('span').show();
							$('#wpae-secure-url').val(response);
							$('#wpae-generate-token span').html('Remove URL');

						}
					});

				}, 250);
			} else {


				var removeRequest = {
					action: 'wpae_remove_token',
					data: $(this).data('id'),
					security: wp_all_export_security
				};

				setTimeout(function () {

					$.ajax({
						type: 'POST',
						url: get_valid_ajaxurl(),
						data: removeRequest,
						success: function (response) {
							$('#wpae-secure-url').val('');
							$('#wpae-generate-token span').html('Generate');
						}
					});

				}, 250);

			}

		});

		// swither show/hide logic
		$('input.switcher-horizontal').on('change', function (e) {

			if ($(this).is(':checked')) {
				$(this).parents('form').find('input.switcher-horizontal[name="' + $(this).attr('name') + '"]').not(this).trigger('change');
			}
			var $targets = $('.switcher-target-' + $(this).attr('id'));

			var is_show = $(this).is(':checked');
			if ($(this).is('.switcher-reversed')) is_show = !is_show;

			if (is_show) {
				$targets.animate({width: '205px'}, 350);
			} else {
				$targets.animate({width: '0px'}, 1000).find('.clear-on-switch').add($targets.filter('.clear-on-switch')).val('');
			}
		}).trigger('change');

		// autoselect input content on click
		$(document).on('click', 'input.selectable', function () {
			$(this).select();
		});

		$('.pmxe_choosen').each(function () {
			$(this).find(".choosen_input").select2({tags: $(this).find('.choosen_values').html().split(',')});
		});

		// choose file form: option selection dynamic
		// options form: highlight options of selected post type
		$('form.choose-post-type input[name="type"]').on('click', function () {
			var $container = $(this).parents('.file-type-container');
			$('.file-type-container').not($container).removeClass('selected').find('.file-type-options').hide();
			$container.addClass('selected').find('.file-type-options').show();
		}).filter(':checked').trigger('click');

		$('.wpallexport-collapsed').each(function () {

			if (!$(this).hasClass('closed')) $(this).find('.wpallexport-collapsed-content:first').slideDown();

		});

		$(document).on('click', '.wpallexport-collapsed .wpallexport-collapsed-header:not(.disable-jquery)', function () {

			var $parent = $(this).parents('.wpallexport-collapsed:first');

			if ($parent.hasClass('closed')) {
				$parent.find('hr').show();
				$parent.removeClass('closed');
				$parent.find('.wpallexport-collapsed-content:first').slideDown(400, function () {
					if ($('#wp_all_export_main_code').length) {
						main_editor.codemirror.setCursor(1);
					}
					if ($('#wp_all_export_custom_xml_template').length) {
						xml_editor.codemirror.setCursor(1);
					}
				});
			} else {
				$parent.addClass('closed');
				$parent.find('.wpallexport-collapsed-content:first').slideUp();
				$parent.find('hr').hide();
			}
		});

		// [ Helper functions ]

		var get_valid_ajaxurl = function () {
			var $URL = ajaxurl;
			if (typeof export_id != "undefined") {
				if ($URL.indexOf("?") == -1) {
					$URL += '?id=' + export_id;
				} else {
					$URL += '&id=' + export_id;
				}
			}
			return $URL;
		}

		// generate warning on a fly when required fields deleting from the export template
		var trigger_warnings = function () {

			var missing_fields = ['id'];

			if ($('#is_product_export').length) missing_fields = missing_fields.concat(['_sku', 'product_type', 'parent']);
			if ($('#is_wp_query').length) missing_fields.push('post_type');

			$('#columns').find('li:not(.placeholder)').each(function (i, e) {
				$(this).find('div.custom_column:first').attr('rel', i + 1);

				if ($(this).find('input[name^=cc_type]').val() == 'id') {
					var index = missing_fields.indexOf('id');
					if (index > -1) {
						missing_fields.splice(index, 1);
					}
				}
				if ($(this).find('input[name^=cc_label]').val() == '_sku') {
					var index = missing_fields.indexOf('_sku');
					if (index > -1) {
						missing_fields.splice(index, 1);
					}

				}
				if ($(this).find('input[name^=cc_label]').val() == 'product_type') {
					var index = missing_fields.indexOf('product_type');
					if (index > -1) {
						missing_fields.splice(index, 1);
					}
				}
				if ($(this).find('input[name^=cc_label]').val() == 'parent') {
					var index = missing_fields.indexOf('parent');
					if (index > -1) {
						missing_fields.splice(index, 1);
					}
				}
				if ($(this).find('input[name^=cc_label]').val() == 'post_type') {
					var index = missing_fields.indexOf('post_type');
					if (index > -1) {
						missing_fields.splice(index, 1);
					}
				}
			});

			if (missing_fields.length) {
				var fields = '';
				switch (missing_fields.length) {
					case 1:
						fields = missing_fields.shift();
						break;
					case 2:
						fields = missing_fields.join(" and ");
						break;
					default:
						var latest_field = missing_fields.pop();
						fields = missing_fields.join(", ") + ", and " + latest_field;
						break;
				}

				var warning_template = $('#warning_template').length ? $('#warning_template').val().replace("%s", fields) : '';

				var is_dismiss_warnings = parseInt($('#dismiss_warnings').val());

				if (!is_dismiss_warnings && !$('#pmxe_dismiss_import_warnings_by_default').length) {
					$('.wp-all-export-warning').find('p').html(warning_template);
					$('.wp-all-export-warning').show();
				}
			} else {
				$('.wp-all-export-warning').hide();
			}
		}

		// Get a valid filtering rules for selected field type
		var init_filtering_fields = function () {

			var wp_all_export_rules_config = {
				'#wp_all_export_xml_element': {width: "98%"},
				'#wp_all_export_rule': {width: "98%"}
			};

			for (var selector in wp_all_export_rules_config) {

				$(selector).chosen(wp_all_export_rules_config[selector]);

				if (selector == '#wp_all_export_xml_element') {

					$(selector).on('change', function (evt, params) {

						$('#wp_all_export_available_rules').html('<div class="wp_all_export_preloader" style="display:block;"></div>');

						var date_fields = ['post_date', 'post_modified', 'comment_date', 'comment_parent_date', 'comment_parent_date_gmt', 'user_registered', 'cf__completed_date', 'product_date', '_date_paid'];

						if (date_fields.indexOf(params.selected) > -1) {
							$('#date_field_notice').show();
						} else {
							$('#date_field_notice').hide();
						}

						var type = $(evt.target).find(":selected").data("type");

						var request = {
							action: 'wpae_available_rules',
							data: {
								'selected': params.selected,
								'type': type,
							},
							security: wp_all_export_security
						};

						$.ajax({
							type: 'POST',
							url: ajaxurl,
							data: request,
							success: function (response) {
								$('#wp_all_export_available_rules').html(response.html);
								$('#wp_all_export_rule').chosen({width: "98%"});
								$('#wp_all_export_rule').on('change', function (evt, params) {
									if (params.selected == 'is_empty' || params.selected == 'is_not_empty')
										$('#wp_all_export_value').hide();
									else
										$('#wp_all_export_value').show();
								});
							},
							dataType: "json"
						});
					});
				}
			}

			$('.wp_all_export_filtering_rules').pmxe_nestedSortable({
				handle: 'div',
				items: 'li.dragging',
				toleranceElement: '> div',
				update: function () {
					$('.wp_all_export_filtering_rules').find('.condition').removeClass('last_condition').show();
					$('.wp_all_export_filtering_rules').find('.condition:last').addClass('last_condition');
					liveFiltering();
				}
			});

		};

		var is_first_load = true;

		var filtering = function (postType) {

			// Allow add-ons to disable filters
			if (window.wpaeFiltersDisabled) {
				return false;
			}

			var is_preload = $('.wpallexport-preload-post-data').val();
			var filter_rules_hierarhy = parseInt(is_preload) ? $('input[name=filter_rules_hierarhy]').val() : '';

			$('.wpallexport-preload-post-data').val(0);

			var request = {
				action: 'wpae_filtering',
				data: {
					'cpt': postType,
					'export_type': 'specific',
					'filter_rules_hierarhy': filter_rules_hierarhy,
					'product_matching_mode': 'strict',
					'taxonomy_to_export': $('input[name=taxonomy_to_export]').val(),
					'sub_post_type_to_export': $('input[name=sub_post_type_to_export]').val(),

				},
				security: wp_all_export_security
			};

			if (is_first_load == false || postType != '') $('.wp_all_export_preloader').show();

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: request,
				success: function (response) {

					$('.wp_all_export_preloader').hide();

					var export_type = $('input[name=export_type]').val();

					if (export_type == 'advanced') {
						$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
						$('.wpallexport-choose-file').find('.wp_all_export_continue_step_two').html(response.btns);
						$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').show();
					} else {
						if (postType != '') {

							$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').html(response.html);
							$('.wpallexport-choose-file').find('.wp_all_export_continue_step_two').html(response.btns);

							init_filtering_fields();
							liveFiltering(is_first_load);
						} else {
							$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
							$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').hide();
						}
					}

					is_first_load = false;

				},
				error: function (jqXHR, textStatus) {

					$('.wp_all_export_preloader').hide();

				},
				dataType: "json"
			});

		};

		window.wpae_filtering = filtering;

		var liveFiltering = function (first_load, after_filtering) {

			// serialize filters
			$('.hierarhy-output').each(function () {
				var sortable = $('.wp_all_export_filtering_rules.ui-sortable');
				if (sortable.length) {
					$(this).val(window.JSON.stringify(sortable.pmxe_nestedSortable('toArray', {startDepthCount: 0})));
				}
			});

			var postType = $('input[name=cpt]').length ? $('input[name=cpt]').val() : $('input[name=selected_post_type]').val();

			var $export_only_new_stuff = $('input[name=export_only_new_stuff]').val();
			if ($('#export_only_new_stuff').length) {
				$export_only_new_stuff = $('#export_only_new_stuff').is(':checked') ? 1 : 0;
			}

			var $export_only_modified_stuff = $('input[name=export_only_modified_stuff]').val();
			if ($('#export_only_modified_stuff').length) {
				$export_only_modified_stuff = $('#export_only_modified_stuff').is(':checked') ? 1 : 0;
			}

			var $export_only_customers_that_made_purchases = $('input[name=export_only_customers_that_made_purchases]').val();
			if ($('#export_only_customers_that_made_purchases').length) {
				$export_only_customers_that_made_purchases = $('#export_only_customers_that_made_purchases').is(':checked') ? 1 : 0;
			}

			// prepare data for ajax request to get post count after filtering
			var request = {
				action: 'wpae_filtering_count',
				data: {
					'cpt': postType,
					'filter_rules_hierarhy': $('input[name=filter_rules_hierarhy]').val(),
					'product_matching_mode': $('select[name=product_matching_mode]').length ? $('select[name=product_matching_mode]').val() : '',
					'is_confirm_screen': $('.wpallexport-step-4').length,
					'is_template_screen': $('.wpallexport-step-3').length,
					'export_only_new_stuff': $export_only_new_stuff,
					'export_only_modified_stuff': $export_only_modified_stuff,
					'export_only_customers_that_made_purchases': $export_only_customers_that_made_purchases,
					'export_type': $('input[name=export_type]').val(),
					'taxonomy_to_export': $('input[name=taxonomy_to_export]').val(),
					'sub_post_type_to_export': $('input[name=sub_post_type_to_export]').val(),
					'wpml_lang': $('input[name=wpml_lang]').val(),
					'export_variations': $('#export_variations').val(),
					'enable_real_time_exports': $('#enable_real_time_exports').is(':checked') ? '1' : '0'
				},
				security: wp_all_export_security
			};

			$('.wp_all_export_preloader').show();
			$('.wp_all_export_filter_preloader').show();

			$.ajax({
				type: 'POST',
				url: get_valid_ajaxurl(),
				data: request,
				success: function (response) {

					$('.wpae-record-count').val(response.found_records);

					vm.hasVariations = response.hasVariations;
					if (vm.hasVariations || $('#pmxe_woocommerce_product_addon_installed').length) {

						if ($('#export_to_sheet').val() == 'xls' || $('#export_to_sheet').val() == 'xlsx') {
							$('.csv_delimiter').hide();
							$('.export_to_csv').slideDown();
						}

						$('.product_variations').show();

					}

					$('.wp_all_export_filter_preloader').hide();

					$('#filtering_result').html(response.html);

					$('.wpallexport-choose-file').find('.wpallexport-filtering-wrapper').slideDown(400, function () {
						if (typeof first_load != 'undefined') {
							$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideDown();
							$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').addClass('closed');
							if (response.found_records) $('.wpallexport-choose-file').find('.wpallexport-submit-buttons').show();
						}
					});

					$('.wp_all_export_preloader').hide();

					if (typeof after_filtering != 'undefined') {
						after_filtering(response);
					}

					if ($('.wpallexport-step-4').length && typeof wp_all_export_L10n != 'undefined') {

						if (response.found_records || $('#enable_real_time_exports').is(':checked')) {
							$('.wp_all_export_confirm_and_run').show();
							$('.confirm_and_run_bottom').val(wp_all_export_L10n.confirm_and_run.replace('&amp;', '&'));
							$('#filtering_result').removeClass('nothing_to_export');
						} else {
							$('.wp_all_export_confirm_and_run').hide();
							$('.confirm_and_run_bottom').val(wp_all_export_L10n.save_configuration);
							$('#filtering_result').addClass('nothing_to_export');
						}
					}

					if ($('.wpallexport-step-3').length) {

						$('.founded_records').html(response.html);

						if (response.found_records || $('#enable_real_time_exports').length) {
							$('.founded_records').removeClass('nothing_to_export');
						} else {
							$('.founded_records').addClass('nothing_to_export');
						}
					}

					if ($('.wpallexport-step-1').length) {
						if (response.found_records) {
							$('.founded_records').removeClass('nothing_to_export');
							$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').show();
						} else {
							$('.founded_records').addClass('nothing_to_export');
							$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').hide();
						}
					}
				},
				error: function (jqXHR, textStatus) {

					$('.wp_all_export_filter_preloader').hide();
					$('.wp_all_export_preloader').hide();

				},
				dataType: "json"
			}).fail(function (xhr, textStatus, error) {
				$('div.error.inline').remove();
				$('.wpallexport-header').next('.clear').after("<div class='error inline'><p>" + textStatus + " " + error + "</p></div>");
			});

		}
		// [ \Helper functions ]


		// [ Step 1 ( chose & filter export data ) ]
		$('.wpallexport-step-1').each(function () {

			var $wrap = $('.wrap');

			var formHeight = $wrap.height();

			$('.wpallexport-import-from').on('click', function () {

				var showImportType = false;

				var postType = $('input[name=cpt]').val();

				switch ($(this).attr('rel')) {
					case 'specific_type':
						if (postType != '') {
							showImportType = true;
							$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideDown();
							$('.wpallexport-filtering-wrapper').show();
							if (postType == 'taxonomies') {

								$('.taxonomy_to_export_wrapper').slideDown();
							}

							if ((postType == 'users' || postType == 'shop_customer') && !($('#pmxe_user_addon_installed').length || $('#pmxe_user_addon_free_installed').length)) {
								$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideUp();

								$('.wpallexport-free-edition-notice').slideUp();

								if (postType == 'users') {
									$('.wpallexport-user-export-notice').slideDown();
								} else if (postType == 'shop_customer') {
									$('.wpallexport-customer-export-notice').slideDown();
								}
								$('.wpallexport-filtering-wrapper').slideUp();
								$('.wpallexport-submit-buttons .wp_all_export_btn_with_note').slideUp();
								$('.wpallexport-upload-resource-step-two').slideUp();
							} else {
								$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideDown();
								$('.wpallexport-free-edition-notice').slideUp();
							}
						} else {
							$('.taxonomy_to_export_wrapper').slideUp();
						}
						break;
					case 'advanced_type':

						if ($('#wp_query_selector .dd-selected-value').val() == 'wp_user_query' && !($('#pmxe_user_addon_installed').length || $('#pmxe_user_addon_free_installed').length)) {
							$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideUp();
							$('.wpallexport-user-export-notice').slideDown();
						} else {
							$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideDown();
							$('.wpallexport-free-edition-notice').slideUp();
							filtering();
						}

						$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
						showImportType = true;
						$('.wpallexport-filtering-wrapper').hide();
						break;
				}

				$('.wpallexport-import-from').removeClass('selected').addClass('bind');
				$(this).addClass('selected').removeClass('bind');
				$('.wpallexport-choose-file').find('.wpallexport-upload-type-container').hide();
				$('.wpallexport-choose-file').find('.wpallexport-upload-type-container[rel=' + $(this).attr('rel') + ']').show();
				$('.wpallexport-choose-file').find('input[name=export_type]').val($(this).attr('rel').replace('_type', ''));

				if (!showImportType) {
					$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').hide();
				} else {
					$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').show();
				}

			});

			$('.wpallexport-import-from.selected').trigger('click');

			window.wpaeFiltersDisabled = false;

			window.wpaeDisableFiltering = function () {
				window.wpaeFiltersDisabled = true;
			};

			window.wpaeEnableFiltering = function () {
				window.wpaeFiltersDisabled = false;
			};


			$('#file_selector').ddslick({
				width: 600,
				onSelected: function (selectedData) {

					function showNotice(notice) {
						$('.wpallexport-free-edition-notice').slideUp();
						$('.taxonomy_to_export_wrapper').slideUp();
						$(notice).slideDown();
						$('.wpallexport-filtering-wrapper').slideUp();
						$('.wpallexport-submit-buttons .wp_all_export_btn_with_note').slideUp();
						$('.wpallexport-upload-resource-step-two').slideUp();
					}

					if (selectedData.selectedData.value != "") {

						$('#file_selector').find('.dd-selected').css({'color': '#555'});

						var i = 0;
						var postType = selectedData.selectedData.value;
						$('#file_selector').find('.dd-option-value').each(function () {
							if (postType == $(this).val()) return false;
							i++;
						});

						$('.wpallexport-choose-file').find('input[name=cpt]').val(postType);
						$('.wpallexport-choose-file').find('input[name=cpt]').trigger("change");

						if (postType == 'taxonomies') {
							$('.wpallexport-free-edition-notice').slideUp();

							$('.taxonomy_to_export_wrapper').slideDown();
							if ($('input[name=taxonomy_to_export]').val() != '') {
								filtering(postType);
							} else {
								$('.wpallexport-choose-file').find('.wpallexport-filtering-wrapper').slideUp();
								$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
								$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').hide();
							}
						} else if ((postType == 'users') && !($('#pmxe_user_addon_installed').length || $('#pmxe_user_addon_free_installed').length)) {
							showNotice('.wpallexport-user-export-notice');
							return;
						} else if ((postType == 'shop_customer') && !$('#pmxe_user_addon_installed').length) {
							showNotice('.wpallexport-customer-export-notice');
							return;
						} else if (postType == 'product' && !$('#pmxe_woocommerce_addon_installed').length && !$('#pmxe_woocommerce_product_addon_installed').length && $('#WooCommerce_Installed').length) {
							showNotice('.wpallexport-product-export-notice');
							return;
						} else if (postType == 'shop_order' && !$('#pmxe_woocommerce_addon_installed').length && !$('#pmxe_woocommerce_order_addon_installed').length) {
							showNotice('.wpallexport-order-export-notice');
							return;
						} else if (postType == 'shop_coupon' && !$('#pmxe_woocommerce_addon_installed').length) {
							showNotice('.wpallexport-coupon-export-notice');
							return;
						} else if (postType == 'shop_review' && !$('#pmxe_woocommerce_addon_installed').length) {
							showNotice('.wpallexport-review-export-notice');
							return;
						} else {
							$('.wpallexport-free-edition-notice').slideUp();
							$('.taxonomy_to_export_wrapper').slideUp();
							filtering(postType);
						}

					} else {
						$('.taxonomy_to_export_wrapper').slideUp();
						$('.wpallexport-choose-file').find('input[name=cpt]').val('');
						$('#file_selector').find('.dd-selected').css({'color': '#cfceca'});
						$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
						$('.wpallexport-choose-file').find('.wpallexport-filtering-wrapper').slideUp();

						switch ($('.wpallexport-import-from.selected').attr('rel')) {
							case 'specific_type':
								filtering($('input[name=cpt]').val());
								break;
							case 'advanced_type':

								break;
						}
					}
				}
			});

			$(document).on('keyup', '.wp_query', function () {

				var value = $(this).val();

				if (!$('#pmxe_woocommerce_addon_installed').length) {

					if (value.indexOf('shop_order') === -1 && value.indexOf('product') === -1 && value.indexOf('shop_coupon') === -1) {
						$('.wpallexport-free-edition-notice').hide();
						$('.wpallexport-submit-buttons').show();
						return;
					}

					if (value.indexOf('shop_order') !== -1 && !$('#pmxe_woocommerce_order_addon_installed').length) {
						$('.wpallexport-order-export-notice').show();
						$('.wpallexport-submit-buttons').hide();
					}

					if (value.indexOf('product') !== -1 && !$('#pmxe_woocommerce_product_addon_installed').length && $('#WooCommerce_Installed').length) {
						$('.wpallexport-product-export-notice').show();
						$('.wpallexport-submit-buttons').hide();
					}

					if (value.indexOf('shop_coupon') !== -1) {
						$('.wpallexport-coupon-export-notice').show();
						$('.wpallexport-submit-buttons').hide();
					}
				}
			});

			$(document).on('click', 'a.auto-generate-template', function () {

				var export_type = $('input[name="cpt"]').val();

				if (export_type == 'shop_order' && !$('#woocommerce_add_on_pro_installed').length) {
					$('#migrate-orders-notice').slideDown();
					return false;
				}

				if (export_type == 'product' && !$('#woocommerce_add_on_pro_installed').length && $('#WooCommerce_Installed').length) {
					$('#migrate-products-notice').slideDown();
					return false;
				}

				$('input[name^=auto_generate]').val('1');

				$('.hierarhy-output').each(function () {
					var sortable = $('.wp_all_export_filtering_rules.ui-sortable');
					if (sortable.length) {
						$(this).val(window.JSON.stringify(sortable.pmxe_nestedSortable('toArray', {startDepthCount: 0})));
					}
				});

				$(this).parents('form:first').trigger('submit');
			});

			$('form.wpallexport-choose-file').find('input[type=submit]').on('click', function (e) {
				e.preventDefault();

				$('.hierarhy-output').each(function () {
					var sortable = $('.wp_all_export_filtering_rules.ui-sortable');
					if (sortable.length) {
						$(this).val(window.JSON.stringify(sortable.pmxe_nestedSortable('toArray', {startDepthCount: 0})));
					}
				});

				$(this).parents('form:first').trigger('submit');
			});

			$('#wp_query_selector').ddslick({
				width: 600,
				onSelected: function (selectedData) {

					if (selectedData.selectedData.value != "") {

						$('#wp_query_selector').find('.dd-selected').css({'color': '#555'});
						var queryType = selectedData.selectedData.value;

						if (queryType == 'wp_query') {
							$('textarea[name=wp_query]').attr("placeholder", "'post_type' => 'post', 'post_status' => array( 'pending', 'draft', 'future' )");
							$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideDown();
							$('.wpallexport-user-export-notice').slideUp();

						} else {

							if (queryType == 'wp_user_query' && !($('#pmxe_user_addon_free_installed').length || $('#pmxe_user_addon_installed').length)) {
								$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideUp();
								$('.wpallexport-user-export-notice').slideDown();
							} else {
								$('.wp_all_export_continue_step_two span.wp_all_export_btn_with_note').slideDown();
								$('.wpallexport-user-export-notice').slideUp();
							}
							$('textarea[name=wp_query]').attr("placeholder", "'role' => 'Administrator'");
						}
						$('input[name=wp_query_selector]').val(queryType);
					} else {

						$('#wp_query_selector').find('.dd-selected').css({'color': '#cfceca'});
						$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();

					}
				}
			});
			// Taxonomies Export
			$('#taxonomy_to_export').ddslick({
				width: 600,
				onSelected: function (selectedData) {

					if (selectedData.selectedData.value != "") {

						$('#taxonomy_to_export').find('.dd-selected').css({'color': '#555'});
						$('input[name=taxonomy_to_export]').val(selectedData.selectedData.value);
						filtering($('input[name=cpt]').val());
					} else {
						$('#taxonomy_to_export').find('.dd-selected').css({'color': '#cfceca'});
						$('.wpallexport-choose-file').find('.wpallexport-filtering-wrapper').slideUp();
						$('.wpallexport-choose-file').find('.wpallexport-upload-resource-step-two').slideUp();
						$('.wpallexport-choose-file').find('.wpallexport-submit-buttons').hide();
					}
				}
			});

			$('#taxonomy_to_export li').each(function () {
				var toolTipText = $(this).find('.dd-option-value').val();
				$(this).attr('title', toolTipText);
			});
		});
		// [ \Step 1 ( chose & filter export data ) ]


		// [ Step 2 ( export template ) ]
		$('.wpallexport-export-template').each(function () {

			trigger_warnings();

			var $sortable = $("#columns");

			var outsideContainer = 0;

			// this one control if the draggable is outside the droppable area
			$('#columns_to_export').droppable({
				accept: '.ui-sortable-helper'
			});

			$("#columns_to_export").on("dropout", function (event, ui) {
				outsideContainer = 1;
				ui.draggable.find('.custom_column').css('background', 'white');
			});

			$("#columns_to_export").on("dropover", function (event, ui) {
				outsideContainer = 0;
				ui.draggable.find('.custom_column').css('background', 'white');
			});

			// this one control if the draggable is dropped
			$('body, form.wpallexport-template').droppable({
				accept: '.ui-sortable-helper',
				drop: function (event, ui) {
					if (outsideContainer == 1) {
						ui.draggable.remove();
						trigger_warnings();

						if ($('#columns').find('li:not(.placeholder)').length === 1) {
							$('#columns').find(".placeholder").show();
						}
					} else {
						ui.draggable.find('.custom_column').css('background', 'none');
					}
				}
			});

			$("#columns_to_export ol").droppable({
				activeClass: "pmxe-state-default",
				hoverClass: "pmxe-state-hover",
				accept: ":not(.ui-sortable-helper)",
				drop: function (event, ui) {

					if (event.originalEvent.target.nodeName == 'TEXTAREA') {
						return;
					}
					$(this).find(".placeholder").hide();

					if (ui.draggable.find('input[name^=rules]').length) {
						$('li.' + ui.draggable.find('input[name^=rules]').val()).each(function () {
							var $value = $(this).find('input[name^=cc_value]').val();
							var $is_media_field = false;
							if ($(this).find('input[name^=cc_type]').val().indexOf('image_') !== -1 || $(this).find('input[name^=cc_type]').val().indexOf('attachment_') !== -1) {
								$value = $(this).find('input[name^=cc_type]').val();
								$is_media_field = true;
							}
							var $add_field = true;
							$('#columns').find('li').each(function () {
								if ($is_media_field) {
									if ($(this).find('input[name^=cc_type]').val() == $value) {
										$add_field = false;
									}
								} else {
									if ($(this).find('input[name^=cc_value]').val() == $value) {
										$add_field = false;
									}
								}
							});
							if ($add_field) {
								$("<li></li>").html($(this).html()).appendTo($("#columns_to_export ol"));
								var $just_added = $('#columns').find('li:last').find('div:first');
								$just_added.attr('rel', $('#columns').find('li:not(.placeholder)').length);
								if ($just_added.find('input[name^=cc_type]').val().indexOf('image_') !== -1) {
									$just_added.find('.wpallexport-xml-element').html('Image ' + $just_added.find('input[name^=cc_name]').val());
									$just_added.find('input[name^=cc_name]').val('Image ' + $just_added.find('input[name^=cc_name]').val());
								}
								if ($just_added.find('input[name^=cc_type]').val().indexOf('attachment_') !== -1) {
									$just_added.find('.wpallexport-xml-element').html('Attachment ' + $just_added.find('input[name^=cc_name]').val());
									$just_added.find('input[name^=cc_name]').val('Attachment ' + $just_added.find('input[name^=cc_name]').val());
								}
							}
						});
					} else {
						$("<li></li>").html(ui.draggable.html()).appendTo(this);
						var $just_added = $('#columns').find('li:last').find('div:first');
						$just_added.attr('rel', $('#columns').find('li:not(.placeholder)').length);
						if ($just_added.find('input[name^=cc_type]').val().indexOf('image_') !== -1) {
							$just_added.find('.wpallexport-xml-element').html('Image ' + $just_added.find('input[name^=cc_name]').val());
							$just_added.find('input[name^=cc_name]').val('Image ' + $just_added.find('input[name^=cc_name]').val());
						}
						if ($just_added.find('input[name^=cc_type]').val().indexOf('attachment_') !== -1) {
							$just_added.find('.wpallexport-xml-element').html('Attachment ' + $just_added.find('input[name^=cc_name]').val());
							$just_added.find('input[name^=cc_name]').val('Attachment ' + $just_added.find('input[name^=cc_name]').val());
						}
					}

					trigger_warnings();

				}
			}).sortable({
				items: "li:not(.placeholder)",
				start: function (event, ui) {
					ui.item.addClass('wpae-no-click');
				},
				sort: function () {
					// gets added unintentionally by droppable interacting with sortable
					// using connectWithSortable fixes this, but doesn't allow you to customize active/hoverClass options
					$(this).removeClass("ui-state-default");
				}
			});

			$(".CodeMirror-code").droppable({
				activeClass: "pmxe-template-state-default",
				hoverClass: "pmxe-template-state-hover",
				accept: ":not(.ui-sortable-helper)",
				drag: function (event, ui) {
				},
				drop: function (event, ui) {

					function getCodeToPlace($elementName) {
						var $elementValue = $elementName;
						$elementName = helpers.sanitizeElementName($elementName);
						return "<" + $elementName.replace(/ /g, '') + ">{" + $elementValue + "}</" + $elementName.replace(/ /g, '') + ">\n"
					}


					function replaceLineWithElements(content) {
						removeLine(currentLine);

						addLine(content, currentLine, currentLine);
						currentLine = -1;

						var totalLines = xml_editor.codemirror.lineCount();
						xml_editor.codemirror.autoIndentRange({line: 0, ch: 0}, {line: totalLines, ch: 100});
					}

					if ($('#available_data').find(ui.draggable.find('input[name^=rules]')).length) {
						var content = "";
						$($('#available_data').find('li.' + ui.draggable.find('input[name^=rules]').val())).each(function () {
							var $elementName = $(this).find('input[name^=cc_name]').val();
							$elementName = processElementName($(this), $elementName);
							content = content + getCodeToPlace($elementName);
						});

						replaceLineWithElements(content);
					} else {
						var $elementName = ui.draggable.find('.custom_column').find('input[name^=cc_name]').val();
						var $element = ui.draggable.find('.custom_column');
						$elementName = processElementName($element, $elementName);

						replaceLineWithElements(getCodeToPlace($elementName));
					}
				}
			});

			var $this = $(this);
			var $addAnother = $this.find('input.add_column');
			var $addAnotherForm = $('fieldset.wp-all-export-edit-column');
			var $template = $(this).find('.custom_column.template');

			if (typeof wpPointerL10n != "undefined") wpPointerL10n.dismiss = 'Close';

			// Add Another btn click
			$addAnother.on('click', function () {

				$addAnotherForm.find('form')[0].reset();
				$addAnotherForm.find('.column_name').val('ID');

				$addAnotherForm.find('input[name="combine_multiple_fields"][value="0"]').prop('checked', true).trigger('click');
				//$addAnotherForm.find('input[name="combine_multiple_fields"]').trigger('change');

				// Reset custom field
				$('#combine_multiple_fields_value_container').hide();
				$('#combine_multiple_fields_data').hide();
				$('.export-single').show();
				$('.single-field-options').show();
				$('.php_snipped').show();

				$addAnotherForm.removeAttr('rel');
				$addAnotherForm.removeClass('dc').addClass('cc');
				$addAnotherForm.find('.cc_field').hide();

				$addAnotherForm.find('.wpallexport-edit-row-title').hide();
				$addAnotherForm.find('.wpallexport-add-row-title').show();
				$addAnotherForm.find('div[class^=switcher-target]').hide();
				$addAnotherForm.find('#coperate_php').removeAttr('checked');
				$addAnotherForm.find('input.column_name').parents('div.input:first').show();

				$('.custom_column').removeClass('active');

				$addAnotherForm.find('select[name=column_value_type]').find('option').each(function () {
					if ($(this).val() == 'id')
						$(this).attr({'selected': 'selected'}).trigger('click');
					else
						$(this).removeAttr('selected');
				});

				$('.wp-all-export-chosen-select').trigger('chosen:updated');
				$('.wp_all_export_saving_status').removeClass('error updated').html('');

				$('.wpallexport-overlay').show();
				$addAnotherForm.find('input.switcher').trigger('change');
				$addAnotherForm.show();

			});

			// Delete custom column action
			$addAnotherForm.find('.delete_action').on('click', function () {

				$('.custom_column').removeClass('active');

				$('.custom_column[rel=' + $addAnotherForm.attr('rel') + ']').parents('li:first').fadeOut().remove();

				if (!$('#columns').find('li:visible').length) {
					$('#columns').find(".placeholder").show();
				}

				trigger_warnings();

				$addAnotherForm.fadeOut();
				$('.wpallexport-overlay').hide();
			});

			// Add/Edit custom column action
			$addAnotherForm.on('click', '.save_action', function () {

				if ($('.wp-all-export-field-options input[name="combine_multiple_fields"]').val() == '1') {
					if (!wpaeValidateBraces($('#combine_multiple_fields_value').val())) {
						return false;
					}

				}
				var $save = true;

				// element name in exported file
				var $elementName = $addAnotherForm.find('input.column_name');

				// element name validation
				if ($elementName.val() == '') {
					$save = false;
					$elementName.addClass('error');
					return false;
				}

				// get PHP function name
				var $phpFunction = $addAnotherForm.find('.php_code:visible');

				// validation passed, prepare field data
				var $elementIndex = $addAnotherForm.attr('rel');
				// element type
				var $elementType = $addAnotherForm.find('select[name=column_value_type]');
				// element label, options and other stuff
				var $elementDetails = $elementType.find('option:selected');
				// element labeel
				var $elementLabel = $elementDetails.attr('label');

				var $clone = ($elementIndex) ? $('#columns').find('.custom_column[rel=' + $elementIndex + ']') : $template.clone(true);

				// if new field adding
				if (!parseInt($elementIndex)) {
					// new column added, increase element Index
					$clone.attr('rel', $('#columns').find('.custom_column').length + 1);
				}

				// add element label
				$clone.find('label.wpallexport-xml-element').html($elementName.val());
				// wrap field value into PHP function
				$clone.find('input[name^=cc_php]').val($addAnotherForm.find('#coperate_php').is(':checked') ? '1' : '0');
				// save PHP function name
				$clone.find('input[name^=cc_code]').val($phpFunction.val());
				// save SQL code
				$clone.find('input[name^=cc_sql]').val($addAnotherForm.find('textarea.column_value').val());
				// save element name
				$clone.find('input[name^=cc_name]').val($elementName.val());
				// save element type
				$clone.find('input[name^=cc_type]').val($elementType.val());
				// save element value
				$clone.find('input[name^=cc_value]').val($elementDetails.attr('label'));
				// save element label
				$clone.find('input[name^=cc_label]').val($elementDetails.attr('label'));
				// save element options
				$clone.find('input[name^=cc_options]').val($elementDetails.attr('options'));

				// save combine into multiple fields
				$clone.find('input[name^=cc_combine_multiple_fields]').val($addAnotherForm.find('input[name="combine_multiple_fields"]:checked').val());
				$clone.find('input[name^=cc_combine_multiple_fields_value]').val($addAnotherForm.find('#combine_multiple_fields_value').val());

				// if new field adding append element to the export template
				if (!parseInt($elementIndex)) {
					$("#columns").find(".placeholder").hide();
					$sortable.append('<li></li>');
					$sortable.find('li:last').append($clone.removeClass('template').fadeIn());
				}

				var $options = $clone.find('input[name^=cc_options]').val();

				var $fieldType = $elementType.val();
				var isAddon = wpae_addons.includes($fieldType);

				function isFieldType(type) {
					return (
						$options.indexOf(`s:4:"type";s:${type.length}:"${type}";`) !== -1
					);
				}

				if ($elementLabel == '_sale_price_dates_from' || $elementLabel == '_sale_price_dates_to' || $elementLabel == '_date_paid') $fieldType = 'date';

				// set up additional settings for addons fields

				if (isAddon || $fieldType === 'acf') {
					if (isFieldType('repeater')) {
						var obj = {};
						obj['repeater_field_item_per_line'] = $addAnotherForm.find('#repeater_field_item_per_line').is(':checked');
						obj['repeater_field_fill_empty_columns'] = $addAnotherForm.find('#repeater_field_fill_empty_columns').is(':checked');
						$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
					}
				}

				if (isAddon && (isFieldType('date') || isFieldType('datetime'))) {
					var $dateType = $addAnotherForm.find('select.date_field_export_data').val();
					if ($dateType == 'unix')
						$clone.find('input[name^=cc_settings]').val('unix');
					else
						$clone.find('input[name^=cc_settings]').val($('.pmxe_date_format').val());
				}

				if (isAddon && (isFieldType('time'))) {
					var $format = $addAnotherForm.find('.pmxe_time_format').val();
					var obj = {
						time_format: $format,
					};

					$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
				}

				if (isAddon && (isFieldType('media') || isFieldType('gallery'))) {
					var $format = $addAnotherForm.find('select.media_field_export_data').val();
					var obj = {
						value_format: $format,
					};

					$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
				}

				if (isAddon && isFieldType('post')) {
					var $format = $addAnotherForm.find('select.post_field_export_data').val();
					var obj = {
						post_value_format: $format,
					};

					$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
				}

				if (isAddon && isFieldType('user')) {
					var $format = $addAnotherForm.find('select.user_field_export_data').val();
					var obj = {
						user_value_format: $format,
					};

					$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
				}

				// set up additional element settings by element type
				switch ($fieldType) {
					case 'content':
						var obj = {};
						obj['export_images_from_gallery'] = $addAnotherForm.find('#export_images_from_gallery').is(':checked');
						$clone.find('input[name^=cc_settings]').val(window.JSON.stringify(obj));
						break;
					// save post date field format
					case 'date':
					case 'comment_date':
					case 'comment_parent_date':
					case 'comment_parent_date_gmt':
					case 'user_registered':
					case 'post_modified':
					case '_date_paid':
						var $dateType = $addAnotherForm.find('select.date_field_export_data').val();
						if ($dateType == 'unix')
							$clone.find('input[name^=cc_settings]').val('unix');
						else
							$clone.find('input[name^=cc_settings]').val($('.pmxe_date_format').val());
						break;
					case 'woo':
						switch ($clone.find('input[name^=cc_value]').val()) {
							case '_upsell_ids':
							case '_crosssell_ids':
							case 'item_data___upsell_ids':
							case 'item_data___crosssell_ids':
								$clone.find('input[name^=cc_settings]').val($addAnotherForm.find('select.linked_field_export_data').val());
								break;
						}
						break;
					case 'woo_order':
						$woo_type = $clone.find('input[name^=cc_value]');
						switch ($woo_type.val()) {
							case 'post_date':
							case 'post_modified':
							case '_completed_date':
							case '_date_paid':
								var $dateType = $addAnotherForm.find('select.date_field_export_data').val();
								if ($dateType == 'unix')
									$clone.find('input[name^=cc_settings]').val('unix');
								else
									$clone.find('input[name^=cc_settings]').val($('.pmxe_date_format').val());
								break;
						}
						break;
					default:
						// save option for media images field types
						if ($clone.find('input[name^=cc_type]').val().indexOf('image_') !== -1) {
							var obj = {};
							obj['is_export_featured'] = $addAnotherForm.find('#is_image_export_featured').is(':checked');
							obj['is_export_attached'] = $addAnotherForm.find('#is_image_export_attached_images').is(':checked');
							obj['image_separator'] = $addAnotherForm.find('input[name=image_field_separator]').val();
							$clone.find('input[name^=cc_options]').val(window.JSON.stringify(obj));
						}

						break;
				}

				trigger_warnings();

				$addAnotherForm.hide();

				$('.wpallexport-overlay').hide();

				$('.custom_column').removeClass('active');

			});

			//custom_column_logic
			// Clicking on column for edit
			$('#columns').on('click', '.custom_column', function () {

				if ($(this).parent().hasClass('wpae-no-click') && navigator.userAgent.indexOf("Firefox") !== -1) {
					$(this).parent().removeClass('wpae-no-click');
				} else {


					$addAnotherForm.find('form')[0].reset();
					$addAnotherForm.find('input[type=checkbox]').removeAttr('checked');

					$addAnotherForm.removeClass('dc').addClass('cc');
					$addAnotherForm.attr('rel', $(this).attr('rel'));

					$addAnotherForm.find('.wpallexport-add-row-title').hide();
					$addAnotherForm.find('.wpallexport-edit-row-title').show();

					$addAnotherForm.find('input.column_name').parents('div.input:first').show();

					if ($('input[name^=export_to]').val() == 'xml') {
						$addAnotherForm.find('.wpae_column_name').css('display', 'none');
						$addAnotherForm.find('.wpae_element_name').css('display', 'block');

					} else {
						$addAnotherForm.find('.wpae_column_name').css('display', 'block');
						$addAnotherForm.find('.wpae_element_name').css('display', 'none');
					}

					$addAnotherForm.find('.cc_field').hide();
					$('.custom_column').removeClass('active');
					$(this).addClass('active');

					var $elementType = $(this).find('input[name^=cc_type]');
					var $elementLabel = $(this).find('input[name^=cc_label]');


					$('.wp_all_export_saving_status').html('');

					$addAnotherForm.find('select[name=column_value_type]').find('option').each(function () {
						if ($(this).attr('label') == $elementLabel.val() && $(this).val() == $elementType.val())
							$(this).attr({'selected': 'selected'}).trigger('click');
						else
							$(this).removeAttr('selected');
					});

					$('.wp-all-export-chosen-select').trigger('chosen:updated');

					// set php snipped
					var $php_code = $(this).find('input[name^=cc_code]');
					var $is_php = parseInt($(this).find('input[name^=cc_php]').val());

					if ($is_php) {
						$addAnotherForm.find('#coperate_php').prop('checked', true);
						$addAnotherForm.find('#coperate_php').parents('div.input:first').find('div[class^=switcher-target]').show();
					} else {
						$addAnotherForm.find('#coperate_php').prop('checked', false);
						$addAnotherForm.find('#coperate_php').parents('div.input:first').find('div[class^=switcher-target]').hide();
					}

					var $isCombineMultipleFieldsIntoOne = $(this).find('input[name^=cc_combine_multiple_fields]').val();

					if ($isCombineMultipleFieldsIntoOne == "1") {
						$addAnotherForm.find('input[name="combine_multiple_fields"][value="1"]').prop('checked', true);
						$addAnotherForm.find('#combine_multiple_fields_value').val($(this).find('input[name^=cc_combine_multiple_fields_value]').val());

						$('#combine_multiple_fields_value_container').show();
						$('#combine_multiple_fields_data').show();
						$('.export-single').hide();
						$('.single-field-options').hide();
					} else {
						$addAnotherForm.find('input[name="combine_multiple_fields"][value="0"]').prop('checked', true);

						$('#combine_multiple_fields_value_container').hide();
						$('#combine_multiple_fields_data').hide();
						$('.export-single').show();
						$('.single-field-options').show();
						$('.php_snipped').show();
					}

					$addAnotherForm.find('#coperate_php').parents('div.input:first').find('.php_code').val($php_code.val());

					var $options = $(this).find('input[name^=cc_options]').val();
					var $settings = $(this).find('input[name^=cc_settings]').val();

					var $fieldType = $elementType.val();
					var isAddon = wpae_addons.includes($fieldType);

					function isFieldType(type) {
						return (
							$options.indexOf(`s:4:"type";s:${type.length}:"${type}";`) !== -1
						);
					}

					if ($elementLabel.val() == '_sale_price_dates_from' || $elementLabel.val() == '_sale_price_dates_to') $fieldType = 'date';

					if (isAddon || $fieldType === 'acf') {
						if (isFieldType('repeater')) {
							$addAnotherForm.find('.repeater_field_type').show();
							if ($settings != "") {
								var $field_options = window.JSON.parse($settings);
								if ($field_options.repeater_field_item_per_line) $addAnotherForm.find('#repeater_field_item_per_line').prop('checked', 'checked');
								if ($field_options.repeater_field_fill_empty_columns) $addAnotherForm.find('#repeater_field_fill_empty_columns').prop('checked', 'checked');
							}
						}
					}

					if (isAddon && (isFieldType('date') || isFieldType('datetime'))) {
						$addAnotherForm.find('select.date_field_export_data').find('option').each(function () {
							if ($(this).val() == $settings || $settings != 'unix' && $(this).val() == 'php')
								$(this).attr({'selected': 'selected'}).trigger('click');
							else
								$(this).removeAttr('selected');
						});

						if ($settings != 'php' && $settings != 'unix') {
							if ($settings != '0') $('.pmxe_date_format').val($settings); else $('.pmxe_date_format').val('');
							$('.pmxe_date_format_wrapper').show();
						} else {
							$('.pmxe_date_format').val('');
						}
						$addAnotherForm.find('.date_field_type').show();
					}

					if (isAddon && (isFieldType('time'))) {
						const valueFormat = $settings || '{}';
						const selectedFormat = JSON.parse(valueFormat)?.time_format;
						$('.pmxe_time_format').val(selectedFormat);

						$addAnotherForm.find('.time_field_type').show();
					}

					if (isAddon && (isFieldType('media') || isFieldType('gallery'))) {
						const valueFormat = $settings || '{}';
						const selectedFormat = JSON.parse(valueFormat)?.value_format;
						$addAnotherForm.find('.media_field_type').show();

						$addAnotherForm.find('.media_field_type')
							.find('option')
							.filter(function () {
								return $(this).val() == selectedFormat;
							})
							.prop('selected', true);
					}

					if (isAddon && isFieldType('post')) {
						const valueFormat = $settings || '{}';
						const selectedFormat = JSON.parse(valueFormat)?.post_value_format;
						$addAnotherForm.find('.post_field_type').show();

						$addAnotherForm.find('.post_field_type')
							.find('option')
							.filter(function () {
								return $(this).val() == selectedFormat;
							})
							.prop('selected', true);
					}

					if (isAddon && isFieldType('user')) {
						const valueFormat = $settings || '{}';
						const selectedFormat = JSON.parse(valueFormat)?.user_value_format;
						$addAnotherForm.find('.user_field_type').show();

						$addAnotherForm.find('.user_field_type')
							.find('option')
							.filter(function () {
								return $(this).val() == selectedFormat;
							})
							.prop('selected', true);
					}

					switch ($fieldType) {
						case 'content':
							$addAnotherForm.find('.content_field_type').show();
							if ($settings != "" && $settings != 0) {
								var $field_options = window.JSON.parse($settings);
								if ($field_options.export_images_from_gallery) $addAnotherForm.find('#export_images_from_gallery').prop('checked', true);
							} else {
								// this option should be enabled by default
								$addAnotherForm.find('#export_images_from_gallery').prop('checked', true);
							}
							break;
						case 'sql':
							$addAnotherForm.find('textarea.column_value').val($(this).find('input[name^=cc_sql]').val());
							$addAnotherForm.find('.sql_field_type').show();
							break;
						case 'woo':
							$woo_type = $(this).find('input[name^=cc_value]');
							switch ($woo_type.val()) {
								case '_upsell_ids':
								case '_crosssell_ids':
								case 'item_data___upsell_ids':
								case 'item_data___crosssell_ids':

									$addAnotherForm.find('select.linked_field_export_data').find('option').each(function () {
										if ($(this).val() == $settings)
											$(this).attr({'selected': 'selected'}).trigger('click');
										else
											$(this).removeAttr('selected');
									});
									$addAnotherForm.find('.linked_field_type').show();
									break;
							}
							break;
						case 'woo_order':
							$woo_type = $(this).find('input[name^=cc_value]');
							switch ($woo_type.val()) {
								case 'post_date':
								case 'post_modified':
								case '_completed_date':
								case '_date_paid':

									$addAnotherForm.find('select.date_field_export_data').find('option').each(function () {
										if ($(this).val() == $settings || $settings != 'unix' && $(this).val() == 'php')
											$(this).attr({'selected': 'selected'}).trigger('click');
										else
											$(this).removeAttr('selected');
									});

									if ($settings != 'php' && $settings != 'unix') {
										if ($settings != '0') $('.pmxe_date_format').val($settings); else $('.pmxe_date_format').val('');
										$('.pmxe_date_format_wrapper').show();
									} else {
										$('.pmxe_date_format').val('');
									}
									$addAnotherForm.find('.date_field_type').show();
									break;
							}
							break;
						case 'date':
						case 'comment_date':
						case 'comment_parent_date':
						case 'comment_parent_date_gmt':
						case 'user_registered':
						case 'post_modified':
							$addAnotherForm.find('select.date_field_export_data').find('option').each(function () {
								if ($(this).val() == $settings || $settings != 'unix' && $(this).val() == 'php')
									$(this).attr({'selected': 'selected'}).trigger('click');
								else
									$(this).removeAttr('selected');
							});

							if ($settings != 'php' && $settings != 'unix') {
								if ($settings != '0') $('.pmxe_date_format').val($settings); else $('.pmxe_date_format').val('');
								$('.pmxe_date_format_wrapper').show();
							} else {
								$('.pmxe_date_format').val('');
							}
							$addAnotherForm.find('.date_field_type').show();
							break;
						default:

							if ($elementType.val().indexOf('image_') !== -1) {
								$addAnotherForm.find('.image_field_type').show();

								if ($options != "") {
									var $field_options = window.JSON.parse($options);

									if ($field_options.is_export_featured) $addAnotherForm.find('#is_image_export_featured').prop('checked', 'checked');
									if ($field_options.is_export_attached) $addAnotherForm.find('#is_image_export_attached_images').prop('checked', 'checked');

									$addAnotherForm.find('input[name=image_field_separator]').val($field_options.image_separator);
								}
							}

							break;
					}

					$addAnotherForm.find('input.switcher').trigger('change');

					var $column_name = $(this).find('input[name^=cc_name]').val();

					$addAnotherForm.find('input.column_name').val($column_name);
					$addAnotherForm.show();

					setTimeout(function () {
						if (editor) {
							editor.refresh();
						}
					}, 1);

					$('.wpallexport-overlay').show();

					var availableDataHeight = $('.wp-all-export-edit-column.cc').height() - 200;
					$addAnotherForm.find('.wpallexport-pointer-data.available-data').css('max-height', availableDataHeight);

					var editorElement = $('#wp_all_export_code + .CodeMirror').get(0);

					if (editorElement && editorElement.CodeMirror) {
						var editor = editorElement.CodeMirror;
						editor.refresh();
					}
				}
			});

			// Preview export file
			var doPreview = function (ths, tagno) {

				$('.wpallexport-overlay').show();

				ths.pointer({
					content: '<div class="wpallexport-preview-preload wpallexport-pointer-preview"></div>',
					position: {
						edge: 'right',
						align: 'center'
					},
					pointerWidth: 850,
					close: function () {
						$.post(ajaxurl, {
							pointer: 'pksn1',
							action: 'dismiss-wp-pointer'
						});
						$('.wpallexport-overlay').hide();
					}
				}).pointer('open');

				var $pointer = $('.wpallexport-pointer-preview').parents('.wp-pointer').first();

				var $leftOffset = ($(window).width() - 850) / 2;

				$pointer.css({'position': 'fixed', 'top': '15%', 'left': $leftOffset + 'px'});

				var request = {
					action: 'wpae_preview',
					data: $('form.wpallexport-step-3').serialize(),
					custom_xml: xml_editor.codemirror.getValue(),
					tagno: tagno,
					security: wp_all_export_security
				};
				var url = get_valid_ajaxurl();
				var show_cdata = $('#show_cdata_in_preview').val();

				if (url.indexOf("?") == -1) {
					url += '?show_cdata=' + show_cdata;
				} else {
					url += '&show_cdata=' + show_cdata;
				}

				$.ajax({
					type: 'POST',
					url: url,
					data: request,
					success: function (response) {

						ths.pointer({'content': response.html});

						$pointer.css({'position': 'fixed', 'top': '15%', 'left': $leftOffset + 'px'});

						var $preview = $('.wpallexport-preview');

						$preview.parent('.wp-pointer-content').removeClass('wp-pointer-content').addClass('wpallexport-pointer-content');

						$preview.find('.navigation a').off('click').on('click', function () {

							tagno += '#prev' == $(this).attr('href') ? -1 : 1;

							doPreview(ths, tagno);

						});

					},
					error: function (jqXHR, textStatus) {
						// Handle an eval error
						if (jqXHR.responseText.indexOf('[[ERROR]]') !== -1) {
							vm.preiviewText = $('.wpallexport-preview-title').text();

							var json = jqXHR.responseText.split('[[ERROR]]')[1];
							json = $.parseJSON(json);
							ths.pointer({
								'content': '<div id="post-preview" class="wpallexport-preview">' +
									'<p class="wpallexport-preview-title">' + json.title + '</p>\
						<div class="wpallexport-preview-content">' + json.error + '</div></div></div>'
							});

							$pointer.css({'position': 'fixed', 'top': '15%', 'left': $leftOffset + 'px'});

						} else {
							ths.pointer({
								'content': '<div id="post-preview" class="wpallexport-preview">' +
									'<p class="wpallexport-preview-title">An error occured</p>\
                            <div class="wpallexport-preview-content">An unknown error occured</div></div></div>'
							});
							$pointer.css({'position': 'fixed', 'top': '15%', 'left': $leftOffset + 'px'});
						}

					},
					dataType: "json"
				});
			};

			$(this).find('.preview_a_row').on('click', function () {
				doPreview($(this), 1);
			});

			// preview custom XML template
			$(this).find('.preview_a_custom_xml_row').on('click', function () {
				doPreview($(this), 1);
			});

			// help custom XML template
			$(this).find('.help_custom_xml').on('click', function () {
				$('.wp-all-export-custom-xml-help').css('left', ($(document).width() / 2) - 255).show();
				$('#wp-all-export-custom-xml-help-inner').css('max-height', $(window).height() - 150).show();
				$('.wpallexport-overlay').show();
			});

			$('.wp_all_export_custom_xml_help').find('h3').on('click', function () {
				var $action = $(this).find('span').html();
				$('.wp_all_export_custom_xml_help').find('h3').each(function () {
					$(this).find('span').html("+");
				});
				if ($action == "+") {
					$('.wp_all_export_help_tab').slideUp({queue: false});
					$('.wp_all_export_help_tab[rel=' + $(this).attr('id') + ']').slideDown({queue: false});
					$(this).find('span').html("-");
				} else {
					$('.wp_all_export_help_tab[rel=' + $(this).attr('id') + ']').slideUp({queue: false});
					$(this).find('span').html("+");
				}
			});

			$('.wpae-available-fields-group').on('click', function () {
				var $mode = $(this).find('.wpae-expander').text();
				$(this).next('div').slideToggle();
				if ($mode == '+') $(this).find('.wpae-expander').text('-'); else $(this).find('.wpae-expander').text('+');
			});

			$(document).on('click', '.pmxe_remove_column', function () {
				$(this).parents('li:first').remove();
			});

			$('.close_action').on('click', function () {
				$(this).parents('fieldset:first').hide();
				$('.wpallexport-overlay').hide();
				$('#columns').find('div.active').removeClass('active');
			});

			$('.date_field_export_data').on('change', function () {
				if ($(this).val() == "unix")
					$('.pmxe_date_format_wrapper').hide();
				else
					$('.pmxe_date_format_wrapper').show();
			});

			$(document).on('click', '.xml-expander', function () {
				var method;
				if ('-' == $(this).text()) {
					$(this).text('+');
					method = 'addClass';
				} else {
					$(this).text('-');
					method = 'removeClass';
				}
				// for nested representation based on div
				$(this).parent().find('> .xml-content')[method]('collapsed');
				// for nested representation based on tr
				var $tr = $(this).parent().parent().filter('tr.xml-element').next()[method]('collapsed');
			});

			$('.wp-all-export-edit-column').css('left', ($(document).width() / 2) - 432);

			var wp_all_export_config = {
				'.wp-all-export-chosen-select': {width: "50%"}
			};

			for (var selector in wp_all_export_config) {
				$(selector).chosen(wp_all_export_config[selector]);
				$(selector).on('change', function (evt, params) {
					$('.cc_field').hide();
					var selected_value = $(selector).find('option:selected').attr('label');
					var ftype = $(selector).val();

					switch (ftype) {
						case 'post_modified':
						case 'date':
							$('.date_field_type').show();
							break;
						case 'sql':
							$('.sql_field_type').show();
							break;
						case 'content':
							$('.content_field_type').show();
							break;
						case 'woo':
							switch (selected_value) {
								case 'item_data___upsell_ids':
								case 'item_data___crosssell_ids':
								case '_upsell_ids':
								case '_crosssell_ids':
									$addAnotherForm.find('.linked_field_type').show();
									break;
							}
							break;
						default:
							if ($(selector).val().indexOf('image_') !== -1) {
								$('.image_field_type').show();
							}
							break;
					}
				});
			}

			$('.wp-all-export-advanced-field-options').on('click', function () {
				if ($(this).find('span').html() == '+') {
					$(this).find('span').html('-');
					$('.wp-all-export-advanced-field-options-content').fadeIn('fast', function () {
						if ($('#coperate_php').is(':checked')) editor.codemirror.setCursor(1);
					});
				} else {
					$(this).find('span').html('+');
					$('.wp-all-export-advanced-field-options-content').hide();
				}
			});

			// Auto generate available data
			$('.wp_all_export_auto_generate_data').on('click', function () {

				$('ol#columns').find('li:not(.placeholder)').fadeOut().remove();
				$('ol#columns').find('li.placeholder').fadeOut();


				if (vm.availableDataSelector.find('li.wp_all_export_auto_generate').length) {
					vm.availableDataSelector.find('li.wp_all_export_auto_generate, li.pmxe_cats').each(function (i, e) {
						var $clone = $(this).clone();
						$clone.attr('rel', i);
						$("<li></li>").html($clone.html()).appendTo($("#columns_to_export ol"));
					});
				} else {
					vm.availableDataSelector.find('div.custom_column').each(function (i, e) {
						var $parent = $(this).parent('li');
						var $clone = $parent.clone();
						$clone.attr('rel', i);

						if ($clone.find('input[name^=cc_type]').val().indexOf('image_') !== -1) {
							$clone.find('.wpallexport-xml-element').html('Image ' + $clone.find('input[name^=cc_name]').val());
							$clone.find('input[name^=cc_name]').val('Image ' + $clone.find('input[name^=cc_name]').val());
						}

						if ($clone.find('input[name^=cc_type]').val().indexOf('attachment_') !== -1) {
							$clone.find('.wpallexport-xml-element').html('Attachment ' + $clone.find('input[name^=cc_name]').val());
							$clone.find('input[name^=cc_name]').val('Attachment ' + $clone.find('input[name^=cc_name]').val());
						}

						$("<li></li>").html($clone.html()).appendTo($("#columns_to_export ol"));
					});
				}

				trigger_warnings();

			});

			$(document).on('click', '.wp_all_export_clear_all_data', function () {
				$('ol#columns').find('li:not(.placeholder)').remove();
				$('ol#columns').find('li.placeholder').fadeIn();
			});

			if ($('input[name^=selected_post_type]').length) {

				init_filtering_fields();

				liveFiltering();

				$('form.wpallexport-template').find('input[type=submit]').on('click', function (e) {
					e.preventDefault();

					$('#validationError').fadeOut();
					$('#validationError p').find('*').remove();

					var submitButton = $(this);

					if (!vm.isGoogleMerchantsExport) {
						// Validate the form by sending it to preview before submitting it
						var request = {
							action: 'wpae_preview',
							data: $('form.wpallexport-step-3').serialize(),
							custom_xml: xml_editor.codemirror.getValue(),
							security: wp_all_export_security
						};


						$.ajax({
							type: 'POST',
							url: get_valid_ajaxurl(),
							data: request,
							success: function (response) {

								// Look for errors
								var tempDom = $('<div>').append($.parseHTML(response.html));
								var errorMessage = $('.error', tempDom);

								// If we have error messages
								if (errorMessage.length) {
									// Display the error messages
									errorMessage.each(function () {
										$('#validationError').find('p').append($(this));
									});

									$('#validationError').fadeIn();
									$('html, body').animate({scrollTop: $("#validationError").offset().top - 50});
								} else {
									// Else submit the form
									$('.hierarhy-output').each(function () {
										var sortable = $('.wp_all_export_filtering_rules.ui-sortable');
										if (sortable.length) {
											$(this).val(window.JSON.stringify(sortable.pmxe_nestedSortable('toArray', {startDepthCount: 0})));
										}
									});
									submitButton.parents('form:first').trigger('submit');
								}
							},
							error: function (jqXHR, textStatus) {
								$('#validationError p').html('');

								// Handle an eval error
								if (jqXHR.responseText.indexOf('[[ERROR]]') != -1) {
									var json = jqXHR.responseText.split('[[ERROR]]')[1];
									json = $.parseJSON(json);

									$('#validationError').find('p').append(json.error);
									$('#validationError').fadeIn();
									$('html, body').animate({scrollTop: $("#validationError").offset().top - 50});

								} else {
									// We don't know the error
									$('#validationError').find('p').html('An unknown error occured');
									$('#validationError').fadeIn();
									$('html, body').animate({scrollTop: $("#validationError").offset().top - 50});
								}
							},
							dataType: "json"
						});
					} else {
						submitButton.parents('form:first').trigger('submit');
					}
				});
			}

			$('.wpallexport-import-to-format').on('click', function () {

				var isWooCommerceOrder = vm.isWoocommerceOrderExport();

				$('.wpallexport-import-to-format').removeClass('selected');
				$(this).addClass('selected');

				if ($(this).hasClass('wpallexport-csv-type')) {
					selectSpreadsheet();
				} else {
					selectFeed();
				}
			});

			// template form: auto submit when `load template` list value is picked
			$(this).find('select[name="load_template"]').on('change', function () {

				var template = $(this).find('option:selected').val();
				var exportMode = $('.xml_template_type').find('option:selected').val();

				$(this).parents('form').trigger('submit', ['templateSelected']);
				return;
				if (exportMode == 'XmlGoogleMerchants') {
					angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('selectedTemplate', template);
				} else {
					$(this).parents('form').trigger('submit');
				}
			});

			var height = $(window).height();
			vm.availableDataSelector.find('.wpallexport-xml').css({'max-height': height - 125});

			// dismiss export template warnings
			$('.wp-all-export-warning').find('.notice-dismiss').on('click', function () {

				var $parent = $(this).parent('.wp-all-export-warning');

				$('#dismiss_warnings').val('1');

				if (typeof export_id == 'undefined') {
					$parent.slideUp();
					return true;
				}

				var request = {
					action: 'dismiss_export_warnings',
					data: {
						export_id: export_id,
						warning: $parent.find('p:first').html()
					},
					security: wp_all_export_security
				};

				$parent.slideUp();

				$.ajax({
					type: 'POST',
					url: get_valid_ajaxurl(),
					data: request,
					success: function (response) {
					},
					dataType: "json"
				});
			});

		});
		// [ \Step 2 ( export template ) ]


		// [ Step 3 ( export options ) ]
		if ($('.wpallexport-export-options').length) {

			if ($('input[name^=selected_post_type]').length) {

				var postType = $('input[name^=selected_post_type]').val();

				init_filtering_fields();
				liveFiltering();

				$(document).on('wpae-scheduling-options-form:submit', function (e) {

					$('.hierarhy-output').each(function () {
						var sortable = $('.wp_all_export_filtering_rules.ui-sortable');
						if (sortable.length) {
							$(this).val(window.JSON.stringify(sortable.pmxe_nestedSortable('toArray', {startDepthCount: 0})));
						}
					});

					$('#wpae-options-form').trigger('submit');
				});
			}

		}
		$('#export_only_new_stuff').on('click', function () {
			$(this).prop('disabled', 'disabled');
			$('label[for=export_only_new_stuff]').addClass('loading');
			liveFiltering(null, function () {
				$('label[for=export_only_new_stuff]').removeClass('loading');
				$('#export_only_new_stuff').removeAttr('disabled');
			});
		});
		$('#export_only_modified_stuff').on('click', function () {
			$(this).prop('disabled', 'disabled');
			$('label[for=export_only_modified_stuff]').addClass('loading');
			liveFiltering(null, function () {
				$('label[for=export_only_modified_stuff]').removeClass('loading');
				$('#export_only_modified_stuff').removeAttr('disabled');
			});
		});
		$('#export_only_customers_that_made_purchases').on('click', function () {
			$(this).prop('disabled', 'disabled');
			$('label[for=export_only_customers_that_made_purchases]').addClass('loading');
			liveFiltering(null, function () {
				$('label[for=export_only_customers_that_made_purchases]').removeClass('loading');
				$('#export_only_customers_that_made_purchases').removeAttr('disabled');
			});
		});
		// [ \Step 3 ( export options ) ]


		// [ Step 4 ( export completed ) ]
		$('.download_data').on('click', function () {
			window.location.href = $(this).attr('rel');
		});
		// [ \Step 4 ( export completed ) ]


		// [ Additional functionality ]

		// Add new filtering rule
		$(document).on('click', '#wp_all_export_add_rule', function () {

			var $el = $('#wp_all_export_xml_element');
			var $rule = $('#wp_all_export_rule');
			var $val = $('#wp_all_export_value');

			if ($el.val() == "" || $rule.val() == "") return;

			var relunumber = $('.wp_all_export_filtering_rules').find('li').length + 1;

			var html = '<li id="item_' + relunumber + '" class="dragging"><div class="drag-element">';
			html += '<input type="hidden" value="' + $el.val() + '" class="wp_all_export_xml_element" name="wp_all_export_xml_element[' + relunumber + ']"/>';
			html += '<input type="hidden" value="' + $el.find('option:selected').html() + '" class="wp_all_export_xml_element_title" name="wp_all_export_xml_element_title[' + relunumber + ']"/>';
			html += '<input type="hidden" value="' + $rule.val() + '" class="wp_all_export_rule" name="wp_all_export_rule[' + relunumber + ']"/>';
			html += '<input type="hidden" value="' + $val.val() + '" class="wp_all_export_value" name="wp_all_export_value[' + relunumber + ']"/>';
			html += '<span class="rule_element">' + $el.find('option:selected').html() + '</span> <span class="rule_as_is">' + $rule.find('option:selected').html() + '</span> <span class="rule_condition_value">"' + $val.val() + '"</span>';
			html += '<span class="condition"> <label for="rule_and_' + relunumber + '">AND</label><input id="rule_and_' + relunumber + '" type="radio" value="and" name="rule[' + relunumber + ']" checked="checked" class="rule_condition"/><label for="rule_or_' + relunumber + '">OR</label><input id="rule_or_' + relunumber + '" type="radio" value="or" name="rule[' + relunumber + ']" class="rule_condition"/> </span>';
			html += '</div><a href="javascript:void(0);" class="icon-item remove-ico"></a></li>';

			$('#wpallexport-filters, #wp_all_export_apply_filters').show();
			$('#no_options_notice').hide();

			$('.wp_all_export_filtering_rules').append(html);

			$('.wp_all_export_filtering_rules').find('.condition:hidden').each(function () {
				$(this).show();
				$(this).find('.rule_condition:first').prop('checked', 'checked');
			});
			$('.wp_all_export_filtering_rules').find('.condition').removeClass('last_condition');
			$('.wp_all_export_filtering_rules').find('.condition:last').addClass('last_condition');

			$('.wp_all_export_product_matching_mode').show();

			$el.prop('selectedIndex', 0).trigger('chosen:updated');
			$rule.prop('selectedIndex', 0).trigger('chosen:updated');

			$val.val('');
			$('#wp_all_export_value').show();

			$('#date_field_notice').hide();

			liveFiltering();

		});

		// Re-count posts when clicking "OR" | "AND" clauses
		$(document).on('click', 'input[name^=rule]', function () {
			liveFiltering();
		});
		$(document).on('click', 'input.wpml_lang', function () {
			var inputName = $(this).prop('name');
			var value = $('input[name=' + inputName + ']:checked').val();
			var $thisInput = $('.wpml_lang[value=' + value + ']');
			$thisInput.prop('checked', 'checked');

			$('#wpml_lang').val(value);
			liveFiltering();
		});
		// Re-count posts when changing product matching mode in filtering section
		$(document).on('change', 'select[name^=product_matching_mode]', function () {
			liveFiltering();
		});

		// Re-count posts when deleting a filtering rule
		$('#wpallexport-filtering-container').on('click', '.remove-ico', function () {
			$(this).parents('li:first').remove();
			if (!$('.wp_all_export_filtering_rules').find('li').length) {
				$('#wp_all_export_apply_filters').hide();
				$('#no_options_notice').show();
				$('.wp_all_export_product_matching_mode').hide();
			} else {
				$('.wp_all_export_filtering_rules').find('li:last').find('.condition').addClass('last_condition');
			}

			liveFiltering();
		});
		// hide "value" input when "Is Empty" or "Is Not Empty" rule is selected
		$('#wp_all_export_rule').on('change', function () {
			if ($(this).val() == 'is_empty' || $(this).val() == 'is_not_empty')
				$('#wp_all_export_value').hide();
			else
				$('#wp_all_export_value').show();
		});

		function wpae_save_functions(data = '') {
			if (data === '') {
				data = $(this).hasClass('wp_all_export_save_main_code') ? main_editor.codemirror.getValue() : editor.codemirror.getValue();
			}

			var request = {
				action: 'save_functions',
				data: data,
				security: wp_all_export_security
			};
			$('.wp_all_export_functions_preloader').show();
			$('.wp_all_export_saving_status').removeClass('error updated').html('');

			return $.ajax({
				type: 'POST',
				url: get_valid_ajaxurl(),
				data: request,
				success: function (response) {
					$('.wp_all_export_functions_preloader').hide();

					if (response.result) {
						$('.wp_all_export_saving_status').addClass('updated');
						setTimeout(function () {
							$('.wp_all_export_saving_status').removeClass('error updated').html('').fadeOut();
						}, 3000);
					} else {
						$('.wp_all_export_saving_status').addClass('error');
					}

					$('.wp_all_export_saving_status').html(response.msg).show();

				},
				error: function (jqXHR, textStatus) {
					$('.wp_all_export_functions_preloader').hide();
				},
				dataType: "json"
			});
		}

		// saving & validating function editor
		$('.wp_all_export_save_functions').on('click', function () {

			$('.cross-sale-notice.codebox').slideUp();

			wpae_save_functions.call(this);

		});
		// auot-generate zapier API key
		$('input[name=pmxe_generate_zapier_api_key]').on('click', function (e) {

			e.preventDefault();

			var request = {
				action: 'generate_zapier_api_key',
				security: wp_all_export_security
			};

			$.ajax({
				type: 'POST',
				url: get_valid_ajaxurl(),
				data: request,
				success: function (response) {
					$('input[name=zapier_api_key]').val(response.api_key);
				},
				error: function (jqXHR, textStatus) {

				},
				dataType: "json"
			});
		});

		var $tmp_xml_template = '';
		var $xml_template_first_load = true;

		$('.xml_template_type').on('change', function (e) {

			switch ($(this).find('option:selected').val()) {
				case 'simple':
					$('.simple_xml_template_options').slideDown();
					$('.wpallexport-simple-xml-template').slideDown();
					$('.wpallexport-custom-xml-template').slideUp();
					$('.wpallexport-function-editor').slideUp();

					$('.pmxe_product_data').find(".wpallexport-xml-element:contains('Attributes')").parents('li:first').show();
					if (angular.element(document.getElementById('googleMerchants')).injector()) {
						resetDraggable();
						angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsDeselected');
					}
					vm.isGoogleMerchantsExport = false;

					if (vm.availableDataSelector.css('position') == 'fixed') {
						$('.template-sidebar').find('.wpae_available_data').css({'position': 'static', 'top': '50px'});
					}

					if (!angular.isUndefined(e.originalEvent)) {
						if (!$('.wpallexport-file-options').hasClass('closed')) $('.wpallexport-file-options').find('.wpallexport-collapsed-header').trigger('click');
					}

					break;
				case 'custom':
					if (angular.element(document.getElementById('googleMerchants')).injector()) {
						resetDraggable();
						angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsDeselected');
					}
					vm.isGoogleMerchantsExport = false;

					if (vm.availableDataSelector.css('position') == 'fixed') {
						$('.template-sidebar').find('.wpae_available_data').css({'position': 'static', 'top': '50px'});
					}

					$('.simple_xml_template_options').slideUp();
					$('.wpallexport-simple-xml-template').slideUp();
					$('.wpallexport-function-editor').slideDown();

					// If the event was not triggered by the user
					if (!angular.isUndefined(e.originalEvent)) {
						if (!$('.wpallexport-file-options').hasClass('closed')) $('.wpallexport-file-options').find('.wpallexport-collapsed-header').trigger('click');
					}

					$('.wpallexport-custom-xml-template').slideDown(400, function () {
						setTimeout(function () {
							xml_editor.codemirror.setCursor(1);
						}, 1000);
					});
					$('.pmxe_product_data').find(".wpallexport-xml-element:contains('Attributes')").parents('li:first').hide();

					if ($(this).find('option:selected').val() == 'XmlGoogleMerchants') {
						if (!$xml_template_first_load) {
							$tmp_xml_template = xml_editor.codemirror.getValue();
							// Get all necessary data according to the spec
							var request = {
								action: 'get_xml_spec',
								security: wp_all_export_security,
								spec_class: $(this).find('option:selected').val()
							};
							xml_editor.codemirror.setValue("Loading...");
							$.ajax({
								type: 'POST',
								url: get_valid_ajaxurl(),
								data: request,
								success: function (response) {
									if (response.result) {
										xml_editor.codemirror.setValue(response.fields);
									}
								},
								error: function (jqXHR, textStatus) {

								},
								dataType: "json"
							});
						}
					} else {
						if ($tmp_xml_template != '') {
							xml_editor.codemirror.setValue($tmp_xml_template);

							$tmp_xml_template = '';
						}
					}
					break;
				case 'XmlGoogleMerchants':

					if ($('#pmxe_woocommerce_addon_installed').length) {
						$('.simple_xml_template_options').slideUp();
						if (!vm.isCSVExport()) {
							$('.wpallexport-simple-xml-template').slideUp();
						}
						$('.wpallexport-custom-xml-template').slideUp();
						$('.wpallexport-function-editor').slideDown();

						if (!angular.isUndefined(e.originalEvent)) {
							if (!$('.wpallexport-file-options').hasClass('closed')) $('.wpallexport-file-options').find('.wpallexport-collapsed-header').trigger('click');
						}
						$('.pmxe_product_data').find(".wpallexport-xml-element:contains('Attributes')").parents('li:first').show();

						if (angular.element(document.getElementById('googleMerchants')).injector()) {
							resetDraggable();
							angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsSelected', vm.isProductVariationsExport());
						}
						vm.isGoogleMerchantsExport = true;
					} else {
						$('.wpallexport-submit-buttons').hide();
						$('.simple_xml_template_options').slideUp();
						$('.wpallexport-simple-xml-template').slideUp();
						$('.wpallexport-custom-xml-template').slideUp();

						$('.wpallexport-google-merchants-template').slideDown();

						if (!$('.wpallexport-file-options').hasClass('closed')) {
							$('.wpallexport-file-options').find('.wpallexport-collapsed-header').trigger('click');
						}

						$('.pmxe_product_data').find(".wpallexport-xml-element:contains('Attributes')").parents('li:first').show();

						if (angular.element(document.getElementById('googleMerchants')).injector()) {
							resetDraggable();
							angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsSelected', vm.isProductVariationsExport());
						}
						vm.isGoogleMerchantsExport = true;
						$('.wpallexport-submit-template').prop('disabled', 'disabled');

						setTimeout(function () {
							$('.wpallexport-google-merchants-template').show();
						}, 100);

					}
					break;
				default:
					resetDraggable();
					angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsDeselected');
					vm.isGoogleMerchantsExport = false;

					if (vm.availableDataSelector.css('position') == 'fixed') {
						$('.template-sidebar').find('.wpae_available_data').css({'position': 'static', 'top': '50px'});
					}

					$('.simple_xml_template_options').slideUp();
					$('.wpallexport-simple-xml-template').slideDown();
					$('.wpallexport-custom-xml-template').slideUp();
					$('.wpallexport-function-editor').slideDown();

					$('.pmxe_product_data').find(".wpallexport-xml-element:contains('Attributes')").parents('li:first').show();

					break;
			}
			$xml_template_first_load = false;
		}).trigger('change');

		$('.wpallexport-overlay').on('click', function () {

			if ($('.wp-all-export-edit-column.cc').css('visibility') == 'hidden') {
				$('.wp-all-export-edit-column.cc').css('visibility', 'visible');
				$('.wp-pointer').hide();

				window.$pmxeBackupElement.html(window.$pmxeBackupElementContent);
				return;
			}

			$('.wp-pointer').hide();
			$('#columns').find('div.active').removeClass('active');
			$('fieldset.wp-all-export-edit-column').hide();
			$('fieldset.wp-all-export-custom-xml-help').hide();
			$('fieldset.wp-all-export-scheduling-help').hide();


			$(this).hide();
		});

		if ($('.wpallexport-template').length) {
			setTimeout(function () {
				$('.wpallexport-template').slideDown();
			}, 1000);
		}
		// [ \Additional functionality ]

		// Logic for radio boxes (CDATA settings)
		$('input[name=simple_custom_xml_cdata_logic]').on('change', function () {
			var value = $('input[name=simple_custom_xml_cdata_logic]:checked').val();
			$('#custom_custom_xml_cdata_logic_' + value).prop('checked', true);
			$('#custom_xml_cdata_logic').val(value);
		});


		$('input[name=custom_custom_xml_cdata_logic]').on('change', function (event) {
			event.stopImmediatePropagation();
			var value = $('input[name=custom_custom_xml_cdata_logic]:checked').val();
			$('#simple_custom_xml_cdata_logic_' + value).prop('checked', true);
			$('input[name=simple_custom_xml_cdata_logic]').trigger('change');

		});

		// Logic for show CDATA tags in preview
		$('.show_cdata_in_preview').on('change', function () {
			if ($(this).is(':checked')) {
				$('#show_cdata_in_preview').val(1);
				$('.show_cdata_in_preview').prop('checked', true);
			} else {
				$('#show_cdata_in_preview').val(0);
				$('.show_cdata_in_preview').prop('checked', false);
			}
		});

		// Logic to show CSV advanced options
		$('#export_to_sheet').on('change', function (e) {

			if ($('input[name=export_to]').val() === 'xml') return;

			var isWooCommerceOrder = vm.isWoocommerceOrderExport();
			var isVariationsExport = vm.isProductVariationsExport();

			var value = $(this).val();
			if (value === 'xls' || value === 'xlsx') {
				if (isWooCommerceOrder || isVariationsExport) {
					$('.csv_delimiter').hide();
				} else {
					$('.export_to_csv').slideUp();
				}
			} else {
				$('.csv_delimiter').show();
				$('.export_to_csv').slideDown();
			}

			// If the event was not triggered by the user
			if (!angular.isUndefined(e.originalEvent)) {
				if (!$('.wpallexport-file-options').hasClass('closed')) $('.wpallexport-file-options').find('.wpallexport-collapsed-header').trigger('click');
			}
		}).trigger('change');

		if ($('.wpallexport-step-3').length && $(window).height()) {
			$('.wp-all-export-field-options').css({'max-height': $(window).height() - 220 + 'px'});
		}


		$('#templateForm').on('submit', function (event, data) {

			//var schedulingFormValid = pmxeISchedulingFormValid();
			var schedulingFormValid = {
				isValid: true
			};

			if (schedulingFormValid.isValid) {
			} else {
				alert(schedulingFormValid.message);
				event.preventDefault();
				event.stopImmediatePropagation();
				return false;

			}


			if (vm.isGoogleMerchantsExport) {
				if (data !== 'templateSelected') {
					event.stopImmediatePropagation();
					var templateName = $('.switcher-target-save_template_as input').val();
					angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('googleMerchantsSubmitted', {templateName: templateName});
					return false;
				} else {
					// If a template was selected submit the form as usual
					return true;
				}
			}


		});

		$('#save_template_as').on('change', function () {
			if ($(this).prop('checked') == true) {
				angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('templateShouldBeSaved', 'Template name');
			} else {
				angular.element(document.getElementById('googleMerchants')).injector().get('$rootScope').$broadcast('templateShouldNotBeSaved');
			}

		});

		if (vm.availableDataSelector.length) {

			var originalOffset = vm.availableDataSelector.offset().top - 50;
			var elementWidth = vm.availableDataSelector.width();

			vm.availableDataSelector.css('width', elementWidth);

			$(window).scroll(function (e) {

				if (vm.isGoogleMerchantsExport) {
					var isPositionFixed = (vm.availableDataSelector.css('position') == 'fixed');
					if ($(this).scrollTop() > originalOffset && !isPositionFixed) {
						$('.template-sidebar').find('.wpae_available_data').css({'position': 'fixed', 'top': '50px'});
					}
					if ($(this).scrollTop() < originalOffset && isPositionFixed) {
						$('.template-sidebar').find('.wpae_available_data').css({'position': 'static', 'top': '50px'});
					}
				}
			});
		}

		// dismiss export template warnings
		$('.wpae-general-notice-dismiss').on('click', function () {

			var $parent = $(this).parent();
			var noticeId = $(this).attr('data-noticeId');

			var request = {
				action: 'dismiss_warnings',
				data: {
					notice_id: noticeId
				},
				security: wp_all_export_security
			};

			$parent.slideUp();

			$.ajax({
				type: 'POST',
				url: get_valid_ajaxurl(),
				data: request,
				success: function (response) {
				},
				dataType: "json"
			});

		});

		$('#runExportForm').on('submit', function () {
			$('#mainRunForm').trigger('submit');
			return false;
		});


	});
})(jQuery, window.EventService);
