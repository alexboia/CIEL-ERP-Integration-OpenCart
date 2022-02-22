(function($) {
	"use strict";

	$.showCielLoading = function() {
		$('#ciel_modal_loading_indicator').modal({
			backdrop: "static",
			keyboard: false,
			show: true
		});
	}

	$.hideCielLoading = function() {
		$('#ciel_modal_loading_indicator').modal('hide');
	}
})(jQuery);