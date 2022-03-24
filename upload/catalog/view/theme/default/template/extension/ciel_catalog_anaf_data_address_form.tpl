<script type="text/javascript">
	window['myc_custom_fields_mapping'] = JSON.parse('<?php echo json_encode($myc_custom_fields_mapping); ?>');
	window['myc_vat_code_lookup_action_url'] = '<?php echo $myc_vat_code_lookup_action_url; ?>';
</script>

<script type="text/javascript">
	(function($) {
		$(document).ready(function() {
			$('#content').cielCatalogAnafData();
		});
	})(jQuery);
</script>