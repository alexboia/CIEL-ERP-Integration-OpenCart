<?php
$_['text_module'] = 'Modul';
$_['heading_title'] = 'Integrare NextUp ERP';

//
// Common stuff
//

$_['text_home'] = 'Pagina principală';
$_['lbl_txt_yes'] = 'Da';
$_['lbl_txt_no'] = 'Nu';
$_['lbl_txt_none'] = 'Nimic';

$_['lbl_column_product_connected_to_ciel_erp_header'] = 'Conectat la NextUp ERP';
$_['lbl_column_customer_connected_to_ciel_erp_header'] = 'Conectat la NextUp ERP';
$_['lbl_column_order_connected_to_ciel_erp_header'] = 'Document emis în NextUp ERP';

$_['lbl_tab_product_label'] = 'Integrare NextUp ERP';
$_['lbl_tab_customer_label'] = $_['lbl_tab_product_label'];
$_['lbl_tab_order_label'] = $_['lbl_tab_product_label'];

$_['option_txt_document_status_type_valid'] = 'Valid';
$_['option_txt_document_status_type_temporary'] = 'Temporar';

$_['option_txt_stock_update_manual'] = 'Manual';
$_['option_txt_stock_update_system_cron'] = 'Cron sistem';

$_['option_txt_document_type_none'] = 'Nu emite niciun documents';
$_['option_txt_document_type_sale_order'] = 'Comandă de vânzare';
$_['option_txt_document_type_sale_invoice'] = 'Factura de vânzare';


//
// Ciel integration settings page
//

$_['ciel_settings_page_title'] = 'Configurare Integrare NextUp ERP';

$_['text_connection_settings_form_heading'] = 'Opțiuni Conexiune NextUp ERP';
$_['msg_connection_test_ok'] = 'Conexiunea și autentificarea s-au desfășurat cu succes';
$_['msg_connection_test_failed'] = 'Conexiunea nu a putut fi stabilită sau autentificarea a eșuat';
$_['msg_err_fill_in_connection_properties'] = 'Te rugăm să completezi proprietățile conexiunii';
$_['button_test_connection'] = 'Testează conexiune';
$_['lbl_connection_endpoint_url_field'] = 'URL Webservice';
$_['txt_placeholder_connection_endpoint_url_field'] = 'URL server NextUp';
$_['lbl_connection_username_field'] = 'Nume utilizator';
$_['txt_placeholder_connection_username_field'] = 'Username-ul folosit pentru autentificarea la server-ul NextUp';
$_['lbl_connection_password_field'] = 'Parola';
$_['txt_placeholder_connection_password_field'] = 'Parola folosit pentru autentificarea la server-ul NextUp';
$_['lbl_connection_society_code_field'] = 'Cos societate';
$_['txt_placeholder_connection_society_code_field'] = 'Codul de societate folosit pentru acest magazin';

$_['text_runtime_settings_form_heading'] = 'Opțiuni Integrare NextUp ERP';
$_['mgs_err_fill_in_valid_warehouse'] = 'Trebuie să competezi un depozit valid.';
$_['mgs_err_fill_in_valid_document_type'] = 'Trebuie să completezi un tip de document valid.';
$_['msg_err_fill_in_valid_stock_update_mode'] = 'Please fill in a valid stock update mode.';
$_['msg_err_fill_in_valid_document_status_type'] = 'Please fill in a valid document status type.';
$_['msg_err_fill_in_valid_document_due_days'] = 'Please fill in a valid document due days interval.';
$_['msg_err_fill_in_valid_shipping_vat_quota'] = 'Please fill in a valid shipping VAT quota.';
$_['lbl_runtime_warehouse_field'] = 'Depozit';
$_['txt_placeholder_runtime_warehouse_field'] = 'Depozit';
$_['lbl_runtime_issue_doctype_field'] = 'Emite document';
$_['txt_placeholder_runtime_issue_doctype_field'] = 'Emite document în NextUp pentru fiecare comandă din magazin';
$_['lbl_runtime_issue_auto_order_status_field'] = 'Automatically issue document for these order statuses';
$_['lbl_runtime_remove_auto_order_status_field'] = 'Automatically remove document for these order statuses';
$_['lbl_runtime_issue_doc_status_field'] = 'Status document emis';
$_['txt_placeholder_runtime_issue_doc_status_field'] = 'When issuing a document for an order, use this status';
$_['lbl_runtime_issue_doc_due_days_field'] = 'Scadența document emis (în zile)';
$_['txt_placeholder_runtime_issue_doc_due_days_field'] = 'Scadența document emis (în zile)';
$_['lbl_runtime_use_company_billing_fields_field'] = 'Use company billing fields';
$_['lbl_runtime_shipping_vat_quota_field'] = 'Shipping VAT quota';
$_['txt_placeholder_runtime_shipping_vat_quota_field'] = 'Shipping VAT quota';
$_['lbl_runtime_stock_update_mode_field'] = 'Stock update mode';