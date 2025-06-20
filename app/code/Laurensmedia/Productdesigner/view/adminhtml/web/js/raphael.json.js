require([
	'jquery',
	'Laurensmedia_Productdesigner/js/raphael-min',
], function($, RaphaelCustom){
	if(typeof(window.Raphael) == 'undefined'){
		window.Raphael = RaphaelCustom;
	}
/*
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/mit-license.php
 *
 */

(function() {
	Raphael.fn.toJSON = function(callback) {
		var
			data,
			elements = new Array,
			paper    = this
			;

		for ( var el = paper.bottom; el != null; el = el.next ) {
			var type = el.type;
			if(el.data('dragbox') != '' && typeof(el.data('dragbox')) !== 'undefined'){
			} else {

				if(el){
					data = callback ? callback(el, new Object) : new Object;

					// Zelf toegevoegd
					var addData = {};
					if(el.data('font')){
						addData.font = el.data('font');
						addData.color = el.data('color');
						addData.text = el.data('text');
					}
					
					if(typeof(el.data('isobject')) !== 'undefined'){
						addData.isobject = el.data('isobject');
					}
					
					if(typeof(el.data('isbackground')) !== 'undefined'){
						addData.isbackground = el.data('isbackground');
					}
					
					if(typeof(el.data('overlayimage')) !== 'undefined'){
						addData.overlayimage = el.data('overlayimage');
					}
					
					if(typeof(el.data('isactive')) !== 'undefined'){
						addData.isactive = el.data('isactive');
					}
					
					if(typeof(el.data('dragbox')) !== 'undefined'){
						addData.dragbox = el.data('dragbox');
					}
					
					if(typeof(el.data('isStroked')) !== 'undefined'){
						addData.isStroked = el.data('isStroked');
					}
					
					if(jQuery(el.node).attr('stroke-width') > 0){
						el.attrs['stroke-width'] = jQuery(el.node).attr('stroke-width');
					}
					
					if(typeof(el.data('isstatic')) !== 'undefined'){
						addData.isstatic = el.data('isstatic');
					}

					if ( data ) elements.push({
						data:      data,
						type:      el.type,
						attrs:     el.attrs,
						transform: el.matrix.toTransformString(),
						addData:   addData
						});
				}
			}
		}
		return JSON.stringify(elements);
	}

	Raphael.fn.fromJSON = function(json, callback) {
		var
			el,
			paper = this
			;

		if ( typeof json === 'string' ) json = JSON.parse(json);

		for ( var i in json ) {
			if ( json.hasOwnProperty(i) ) {
				el = paper[json[i].type]()
					.attr(json[i].attrs)
					.transform(json[i].transform)
					;

				if(json[i].addData != 'undefined'){
					jQuery.each(json[i].addData, function(index, value){
						el.data(index, value);
					});
				}

				if ( callback ) el = callback(el, json[i].data);

				if ( el ) paper.set().push(el);
			}
		}
	}
})();

});