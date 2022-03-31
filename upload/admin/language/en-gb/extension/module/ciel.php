<?php
$_['text_module'] = 'Module';
$_['heading_title'] = 'CIEL ERP Integration';

//
// Common stuff
//

$_['lbl_txt_yes'] = 'Yes';
$_['lbl_txt_no'] = 'No';
$_['lbl_txt_none'] = 'None';

$_['lbl_column_product_connected_to_ciel_erp_header'] = 'Connected to CIEL ERP';
$_['lbl_column_customer_connected_to_ciel_erp_header'] = 'Connected to CIEL ERP';
$_['lbl_column_order_connected_to_ciel_erp_header'] = 'Document issued in CIEL ERP';

$_['option_txt_document_status_type_valid'] = 'Valid';
$_['option_txt_document_status_type_temporary'] = 'Temporary';

$_['option_txt_stock_update_manual'] = 'Manual';
$_['option_txt_stock_update_system_cron'] = 'System Cron';

$_['option_txt_document_type_none'] = 'Do not issue any documents';
$_['option_txt_document_type_sale_order'] = 'Sale order';
$_['option_txt_document_type_sale_invoice'] = 'Sale invoice';

//
// Ciel integration settings page
//

$_['ciel_settings_page_title'] = 'Configure CIEL ERP Integration';

$_['text_connection_settings_form_heading'] = 'CIEL ERP Connection Options';
$_['msg_connection_test_ok'] = 'The connection and authentication have been successfully established';
$_['msg_connection_test_failed'] = 'The connection could not be established or authentication failed';
$_['msg_err_fill_in_connection_properties'] = 'Please fill in all the connection properties';
$_['button_test_connection'] = 'Test connection';
$_['lbl_connection_endpoint_url_field'] = 'Webservice URL';
$_['txt_placeholder_connection_endpoint_url_field'] = 'CIEL server webservice URL';
$_['lbl_connection_username_field'] = 'Username';
$_['txt_placeholder_connection_username_field'] = 'Username used for CIEL webserivce server logon';
$_['lbl_connection_password_field'] = 'Password';
$_['txt_placeholder_connection_password_field'] = 'Password used for CIEL webserivce server logon';
$_['lbl_connection_society_code_field'] = 'Society code';
$_['txt_placeholder_connection_society_code_field'] = 'Society code bound to this store';

$_['text_runtime_settings_form_heading'] = 'CIEL ERP Integration Options';
$_['mgs_err_fill_in_valid_warehouse'] = 'Please fill in a valid warehouse.';
$_['mgs_err_fill_in_valid_document_type'] = 'Please fill in a valid document type.';
$_['msg_err_fill_in_valid_stock_update_mode'] = 'Please fill in a valid stock update mode.';
$_['msg_err_fill_in_valid_document_status_type'] = 'Please fill in a valid document status type.';
$_['msg_err_fill_in_valid_document_due_days'] = 'Please fill in a valid document due days interval.';
$_['msg_err_fill_in_valid_shipping_vat_quota'] = 'Please fill in a valid shipping VAT quota.';
$_['lbl_runtime_warehouse_field'] = 'Warehouse';
$_['txt_placeholder_runtime_warehouse_field'] = 'Warehouse';
$_['lbl_runtime_issue_doctype_field'] = 'Issue document type';
$_['txt_placeholder_runtime_issue_doctype_field'] = 'Issue document type for each order';
$_['lbl_runtime_issue_auto_order_status_field'] = 'Automatically issue document for these order statuses';
$_['lbl_runtime_remove_auto_order_status_field'] = 'Automatically remove document for these order statuses';
$_['lbl_runtime_issue_doc_status_field'] = 'Issue document with status';
$_['txt_placeholder_runtime_issue_doc_status_field'] = 'When issuing a document for an order, use this status';
$_['lbl_runtime_issue_doc_due_days_field'] = 'Document due days';
$_['txt_placeholder_runtime_issue_doc_due_days_field'] = 'Document due days';
$_['lbl_runtime_use_company_billing_fields_field'] = 'Use company billing fields';
$_['lbl_runtime_shipping_vat_quota_field'] = 'Shipping VAT quota';
$_['txt_placeholder_runtime_shipping_vat_quota_field'] = 'Shipping VAT quota';
$_['lbl_runtime_stock_update_mode_field'] = 'Stock update mode';

$_['text_workflow_settings_form_heading'] = 'CIEL ERP OpenCart Workflow Options';
$_['msg_err_fill_in_valid_in_stock_status_id'] = 'Please fill in a valid in-stock status id.';
$_['msg_err_fill_in_valid_out_of_stock_status_id'] = 'Please fill in a valid out-of-stock status id.';
$_['lbl_add_shipping_to_document_field'] = 'Add shipping to document';
$_['lbl_in_stock_status_id_field'] = 'Stock status for in-stock products';
$_['lbl_out_of_stock_status_id_field'] = 'Stock status for out-of-stock products';
$_['lbl_pf_customer_group_id_field'] = 'Client group used for individuals';
$_['lbl_pj_customer_group_id_field'] = 'Client group used for legal persons';
$_['lbl_vat_code_custom_field_id_field'] = 'Custom field used for VAT Code entry';
$_['lbl_reg_com_number_custom_field_id_field'] = 'Custom field used for reg. com. number entry';
$_['lbl_bank_account_custom_field_id_field'] = 'Custom field used for bank account (IBAN) entry';
$_['lbl_bank_name_custom_field_id_field'] = 'Custom field used for bank name entry';
$_['lbl_new_tax_rate_customer_group_id_field'] = 'Customer group used for CIEL-imported new tax rates';
$_['lbl_new_tax_rate_geo_zone_id_field'] = 'Geozone used for CIEL-imported new tax rates';
$_['lbl_new_product_weight_class_id_field'] = 'Weight class used for CIEL-imported new products';
$_['lbl_new_product_length_class_id_field'] = 'Length class used for CIEL-imported new products';