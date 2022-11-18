<?php
function myc_status_label($status, $statusYes, $statusNo) {
	return $status
		? '<label class="label label-success">' . $statusYes . '</label>' 
		: '<label class="label label-danger">' . $statusNo . '</label>';
}

function myc_checked_attr($checked) {
	return $checked 
		? 'checked="checked"' 
		: '';
}
