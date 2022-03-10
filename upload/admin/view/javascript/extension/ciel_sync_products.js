(function($) {
	"use strict";

	function _initTooltips() {
		$('[data-toggle=\'tooltip\']').tooltip({
			container: 'body',
			html: true
		});
	}

	$(document).ready(function() {
		_initTooltips();
	});
})(jQuery);