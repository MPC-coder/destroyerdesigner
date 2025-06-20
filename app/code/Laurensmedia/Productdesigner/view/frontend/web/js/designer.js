var canvasdesigner;
var yourDesigner;
var isLoadingTemplate = false;

document.addEventListener('DOMContentLoaded', function(){
	var isEnabled = (document.getElementById("enable_designer") != null) ? document.getElementById("enable_designer").value : '0';
	if(isEnabled == '0' || isEnabled == ''){
		require([
			'jquery'
		], function($){
			var tierPrices = $(".tier-prices-json").val() ? JSON.parse($(".tier-prices-json").val()) : [];
			$('<div class="pd-item-price"><span class="pd-item-price-insert"></span><span>€</span><span> par pièce</span></div><div class="shipping-price">Livraison: <span></span></div>').insertAfter(".price-wrapper");
			
			$("input#qty").bind('change', function(){
				var baseQty = ($(this).val() > 0) ? parseFloat($(this).val()) : 1;
				var basePrice = parseFloat($(".price-wrapper").first().data('price-amount')) * baseQty;
				$.each(tierPrices, function(qty, price){
					if(baseQty >= qty){
						basePrice = price * baseQty;
					}
				});
				$(".price-wrapper span.price").html(basePrice.toFixed(2).replace('.', ',') +' €');
				$(".pd-item-price-insert").html((basePrice / baseQty).toFixed(2).replace('.', ',')+' ');

				$.ajax({
					url: BASE_URL+'advancedshipping/index/getrate/',
					method: 'POST',
					data: {
						qty: baseQty,
						product_id: $("input[name=product]").val()
					},
					success: function(data){
						$(".shipping-price span").html(data);
					}
				});

				$.ajax({
					type: "POST",
					url: $("input#baseurl").val()+'estimatetimeshipping/estimation/quoteDate',
					data: {
						currentSku: $(".product.attribute.sku .value").text(),
						type: 'product',
						qty: $("input#qty").val(),
						address: '{"region_id":"","country_id":"","region":"","postcode":""}',
						method: ''
					},
					success: function(result) {
						$("#preparation-time").addClass('success').html(result.preparationDate);
					}
				});
			});
			$("input#qty").trigger('change');
			setTimeout(function(){
				if($("input#qty").val() <= 0){
					$("input#qty").val(1);
				}
				$("input#qty").trigger('change');
			}, 2000);
		});
		return;
	}
	require([
		'jquery',
		'jquery/ui'
	], function($, tmp1){
		if($(window).width() <= 768){
			$(".open-product-designer").insertAfter('.product.media').css('order', '-1');
		} else {
			$(".open-product-designer").insertAfter('.product-info-price');
		}
		$(".open-product-builder").hide();
		
		$(".product-info-stock-sku .block-static-block").insertAfter('.product-info-stock-sku');
		$(".product-info-stock-sku").insertAfter('h1.page-title');
		$("#preparation-time").insertAfter('.product.attribute.overview');
		if($(window).width() <= 768){
			$(".product-options-bottom").prependTo('.product-info-main');
		} else {
			$(".product-options-bottom").insertAfter('.product.attribute.overview');
		}
		$(".product-info-price").insertAfter('.product-options-bottom');
		$(".product-info-price .price-box").show();
		
		$("input#qty").trigger('change');
		setTimeout(function(){
			if($("input#qty").val() <= 0){
				$("input#qty").val(1);
			}
			$("input#qty").trigger('change');
		}, 2000);
		
		var hasLoadedDesigner = false;
		$("#product-addtocart-button").removeAttr('disabled');
		$('#product-addtocart-button, #product-updatecart-button').click(function(){
			if(!hasLoadedDesigner && $("input#enable_designer").val() == '1'){
				$(".open-product-designer").trigger('click');
				return false;
			}
		});

		jQuery(document).on('click', '.open-product-designer', function(){
			if($("input#enable_designer").val() == '0' || $("input#enable_designer").val() == ''){
				return;
			}
			
			require([
				'jquery',
				'jquery/ui',
				'Laurensmedia_Productdesigner/js/fabric.min',
				'Laurensmedia_Productdesigner/js/FancyProductDesigner-all'
			], function($, tmp1, fabric, FancyProductDesigner2){
				if(typeof(window.fabric) == 'undefined'){
					window.fabric = fabric;
				}
				
				hasLoadedDesigner = true;
				$(".open-product-designer").hide();
				$(".open-product-builder").show();
				$(".canvas-designer-container").show();
				//$(".product.media").html('');
				//$(".canvas-designer-container").appendTo('.product.media');
        $(".canvas-designer-container").appendTo('.product.media');
        $(".product.media").children(':not(.canvas-designer-container)').remove();
				$(".product-info-price .price-box").hide();
				
				$(".fpd-custom-layers-container").insertAfter($(".fpd-container .fpd-mainbar").first());
				
				var date = new Date();
				
				var fonts = JSON.parse($("input#fonts").val());
				var editorMode = false;
				if($("input#editormode").val() == 'true'){
					editorMode = true;
				}
				var designs = JSON.parse($("input#designer_designs").val());
				var templatesData = JSON.parse($("input#templates").val());
				
				var mainBarModules = [];
				if($("input#disable_upload").val() != '1'){
					mainBarModules.push('images');
				}
				if($("input#disable_text").val() != '1'){
					mainBarModules.push('text');
				}
				if(!$.isEmptyObject(designs) && $("input#disable_library").val() != '1'){
					mainBarModules.push('designs');
				}
				if(!$.isEmptyObject(templatesData) && typeof(templatesData.length) == 'undefined'){
					mainBarModules.push('layouts');
				}
				$(".box-tocart").hide();
			
				var $yourDesigner = $('#clothing-designer'),
					pluginOpts = {
			//     		stageWidth: 1200,
						editorMode: editorMode,
						fonts: fonts,
						customTextParameters: {
							colors: ($("input#is_engraving").val() == 'true' || $("input#fixed_printing_color").val() != '') ? false : true,
							removable: true,
							resizable: true,
							draggable: true,
							rotatable: true,
							autoCenter: true,
							curvable: true,
							boundingBox: "Base"
						},
						customImageParameters: {
							draggable: true,
							removable: true,
							resizable: true,
							rotatable: true,
							colors: ($("input#is_engraving").val() == 'true' || $("input#fixed_printing_color").val() != '') ? false : true,
							// colors: '#000',
							autoCenter: true,
							boundingBox: "Base",
							maxW: 10000,
							maxH: 10000,
							minDPI: 0
						},
						imageParameters: {
							scaleMode: 'fit'
						},
						mainBarModules: mainBarModules,
						customAdds: {
							uploads: ($("input#disable_upload").val() == '1') ? false : true
						},
						elementParameters: {
							zChangeable: true,
							boundingBoxMode: 'none',
							color: $("input#fixed_printing_color").val()
						},
						actions:  {
							'top': [/*'download','print', 'preview-lightbox'*/],
							'right': [/*'magnify-glass', 'zoom', 'reset-product', 'qr-code'*/],
							'bottom': [/*'undo','redo'*/],
							'left': ['undo','redo','manage-layers',/*'save','load','magnify-glass',*/ 'zoom', 'reset-product', /* 'qr-code' */]
						},
						langJSON: '/media/productdesigner/default.json',
						templatesDirectory: '/media/productdesigner/templates/',
						stageWidth: parseInt($("input#column_width").val()),
						stageHeight: parseInt($("input#column_width").val()),
						customImageAjaxSettings: {
							url: $("input#baseurl").val()+'productdesigner/index/upload?grayscale='+(($("input#is_engraving").val() == 'true' && $("input#disable_grayscale_filter").val() != '1') ? 'true' : 'false'),
							data: {
								saveOnServer: 1,
								uploadsDir: 'media/productdesigner/img_shirt/'+date.getFullYear()+'/'+padLeft(date.getMonth() + 1, 2)+'/'+date.getDate()+'/',
								uploadsDirURL: $("input#baseurl").val()+'media/productdesigner/img_shirt/'+date.getFullYear()+'/'+padLeft(date.getMonth() + 1, 2)+'/'+date.getDate()+'/',
							},
							type: 'POST',
							method: 'POST'
						},
						layouts: JSON.parse($("input#layouts").val()),
						textParameters: {
							fontFamily: "Verdana",
							fontSize: 30
						},
						toolbarDynamicContext: '.fpd-custom-layers-container',
						toolbarPlacement: 'smart',
						disableTextEmojis: true
					};
			
				yourDesigner = new FancyProductDesigner($yourDesigner, pluginOpts);
				
				$yourDesigner.on('ready', function(){
					$("body").addClass('product-shop');
					if($(window).width() <= 768){
						$(".fpd-mainbar").insertBefore('.product-name');
					} else {
						$(".fpd-mainbar").insertBefore('.product-add-form');
					}
					$(".fpd-mainbar").wrap('<div class="fpd-topbar fpd-primary-bg-color"></div>');
					$(".fpd-topbar").wrap('<div class="fpd-container"></div>');
				//$(".fpd-views-selection").insertAfter('.canvas-designer-container');
					$(".canvas-designer-container > .fpd-clearfix").insertAfter('.fpd-mainbar');
					//$(".product-options-bottom").hide();
					$(".product-type-data .price-box").hide();
					if(!editorMode){
						//$(".fpd-actions-wrapper.fpd-pos-left").insertAfter('.designer-product-shop > .fpd-container');
						// $(".fpd-views-selection").insertBefore('.fpd-topbar');
					}
					if($("input#fixed_printing_color").val() != ''){
						$(".fpd-tool-fill").addClass('hidden');
					}
					
					if(parseFloat($("input#qty").val()) > 0){
						$("input.fpd-qty").val($("input#qty").val());
					}

					if($(".fpd-views-wrapper").length == 0){
						$(".fpd-views-selection").wrap('<div class="fpd-views-wrapper" />');
					}
					// $(".fpd-views-selection").insertBefore($('.product-info-main .fpd-topbar').first());
					$(".fpd-views-wrapper").insertBefore($('.product-info-main .fpd-topbar').first());
					$('.product-info-main .fpd-topbar').first().addClass('fullwidth');
					
					setTimeout(function(){
						if($(".fpd-views-selection .fpd-item").length > 2){
							$(".fpd-views-selection").parent().addClass('fullwidth');
						}
					}, 3000);
					
					$(".fpd-custom-layers-container").insertAfter($(".fpd-container .fpd-mainbar").first());
					
					if(jQuery(".fpd-views-selection").length > 1){
						// jQuery(".fpd-views-selection").last().remove();
					}
					jQuery(".fpd-close-dialog").trigger('click');
					
					$yourDesigner.on('viewSelect', function(e, viewIndex, viewInstance){
						if($("input#is_engraving").val() == 'true' && $("input#disable_grayscale_filter").val() != '1'){
							if($("input#engrave_both_sides").val() != 'true'){
								if(viewIndex == 0){
									yourDesigner.mainOptions.customImageAjaxSettings.url = $("input#baseurl").val()+'productdesigner/index/upload?grayscale=false';
								} else {
									yourDesigner.mainOptions.customImageAjaxSettings.url = $("input#baseurl").val()+'productdesigner/index/upload?grayscale=true';
								}
							}
						}
					});
					
					$yourDesigner.on('elementAdd', function(e, el){
						if(isLoadingTemplate == true){
							$yourDesigner.trigger('priceChange');
							return;
						}
						var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
						var label = currentView.title;
						var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
						
						if(el.text != null){
							yourDesigner.setElementParameters({
								top: parseFloat(data.output_y1) + ((parseFloat(data.output_y2) - parseFloat(data.output_y1)) / 2),// + 100,
								left: parseFloat(data.output_x1) + ((parseFloat(data.output_x2) - parseFloat(data.output_x1)) / 2),// + 75
							}, el);
							if($("input#fixed_printing_color").val() != ''){
								yourDesigner.setElementParameters({
									color: $("input#fixed_printing_color").val(),
									fill: $("input#fixed_printing_color").val()
								}, el);
							}
							if(el.fontFamily == 'Arial'){
								var firstFont = yourDesigner.mainOptions.fonts[0].name;
								yourDesigner.setElementParameters({
									fontFamily: firstFont
								}, el);
							}
						}
						
						if(el.svgUid != null && el.fill != null){
							if($("input#fixed_printing_color").val() != ''){
								yourDesigner.setElementParameters({
									color: $("input#fixed_printing_color").val(),
									fill: $("input#fixed_printing_color").val()
								}, el);
							}
						}
						
						if(el.source != null && (el.source.indexOf('http') > -1 || el.source.indexOf('data:image') > -1)){
							var width = el.width;
							var height = el.height;
							console.log(width);
							
							var origWidth = width;
							var origHeight = height;
							
							var jsonData = $("input.canvas-json-data[data-label="+label+"]").first().data('jsondata');
					
							var x = (parseFloat(jsonData.width) - width) / 2;
							var y = (parseFloat(jsonData.height) - height) / 2;
							var x = parseFloat(jsonData.output_x1) - 2;
							var y = parseFloat(jsonData.output_y1);
							var x2 = parseFloat(jsonData.output_x2);
							var y2 = parseFloat(jsonData.output_y2);
							var width = x2 - x;
							var scale = origWidth / width;
							var height = height / scale;
							var diffY = ((y2 - y) - height) / 2;
							y = parseFloat(y) + diffY;
							var scale = 1;//parseInt($("input#column_width").val()) / (parseInt($("input#column_width").val()) + 10);
							
							width = (parseFloat(width)) * scale;
							height = (parseFloat(height)) * scale;
							x = (parseFloat(x)) * scale;
							y = (parseFloat(y)) * scale;
							y = y + 2;
							
							var imageScale = width / origWidth;
							// Custom scale factor
							if(el.scale_factor != null && !isNaN(parseInt(el.scale_factor))){
								imageScale = imageScale * (parseInt(el.scale_factor) / 100);
							}
			
							if(el.title != 'Base' && el.title != 'Overlay'){
								var scale = 1;//parseInt($("input#column_width").val()) / (parseInt($("input#column_width").val()) + 10);
								yourDesigner.setElementParameters({
									top: y + (height / 2),
									left: x + (width / 2),
									scaleX: imageScale,
									scaleY: imageScale,
									origScale: imageScale,
								}, el);
							} else {
								if(!editorMode){
									setTimeout(function(){
										yourDesigner.setElementParameters({
											draggable: false,
											copyable: false,
											resizable: false,
											rotatable: false,
											zChangeable: false,
											selectable: false,
											editable: false,
											locked: true
										}, el);
									}, 1000);
								}
							}
						}
						
						$yourDesigner.trigger('priceChange');
					});
					
					$yourDesigner.on('elementRemove', function(e, el){
						$yourDesigner.trigger('priceChange');
					});
					
					var fancyProductDesigner = $('#clothing-designer').data('instance');
					var $moduleClone = fancyProductDesigner.translatedUI.find('.fpd-modules > [data-module="manage-layers"]').clone();
					$moduleClone.appendTo($('.fpd-custom-layers-container')); //add module UI into custom HTML
					new FPDProductsModule(fancyProductDesigner, $moduleClone); //init JS Module
					
					var isTextCurveSwitcher = false;
					jQuery(document).on('click', '.fpd-curved-text-switcher', function(){
						isTextCurveSwitcher = true;
						setTimeout(function(){
							isTextCurveSwitcher = false;
						}, 2000);
					});
					
					$yourDesigner.on('layersListUpdate', function() {
						
						var currentElement = yourDesigner.currentElement;

						jQuery('.fpd-custom-layers-container [data-module="manage-layers"]').each(function(i, moduleWrapper) {

							if(typeof FancyProductDesigner !== 'undefined' && !isTextCurveSwitcher) {
								
								$(".fpd-element-toolbar-smart").appendTo($(".fpd-custom-layers-container"));

								var $moduleWrapper = $('.fpd-custom-layers-container');
								FPDLayersModule.createList(fancyProductDesigner, $moduleWrapper.children('.fpd-module'));

							} else if(isTextCurveSwitcher && currentElement && currentElement.id){
								$(".fpd-list-row[id="+currentElement.id+"]").remove();
							}

						});

// 						jQuery('.fpd-custom-layers-container [data-module="text-layers"]').each(function(i, moduleWrapper) {
// 
// 							if(typeof FancyProductDesigner !== 'undefined') {
// 
// 								var $moduleWrapper = $('.fpd-custom-layers-container');
// 								FPDTextLayersModule.createList(fancyProductDesigner, $moduleWrapper.children('.fpd-module'));
// 
// 							}
// 
// 						});

						$.each(yourDesigner.getElements(), function(index, element){
							if(element.type == 'image' && element.title != 'Base' && element.title != 'Overlay'){
								var row = $(".fpd-list-row[id="+element.id+"]");
								$(row).find('.fpd-cell-0').html('<span class="fa fa-image"></span>');
								$(row).find('.fpd-cell-1 .image-scale-percentage').remove();
								var scaleFactor = 1;
								if(element.origScale > 0 && element.scaleX > 0){
									scaleFactor = element.scaleX / element.origScale;
								}
								var scalePercentage = Math.round(scaleFactor * 100);
								$(row).find('.fpd-cell-1').append('<span class="image-scale-percentage"><input type="text" value="'+scalePercentage+'" />%</span>');
							}
						});

					});
					
					$yourDesigner.on('elementModify', function() {
						if(yourDesigner.currentElement == null){
							return;
						}
						var element = yourDesigner.currentElement;
						var row = $(".fpd-list-row#"+element.id);
						$(row).find('.fpd-cell-1 .image-scale-percentage').remove();
						var scaleFactor = 1;
						if(element.origScale > 0 && element.scaleX > 0){
							scaleFactor = element.scaleX / element.origScale;
						}
						var scalePercentage = Math.round(scaleFactor * 100);
						$(row).find('.fpd-cell-1').append('<span class="image-scale-percentage"><input type="text" value="'+scalePercentage+'" />%</span>');
					});
					
					$yourDesigner.on('elementSelect', function() {
						if(yourDesigner.currentElement == null){
							return;
						}
						console.log(yourDesigner.currentElement);
						
						var elementContainer = $(".fpd-list-row#"+yourDesigner.currentElement.id);
						$(".fpd-list-row").removeClass('active-row');
						$(elementContainer).addClass('active-row');
						$(".fpd-element-toolbar-smart").insertAfter($(elementContainer));
						console.log(elementContainer);
						
						if($(row).find('.fpd-cell-1 .image-scale-percentage').length == 0){
							var element = yourDesigner.currentElement;
							var row = $(".fpd-list-row#"+element.id);
							$(row).find('.fpd-cell-1 .image-scale-percentage').remove();
							var scaleFactor = 1;
							if(element.origScale > 0 && element.scaleX > 0){
								scaleFactor = element.scaleX / element.origScale;
							}
							var scalePercentage = Math.round(scaleFactor * 100);
							$(row).find('.fpd-cell-1').append('<span class="image-scale-percentage"><input type="text" value="'+scalePercentage+'" />%</span>');
						}
						
						// var fancyProductDesigner = $('#clothing-designer').data('instance');
						// var $moduleClone = fancyProductDesigner.translatedUI.find('.fpd-modules > [data-module="text"]').clone();
						// $moduleClone.insertAfter(elementContainer); //add module UI into custom HTML
						// new FPDProductsModule(fancyProductDesigner, $moduleClone); //init JS Module
					});
					
					$(document).on('click', '.fpd-view-stage canvas', function(){
						var el = canvasdesigner.currentElement;
						if(el != null && el.title != 'Base' && el.title != 'Overlay' && !editorMode && el.uploadZone == true){
							yourDesigner.setElementParameters({
								resizeToW: 1000
							}, el);
							$(".fpd-input-image").first().trigger('click');
						}
					});
					
					$(document).on('mouseup', '.fpd-navigation', function(e){
						if($(e.target).closest('div').data('module') == 'images'){
							$("input.fpd-input-image").first().trigger('click');
						}
						setTimeout(function(){
							$(".fpd-draggable-dialog.fpd-active").css({
								top: 'calc(50% - 225px)',
								position: 'fixed'
							});
						}, 100);
						setTimeout(function(){
							$(".fpd-draggable-dialog.fpd-active").css({
								top: 'calc(50% - 225px)',
								position: 'fixed'
							});
						}, 500);
					});
					
					setTimeout(function(){
						var savedData = JSON.parse($("input#saveddata").val());
						var count = 0;
						if(typeof(savedData) != 'undefined' && typeof(savedData.json) != 'undefined'){
							$.each(savedData.json, function(index, data){
								var view = canvasdesigner.viewInstances[count].stage;
								view.loadFromJSON(data, function() {
								   view.renderAll(); 
								},function(o,object){
				// 				   console.log(o,object);
								});
								count++;
							});
						}
						
						if($("input#is_engraving").val() == 'true' && $("input#disable_grayscale_filter").val() != '1'){
							if($("input#engrave_both_sides").val() != 'true'){
								//$(".fpd-view-stage").css('filter', 'grayscale(100%)');
								var views = yourDesigner.viewInstances;
								$.each(views, function(index, view){
									if(index == 0){
										view.options.customImageAjaxSettings.url = $("input#baseurl").val()+'productdesigner/index/upload?grayscale=false';
									}
								});
							} else {
								//$(".fpd-view-stage").last().css('filter', 'grayscale(100%)');
							}
						}
					}, 2000);
					
					setTimeout(function(){
						var savedData = JSON.parse($("input#saveddata").val());
						if(typeof(savedData) != 'undefined' && typeof(savedData.json) != 'undefined'){
							return false;
						}
			// 			if(editorMode){ return; }
						console.log(templatesData);
						var totalCounter = 0;
						$.each(templatesData, function(index, templateSides){
							var count = 0;
							if(index == $("input#autoload_template").val()){
								if(typeof(templateSides) != 'undefined' && templateSides.length > 0){
									isLoadingTemplate = true;
									setTimeout(function(){
										isLoadingTemplate = false;
									}, 5000);
									$.each(templateSides, function(count, data){
										if(count > 0){
											return true;
										}
										$($(".fpd-views-selection .fpd-item").get(count)).trigger('click');
										$($(".fpd-layouts-panel .fpd-grid .fpd-item").get(totalCounter)).trigger('click');
										$(".fpd-modal-wrapper:visible .fpd-confirm").trigger('click');
			/*
										var view = canvasdesigner.viewInstances[count].stage;
										var json = JSON.parse(data.json);
										view.loadFromJSON(json, function() {
										   view.renderAll(); 
										},function(o,object){
			// 							   console.log(o,object);
										});
			*/
										totalCounter++;
										count++;
									});
									$(".fpd-views-selection .fpd-item").first().trigger('click');
								}
							}
						});
					}, 2000);
					
					// Library images
					yourDesigner.setupDesigns(designs);
					
					$(".fpd-navigation > div").each(function(){
						if($(this).data('module') == 'products'){
							$(this).remove();
						}
						if($(this).data('module') == 'manage-layers'){
							$(this).remove();
						}
					});
					
					// Display surcharge amount below thumbs
					setTimeout(function(){
						var views = yourDesigner.viewInstances;
						$.each(views, function(index, view){
							var label = view.title;
							var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
							var surcharge = 0;
							var qty = 1;
							if(data.surcharge_table != '' && data.surcharge_table != null && typeof(data.surcharge_table) != 'undefined'){
								var surchargeTable = JSON.parse(data.surcharge_table);				
								$.each(surchargeTable, function(surchargeQty, surchargePrice){
									surchargeQty = parseFloat(surchargeQty);
									surchargePrice = parseFloat(surchargePrice);
									if(surchargeQty <= qty){
										surcharge = parseFloat(surchargePrice) * parseFloat(qty);
									}
								});
							} else if(data.surcharge > 0){
								surcharge = parseFloat(data.surcharge);
							}
							if(surcharge > 0){
								$($(".fpd-views-selection > div:visible").get(index)).append('<span class="surcharge">+'+surcharge.toFixed(2)+' €</span>');
							}
						});
					}, 2000);
					
					setTimeout(function(){
						yourDesigner.selectView(0);
						
						if($("input#disable_upload_for_second_side").val() == '1'){
							if(yourDesigner.currentViews[1] != null){
								yourDesigner.currentViews[1].options.customAdds.uploads = false
							}
						}
					}, 1500);
				});
				canvasdesigner = yourDesigner;
			
				//print button
				$('#print-button').click(function(){
					yourDesigner.print();
					return false;
				});
			
				//create an image
				$('#image-button').click(function(){
					var image = yourDesigner.createImage();
					return false;
				});
				
				$(document).on('click', '.fpd-module[data-module=layouts] .fpd.item', function(){
					isLoadingTemplate = true;
					setTimeout(function(){
						isLoadingTemplate = false;
					}, 5000);
				});
				
				$(document).on('click', '.fpd-confirm', function(){
					if($(".fpd-module.fpd-active[data-module=layouts]").length > 0){
						isLoadingTemplate = true;
						setTimeout(function(){
							isLoadingTemplate = false;
						}, 5000);
					}
				});
				
				$(document).on('click', '.fpd-curved-text-switcher', function(){
					var elementId = yourDesigner.currentElement.id;
					console.log(yourDesigner.currentElement);
					setTimeout(function(){
						jQuery('#clothing-designer').trigger('layersListUpdate');
					}, 1500);
					setTimeout(function(){
						jQuery('#clothing-designer').trigger('layersListUpdate');
						setTimeout(function(){
							$(".fpd-list-row#"+elementId).trigger('click');
							$(".fpd-tool-curved-text").trigger('click');
						}, 1000);
					}, 3000);
				});
				
				$(document).on('click', '.fpd-align-left', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						left: parseFloat(data.output_x1) + (el.getBoundingRect().width / 2)
					}, el);
				});
				$(document).on('click', '.fpd-align-top', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						top: parseFloat(data.output_y1) + (el.getBoundingRect().height / 2)
					}, el);
				});
				$(document).on('click', '.fpd-align-right', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						left: parseFloat(data.output_x2) - (el.getBoundingRect().width / 2)
					}, el);
				});
				$(document).on('click', '.fpd-align-bottom', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						top: parseFloat(data.output_y2) - (el.getBoundingRect().width / 2)
					}, el);
				});
				$(document).on('click', '.fpd-align-center-h', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						left: parseFloat(data.output_x1) + ((parseFloat(data.output_x2) - parseFloat(data.output_x1)) / 2),// + 75
					}, el);
				});
				$(document).on('click', '.fpd-align-center-v', function(){
					var el = yourDesigner.currentElement;
					var currentView = yourDesigner.currentViews[yourDesigner.currentViewIndex];
					var label = currentView.title;
					var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
					yourDesigner.setElementParameters({
						top: parseFloat(data.output_y1) + ((parseFloat(data.output_y2) - parseFloat(data.output_y1)) / 2),// + 100,
					}, el);
				});
			
				//checkout button with getProduct()
				$('#checkout-button').click(function(){
					// Check whether image has been added
					// if($("input#disable_upload").val() == '0'){
					// 	var views = yourDesigner.viewInstances;
					// 	var hasImage = false;
					// 	$.each(views, function(index, view){
					// 		$.each(view.getJSON().objects, function(index, element){
					// 			if(element.type == 'image' && element.title != 'Base' && element.title != 'Overlay'){
					// 				hasImage = true;
					// 			}
					// 		});
					// 	});
					// 	if(!hasImage){
					// 		if(!confirm($("input#no_image_uploaded_message").val())){
					// 			return;
					// 		}
					// 	}
					// }
					
					// Check if has designed anything at all
					var views = yourDesigner.viewInstances;
					var hasDesign = false;
					$.each(views, function(index, view){
						$.each(view.getJSON().objects, function(index, element){
							if((element.type == 'image' || element.type == 'text' || element.type == 'i-text' || element.type == 'path' || element.type == 'group') && element.title != 'Base' && element.title != 'Overlay' && !element.locked){
								hasDesign = true;
							}
						});
					});
					if(!hasDesign){
						if(!confirm($("input#no_image_uploaded_message").val())){
							return;
						}
					}
					
					// Add to cart progress dialog
					$("div.pd-cart-progress-container").show();
					
					var product = yourDesigner.getProduct();
					var svgData = yourDesigner.getViewsSVG();
					var jsonData = [];
					$.each(canvasdesigner.viewInstances, function(index, view){
						jsonData.push(view.getJSON());
					});
					//console.log(jsonData);
					
					var cart = {};
					var save = {};
					var count = 0;
					cart.productid = $("input#productid").val();
					cart.finalprice = 10;
					save.productid = $("input#productid").val();
					save.finalprice = 10;
					save.qty = parseInt($("input.fpd-qty").val());
					saveId = '';
					
					yourDesigner.getViewsDataURL(function(images) {
						$.each(product, function(index, productData){
							var x = 0;
							var y = 0;
							var outputX1 = 0;
							var outputY1 = 0;
							var outputX2 = 0;
							var outputY2 = 0;
							var svg = svgData[count];
							var json = JSON.stringify(jsonData[count]);
							var outputSvg = svgData[count];
							var elements = canvasdesigner.viewInstances[count].getJSON().objects;
							elements = [];
							cart[count] = { 'label': productData.title, 'svg': svg, 'json': json, 'image': productData.thumbnail, 'width': productData.options.stageWidth, 'height': productData.options.stageHeight, 'x': x, 'y': y, 'outputx1': outputX1, 'outputx2': outputX2, 'outputy1': outputY1, 'outputy2': outputY2, 'outputsvg': outputSvg, 'png': images[count], 'elements': elements };
							save[count] = { 'id': saveId, 'type': 'cart', 'label': productData.title, 'svg': svg, 'json': json, 'image': productData.thumbnail, 'width': productData.options.stageWidth, 'height': productData.options.stageHeight, 'x': x, 'y': y, 'outputx1': outputX1, 'outputx2': outputX2, 'outputy1': outputY1, 'outputy2': outputY2, 'outputsvg': outputSvg, 'png': images[count], 'elements': elements };
							count++;
						});
						save.number = count;
						cart.number = count;
						
						var reloadurl = $("input#baseurl").val()+'productdesigner/index/savedesign';
						$.ajax({
							type: "POST",
							url: reloadurl,
							data: { 'save': save},
							success: function(result) {
			// 					console.log(result);
								
								$.ajax({
									type: "POST",
									url: $("input#baseurl").val()+'productdesigner/index/checkconnectid',
									data: { connectid: result },
									dataType: "json",
									success: function(checkresult) {
										if(typeof(checkresult.count) != 'undefined' && checkresult.count > 0){
											// Add to cart when saved
											//var optiondata = $("form#pd-product-options").serializeArray();
											var optiondata = $("form#product_addtocart_form").serializeArray();
											var reloadurl = $("input#baseurl").val()+'productdesigner/index/addtocart';
											var carturl = $("input#baseurl").val()+'checkout/cart';
											var numberChains = 'false';
											if($("input#pd-number-chains").is(':checked')){
												numberChains = 'true';
											}
											$.ajax({
												type: "POST",
												url: reloadurl,
												data: { 'cart': cart, 'qty': parseInt($("input.fpd-qty").val()), 'connect_id': result, 'isupdatequoteitem': $('input#isUpdateQuoteItem').val(), 'number_chains': numberChains, surchargeAmount: $("input#surchargeAmount").val(), options: optiondata },
												success: function(result) {
													//$("div.pd-product-options").append("<br />Product was added to the cart.<br /><a href='"+carturl+"'>View cart</a>");
													window.location = carturl;
												}
											});
											// End add to cart
										} else {
											alert('Your design could not be saved, please try again.');
											$("div.pd-cart-progress-container").hide();
										}
									}
								});
							}
						});
					});
			
					return;
				});
				
				$(document).on('click', '.fpd-action-btn[data-action=reset-product]', function(){
					setTimeout(function(){
						$(".fpd-views-selection").insertBefore($('.product-info-main .fpd-topbar').first());
					}, 100);
				});
				
				$(document).on('change', 'input.fpd-qty', function(){
					$yourDesigner.trigger('priceChange');
					$.ajax({
						type: "POST",
						url: $("input#baseurl").val()+'estimatetimeshipping/estimation/quoteDate',
						data: {
							currentSku: $(".product.attribute.sku .value").text(),
							type: 'product',
							qty: $("input.fpd-qty").val(),
							address: '{"region_id":"","country_id":"","region":"","postcode":""}',
							method: ''
						},
						success: function(result) {
							$("#preparation-time").addClass('success').html(result.preparationDate);
						}
					});
				});
				$(".fpd-qty").trigger('change');
			
				//event handler when the price is changing
				$yourDesigner.on('priceChange', function(evt, price, currentPrice) {
					var views = canvasdesigner.viewInstances;
					var price = parseFloat($("input#productprice").val());
					var qty = parseInt($("input.fpd-qty").val());
					var tierPrices = JSON.parse($("input#tierprices").val());
					$(tierPrices).each(function(index, object){
						if(object.qty <= qty){
							price = parseFloat(object.price);
						}
					});
					
					var isCadeau = 0;
					var numberSurcharge = 0;
					$("input.product-custom-option").each(function(index, option){
						if($(option).next().text().indexOf('Pochette') > -1 && $(option).is(':checked')){
							isCadeau = parseFloat($(option).attr('price'));
						}
						if($(option).next().text().indexOf('numérotation') > -1 && $(option).is(':checked')){
							var percentage = parseFloat($(option).attr('price')) / parseFloat($("input#productprice").val());
							numberSurcharge = percentage * price;
						}
						if($(option).next().text().indexOf('numérotons') > -1 && $(option).is(':checked')){
							var percentage = parseFloat($(option).attr('price')) / parseFloat($("input#productprice").val());
							numberSurcharge = percentage * price;
						}
						if($(option).next().text().indexOf('numéroté') > -1 && $(option).is(':checked')){
							var percentage = parseFloat($(option).attr('price')) / parseFloat($("input#productprice").val());
							numberSurcharge = percentage * price;
						}
					});
					price = price + isCadeau + numberSurcharge;
					
					price = price * qty;
			
					$.each(views, function(index, view){
						var label = view.title;
						var data = $(".canvas-json-data[data-label="+label+"]").first().data('jsondata');
						var elements = view.getJSON().objects;
						var hasElements = false;
						$.each(elements, function(index, element){
							if(element.title != 'Base' && element.title != 'Overlay'){
								hasElements = true;
							}
						});
						if(hasElements){
							if(qty > 0 && data.surcharge_table != '' && data.surcharge_table != null && typeof(data.surcharge_table) != 'undefined'){
								var surcharge = 0;
								var surchargeTable = JSON.parse(data.surcharge_table);				
								$.each(surchargeTable, function(surchargeQty, surchargePrice){
									surchargeQty = parseFloat(surchargeQty);
									surchargePrice = parseFloat(surchargePrice);
									if(surchargeQty <= qty){
										surcharge = parseFloat(surchargePrice) * parseFloat(qty);
									}
								});
								price = price + surcharge;
							} else if(qty > 0 && data.surcharge > 0){
								price = price + (parseFloat(data.surcharge) * qty);
							}
						}
					});
					
					if($("input#store_id").val() == '3'){
						price = price / 1.2;
					}
					$('#thsirt-price').text(price.toFixed(2));
					
					$(".pd-item-price-insert").text((price / qty).toFixed(2));
					
					getShippingRate(qty);
				});
				
				$("input.product-custom-option").bind('change', function(){
					$yourDesigner.trigger('priceChange');
				});
				
				$(".qty-changer span.qty-up").click(function(){
					var curVal = parseFloat($(".fpd-qty").val());
					curVal += 1;
					$(".fpd-qty").val(curVal).trigger('change');
				});
				$(".qty-changer span.qty-down").click(function(){
					var curVal = parseFloat($(".fpd-qty").val());
					curVal -= 1;
					if(curVal < 1){
						curVal = 1;
					}
					$(".fpd-qty").val(curVal).trigger('change');
				});
				
				$(document).on('change keyup', '.fpd-module textarea', function(){
					removeInvalidChars(this);
				});
				
				$(document).on('mousedown', '.pd-make-upload-zone', function(e){
					var image = canvasdesigner.currentElement;
					image.uploadZone = true;
					console.log(image);
					
					yourDesigner.setElementParameters({
						uploadZone: true,
						scaleMode: 'cover',
						copyable: false,
						resizable: false,
						rotatable: false,
						zChangeable: false,
					}, image);
					
					return false;
				});
				
				$(document).on('mousedown', '.pd-make-fixed-position', function(e){
					var element = canvasdesigner.currentElement;
			/*
					element.draggable = false;
					element.copyable = false;
					element.resizable = false;
					element.rotatable = false;
					element.zChangeable = false;
			*/
					
					yourDesigner.setElementParameters({
						draggable: false,
						copyable: false,
						resizable: false,
						rotatable: false,
						zChangeable: false,
						locked: true,
						lockMovementX: true,
						lockMovementY: true
					}, element);
					
					console.log(element);
					return false;
				});
			
			});
			
			function padLeft(nr, n, str){
				return Array(n-String(nr).length+1).join(str||'0')+nr;
			}
			
			function removeInvalidChars(input) {
				var ranges = [
					'\ud83c[\udf00-\udfff]', // U+1F300 to U+1F3FF
					'\ud83d[\udc00-\ude4f]', // U+1F400 to U+1F64F
					'\ud83d[\ude80-\udeff]'  // U+1F680 to U+1F6FF
				];
				var str = $(input).val();
				
			//	str = str.replace(new RegExp(ranges.join('|'), 'g'), '');
				str = str.replace(/(?:[\u2700-\u27bf]|(?:\ud83c[\udde6-\uddff]){2}|[\ud800-\udbff][\udc00-\udfff]|[\u0023-\u0039]\ufe0f?\u20e3|\u3299|\u3297|\u303d|\u3030|\u24c2|\ud83c[\udd70-\udd71]|\ud83c[\udd7e-\udd7f]|\ud83c\udd8e|\ud83c[\udd91-\udd9a]|\ud83c[\udde6-\uddff]|\ud83c[\ude01-\ude02]|\ud83c\ude1a|\ud83c\ude2f|\ud83c[\ude32-\ude3a]|\ud83c[\ude50-\ude51]|\u203c|\u2049|[\u25aa-\u25ab]|\u25b6|\u25c0|[\u25fb-\u25fe]|\u00a9|\u00ae|\u2122|\u2139|\ud83c\udc04|[\u2600-\u26FF]|\u2b05|\u2b06|\u2b07|\u2b1b|\u2b1c|\u2b50|\u2b55|\u231a|\u231b|\u2328|\u23cf|[\u23e9-\u23f3]|[\u23f8-\u23fa]|\ud83c\udccf|\u2934|\u2935|[\u2190-\u21ff])/g, '')
				$(input).val(str);
			}
			
			var getShippingRateVar = false;
			function getShippingRate(qty){
				getShippingRateVar = qty;
			}
			setInterval(function(){
				if(getShippingRateVar !== false){
					var qty = getShippingRateVar+'';
					getShippingRateVar = false;
					$.ajax({
						url: $("#baseurl").val()+'advancedshipping/index/getrate/',
						method: 'POST',
						data: {
							qty: qty,
							product_id: $("#productid").val()
						},
						success: function(data){
							$(".shipping-price span").html(data);
						}
					});
				}
			}, 1000);
		});
		
		if($("input#enable_designer").val() == '1'){
			if($("input#isUpdateQuoteItem").val() > 0){
				$(".open-product-designer").trigger('click');
			}
		}
		
		$(document).on('focus', ".fpd-list-row textarea", function(){
			$(this).closest(".fpd-list-row").trigger('click');
		});
		
		$(document).on('focus', ".image-scale-percentage input", function(){
			$(".image-scale-percentage input").select();
		});
		
		$(document).on('change', ".image-scale-percentage input", function(){
			var percentage = parseInt($(this).val());
			if(isNaN(percentage)){
				$(this).val(100).trigger('keyup');
			}
			var scaleFactor = 1;
			var el = yourDesigner.currentElement;
			if(el.origScale > 0){
				scaleFactor = percentage / 100;
				var newScaleFactor = el.origScale * scaleFactor;
				yourDesigner.setElementParameters({
					scaleX: newScaleFactor,
					scaleY: newScaleFactor,
				}, el);
			}
		});
		
		$(document).on('mouseenter', ".gallery-images .gallery-image", function(){
			$(".gallery-active-image-container").show();
			$(".gallery-active-image-container").css('backgroundImage', 'url("'+$(this).data('url')+'")');
		});
		$(document).on('mouseleave', ".gallery-images .gallery-image", function(){
			$(".gallery-active-image-container").hide();
		});
	});
});


(function orionFixUploadInputs() {
	const applyFix = () => {
	  const inputs = document.querySelectorAll('input[type="file"]');
	  inputs.forEach(input => {
		if (!input.hasAttribute('data-orion-fixed')) {
			input.setAttribute('accept', 'image/*');
			input.removeAttribute('capture'); // <-- ici le changement !
			input.setAttribute('data-orion-fixed', 'true');
			console.log('[ORION] Patch "accept only" appliqué 📁📸');
		}
	  });
	};
  
	// Applique tout de suite si le DOM est prêt
	if (document.readyState === "complete" || document.readyState === "interactive") {
	  applyFix();
	} else {
	  document.addEventListener("DOMContentLoaded", applyFix);
	}
  
	// Observe les ajouts dynamiques
	const observer = new MutationObserver(applyFix);
	observer.observe(document.body, { childList: true, subtree: true });
  })();
  