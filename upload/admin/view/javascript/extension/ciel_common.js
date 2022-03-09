(function($) {
	"use strict";

	$.getCielActionUrl = function($target) {
		return $target.attr('data-action-url');
	}

	$.delayedReloadCielPage = function(timeoutSeconds) {
		window.setTimeout(function() {
			$.showCielLoading();
			window.location.reload();
		}, timeoutSeconds * 1000);
	};
})(jQuery);