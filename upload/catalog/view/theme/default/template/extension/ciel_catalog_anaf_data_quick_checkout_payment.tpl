<script type="text/javascript">
	window['myc_custom_fields_mapping'] = JSON.parse('<?php echo json_encode($myc_custom_fields_mapping); ?>');
	window['myc_vat_code_lookup_action_url'] = '<?php echo $myc_vat_code_lookup_action_url; ?>';
</script>
<script type="text/javascript">
	(function($) {
		"use strict";

		$(document).ready(function() {
			$('#address-modal-form').cielCatalogAnafData({
				sel_vat_code_input_prefix: '#input-custom-field',
				sel_input_company: '#input-company',
				sel_input_post_code: '#input-postcode',
				sel_input_address_1: '#input-address-1',
				sel_submit_address_form: '#btn-address-save'
			});
		});
	})(jQuery);
</script>