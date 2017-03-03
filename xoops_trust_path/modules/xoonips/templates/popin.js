/**
 * popin jquery library
 *
 * based on prettyPopin 1.3 by Stephane Caron (http://www.no-margin-for-errors.com)
 */

(function($) {

$.fn.<{$mytrustdirname}>Popin = function(settings) {

	settings = jQuery.extend({
		modal : false, /* true/false */
		width : false, /* false/integer */
		height: false, /* false/integer */
		opacity: 0.7, /* value from 0 to 1 */
		animationSpeed: 'medium', /* slow/medium/fast/integer */
		followScroll: true, /* true/false */
		close: true, /* true/false */
		overflow: 'hidden', /* scroll/hidden */
		loader_path: '<{$smarty.const.XOOPS_MODULE_URL}>/<{$xoops_dirname}>/image.php/loader.gif', /* path to your loading image */
		callback: function() {} /* callback called when closing the popin */
	}, settings);

	return this.each(function() {
		var popinWidth;
		var popinHeight;
		var popinIndex;
		var $overlay;
		var $popin;
		var $c;
		$(this).click(function() {
			popinIndex = $('div.<{$mytrustdirname}>Popin').length;
			buildoverlay();
			buildpopin();
			// Load the content
			$.get($(this).attr('href'), function(responseText) {
				$c.html(responseText);
				// This block of code is used to calculate the width/height of the popin
				popinWidth = settings.width || $(window).width() - parseFloat($c.css('padding-left')) - parseFloat($c.css('padding-right'));
				popinHeight = settings.height || $(window).height() - parseFloat($c.css('padding-top')) - parseFloat($c.css('padding-bottom'));
				// Now reset the width/height
				$popin.height(45).width(45);
				displayPopin();
			});
			return false;
		});
		$(window).scroll(function() { centerPopin(); });
		$(window).resize(function() { centerPopin(); });

		var displayPopin = function() {
			var scrollPos = _getScroll();
			var projectedTop = ($(window).height() / 2) + scrollPos['scrollTop'] - (popinHeight / 2);
			if (projectedTop < 0) {
				projectedTop = 10;
			};
			$popin.animate({
				'top': projectedTop,
				'left': ($(window).width() / 2) + scrollPos['scrollLeft'] - (popinWidth / 2),
				'width' : popinWidth,
				'height' : popinHeight
			}, settings.animationSpeed, function() {
				displayContent();
			});
		};

		var buildpopin = function() {
                        $('body').append('<div class="<{$mytrustdirname}>Popin"><a href="#" class="b_close" rel="close">Close</a><div class="popinContent"><img src="' + settings.loader_path + '" alt="Loading" class="loader" /><div class="popinContent-container"></div></div></div>');
			$popin = $('div.<{$mytrustdirname}>Popin:last');
			$popin.css('zIndex', 10001 + (popinIndex * 2));
			$c = $popin.find('.popinContent-container'); // The content container
			$popin.find('[rel=close]:eq(0)').click(function() { closeOverlay(); return false; });
			// Disable scroll if overflow is 'hidden'
			if (settings.overflow == 'hidden') {
				$popin.on('scroll touchmove mousewheel', function(e){
					e.preventDefault();
					e.stopPropagation();
					return false;
				});
			}
			var scrollPos = _getScroll();
			// Show the popin
			$popin.width(45).height(45).css({
				'zIndex': 10001 + (popinIndex * 2),
				'top': ($(window).height() / 2) + scrollPos['scrollTop'],
				'left': ($(window).width() / 2) + scrollPos['scrollLeft']
			}).hide().fadeIn(settings.animationSpeed);
		};

		var buildoverlay = function() {
			$('body').append('<div class="<{$mytrustdirname}>Overlay"></div>');
			$overlay = $('div.<{$mytrustdirname}>Overlay:last');
			// Set the proper height
			$overlay.css('height', $(document).height());
			$overlay.css('zIndex', 10000 + (popinIndex * 2));
			// Fade it in
			$overlay.css('opacity', 0).fadeTo(settings.animationSpeed, settings.opacity);
			if (!settings.modal) {
				$overlay.click(function() {
					closeOverlay();
				});
			};
			// Disable scroll
			$overlay.on('scroll touchmove mousewheel', function(e){
				return false;
			});
		};

		var displayContent = function() {
			$popin.find('.loader').hide();
			if (settings.close) {
				$popin.find('[rel=close]:eq(0)').show();
			}
			$c.fadeIn(function() {
				// Focus on the first form input if there's one
				$c.find('input[type=text]:first').trigger('focus');
				// Check for paging
				$c.find('a[rel=internal]').click(function() {
					$link = $(this);
					// Fade out the current content
					$c.fadeOut(function() {
						$c.parent().find('.loader').show();
						// Submit the form
						$.get($link.attr('href'), function(responseText) {
							// Replace the content
							$c.html(responseText);
							refreshContent($c);
						});
					});
					return false;
				});
				// Submit the form in ajax
				$c.find('form').bind('submit', function() {
					$theForm = $(this);
					if ($theForm.attr('target') != undefined) {
						// Submit original request if target attribute is defined
						return true;
					}
					// Fade out the current content
					$c.fadeOut(function() {
						$popin.find('.loader').show();
						// Submit the form
						$.post($theForm.attr('action'), $theForm.serialize(), function(responseText) {
							// Replace the content
							$c.html(responseText);
							refreshContent($c);
						});
					});
					return false;
				});
			});
			$c.find('[rel=close]').click(function() { closeOverlay(); return false; });
			$c.find('[rel=callback]').click(function() { closeOverlay(); executeCallback(); return false; });
		};

		var refreshContent = function() {
			var scrollPos = _getScroll();
			if (!settings.width) popinWidth = $(window).width() - parseFloat($c.css('padding-left')) - parseFloat($c.css('padding-right'));
			if (!settings.height) popinHeight = $(window).height() - parseFloat($c.css('padding-top')) - parseFloat($c.css('padding-bottom'));
			var projectedTop = ($(window).height() / 2) + scrollPos['scrollTop'] - (popinHeight / 2);
			if (projectedTop < 0) {
				projectedTop = 10;
			};
			$popin.animate({
				'top': projectedTop,
				'left': ($(window).width() / 2) + scrollPos['scrollLeft'] - (popinWidth / 2),
				'width' : popinWidth,
				'height' : popinHeight
			}, settings.animationSpeed, function() {
				displayContent();
			});
		};

		var closeOverlay = function() {
			$overlay.fadeOut(settings.animationSpeed, function() { $(this).remove(); });
			$popin.fadeOut(settings.animationSpeed, function() { $(this).remove(); });
			$overlay = null;
			$popin = null;
			$c = null;
		};

		var executeCallback = function() {
			settings.callback($('#<{$mytrustdirname}>_callbackid').val(), $('#<{$mytrustdirname}>_callbackvalue').val());
		};

		var centerPopin = function() {
			// Make sure the popin exist
			if (!$popin) return;
			var scrollPos = _getScroll();
			var projectedTop = ($(window).height() / 2) + scrollPos['scrollTop'] - ($popin.height() / 2);
			var followScroll = settings.followScroll;
			if (projectedTop < 0) {
				projectedTop = 10;
				followScroll = false;
			};
			if (followScroll) {
				$popin.css({
					'top': projectedTop,
					'left': ($(window).width() / 2) + scrollPos['scrollLeft'] - ($popin.width() / 2)
				});
			}
			if (!settings.width) popinWidth = $(window).width() - parseFloat($c.css('padding-left')) - parseFloat($c.css('padding-right'));
			if (!settings.height) popinHeight = $(window).height() - parseFloat($c.css('padding-top')) - parseFloat($c.css('padding-bottom'));
			$popin.css({
				'width' : popinWidth,
				'height' : popinHeight
			});
		};

	});

	function _getScroll() {
		var scrollTop = window.pageYOffset || document.documentElement.scrollTop || 0;
		var scrollLeft = window.pageXOffset || document.documentElement.scrollLeft || 0;
		return {scrollTop:scrollTop, scrollLeft:scrollLeft};
	};
};

})(jQuery);
