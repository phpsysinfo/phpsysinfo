/*
 * jQuery ifixpng plugin
 * (previously known as pngfix)
 * Version 2.1  (23/04/2008)+jquery1.9fix
 * @requires jQuery v1.1.3 or above
 *
 * Examples at: http://jquery.khurshid.com
 * Copyright (c) 2007 Kush M.
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
 
 /**
  *
  * @example
  *
  * optional if location of pixel.gif if different to default which is images/pixel.gif
  * $.ifixpng('media/pixel.gif');
  *
  * $('img[@src$=.png], #panel').ifixpng();
  *
  * @apply hack to all png images and #panel which icluded png img in its css
  *
  * @name ifixpng
  * @type jQuery
  * @cat Plugins/Image
  * @return jQuery
  * @author jQuery Community
  */
 
(function($) {

	/**
	 * helper variables and function
	 */
	$.ifixpng = function(customPixel) {
		if (customPixel !== undefined) {
		    $.ifixpng.pixel = customPixel;
		}
	};
	
	$.ifixpng.getPixel = function() {
		return $.ifixpng.pixel || 'images/pixel.gif';
	};

	var hack = {
		ltie7  : (navigator.userAgent.match(/MSIE ((5\.5)|(6\.))/) !== null),
		filter : function(src) {
			return "progid:DXImageTransform.Microsoft.AlphaImageLoader(enabled=true,sizingMethod=scale,src='"+src+"')";
		}
	};
	
	/**
	 * Applies ie png hack to selected dom elements
	 *
	 * $('img[@src$=.png]').ifixpng();
	 * @desc apply hack to all images with png extensions
	 *
	 * $('#panel, img[@src$=.png]').ifixpng();
	 * @desc apply hack to element #panel and all images with png extensions
	 *
	 * @name ifixpng
	 */
	
	$.fn.ifixpng =  hack.ltie7 ? function(customPixel) {
	    if (customPixel !== undefined) {
	        $.ifixpng.pixel = customPixel;
	    }
    	return this.each(function() {

            var filter = $(this).css('filter');
            if (!filter.match(/src=["']?(.*\.png([?].*)?)["']?/i)) { // if not yet executed
    			// in case rewriting urls
	    		var base = $('base').attr('href');
		    	if (base) {
			    	// remove anything after the last '/'
			    	base = base.replace(/\/[^\/]+$/,'/');
			    }
	    		if ($(this).is('img') || $(this).is('input')) { // hack image tags present in dom
		    		if ($(this).attr('src')) {
			    		if ($(this).attr('src').match(/.*\.png([?].*)?$/i)) { // make sure it is png image
				    		// use source tag value if set 
					    	var source = (base && $(this).attr('src').search(/^(\/|http:)/i)) ? base + $(this).attr('src') : $(this).attr('src');
	    					// apply filter
		    				$(this).css({filter:hack.filter(source), width:$(this).width(), height:$(this).height()})
			    			  .attr({src:$.ifixpng.getPixel()})
				    		  .positionFix();
			    		}
		    		}
	    		} else { // hack png css properties present inside css
		    		var image = $(this).css('backgroundImage');
	    			if (image.match(/^url\(["']?(.*\.png([?].*)?)["']?\)$/i)) {
		    			image = RegExp.$1;
			    		image = (base && image.substring(0,1)!='/') ? base + image : image;
		    			$(this).css({backgroundImage:'none', filter:hack.filter(image)})
		    			  .children().children().positionFix();
		    		}
			    }
		    }
		});
	} : function() { return this; };
	
	/**
	 * Removes any png hack that may have been applied previously
	 *
	 * $('img[@src$=.png]').iunfixpng();
	 * @desc revert hack on all images with png extensions
	 *
	 * $('#panel, img[@src$=.png]').iunfixpng();
	 * @desc revert hack on element #panel and all images with png extensions
	 *
	 * @name iunfixpng
	 */
	 
	$.fn.iunfixpng = hack.ltie7 ? function() {
    	return this.each(function() {
			var src = $(this).css('filter');
			if (src.match(/src=["']?(.*\.png([?].*)?)["']?/i)) { // get img source from filter
				src = RegExp.$1;
				if ($(this).is('img') || $(this).is('input')) {
					$(this).attr({src:src}).css({filter:''});
				} else {
					$(this).css({filter:'', background:'url('+src+')'});
				}
			}
		});
	} : function() { return this; };
	
	/**
	 * positions selected item relatively
	 */
	 
	$.fn.positionFix = function() {
		return this.each(function() {
			var position = $(this).css('position');
			if (position != 'absolute' && position != 'relative') {
				$(this).css({position:'relative'});
			}
		});
	};

})(jQuery);