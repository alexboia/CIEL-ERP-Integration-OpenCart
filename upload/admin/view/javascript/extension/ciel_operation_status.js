(function($) {
	"use strict";

	var ACTION_SHOW = 'show';
	var ACTION_HIDE = 'hide';

	$.fn.cielOperationStatus = function(action) {
		var $me = $(this);

		function _clear() {
			$me.html('');
		}

		function _show(success, message) {
			_clear();

			var html = [];
			if (success) {
				html.push('<div class="alert alert-success"><i class="fa fa-check-circle"></i>');
			} else {
				html.push('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>');
			}

			html.push(message);
			html.push('<button type="button" class="close" data-dismiss="alert">&times;</button>');
			html.push('</div>');

			$me.html(html.join(''))
				.show();
		}

		function _hide() {
			_clear();
			$me.hide();
		}

		if (action == ACTION_SHOW) {
			if (arguments.length != 3) {
                throw new Error('Invalid parameters provided.');
            }

			var success = arguments[1];
            var message = arguments[2];

			_show(success, message);
		} else if (action == ACTION_HIDE) {
			_hide();
		}

		return this;
	};
})(jQuery);