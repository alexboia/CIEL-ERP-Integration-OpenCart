<script type="text/javascript">
	(function($) {
		"use strict";
		
		$(document).ready(function() {
			$('#payment-new').cielCatalogAnafData({
				sel_vat_code_input_prefix: '#input-payment-custom-field',
				sel_input_company: '#input-payment-company',
				sel_input_post_code: '#input-payment-postcode',
				sel_input_address_1: '#input-payment-address-1'
			});
		});
	})(jQuery);
</script>