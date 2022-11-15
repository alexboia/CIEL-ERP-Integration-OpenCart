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

$_['option_txt_product_sync_mode_all_info'] = 'Toate informatiile';
$_['option_txt_product_sync_mode_stocks_only'] = 'Doar stocurile';

//
// Sidebar widgets
//

$_['lbl_ciel_document_issued_percentage'] = 'Documente emise NextUp';

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
$_['lbl_connection_timeout_seconds_field'] = 'Timeout (secunde)';
$_['txt_placeholder_connection_timeout_seconds_field'] = 'Timpul, masurat in secunde, dupa care conexiunea este intrerupta daca nu a fost stabilita cu succes';

$_['text_runtime_settings_form_heading'] = 'Opțiuni Integrare NextUp ERP';
$_['mgs_err_fill_in_valid_warehouse'] = 'Trebuie să competezi un depozit valid.';
$_['mgs_err_fill_in_valid_document_type'] = 'Trebuie să completezi un tip de document valid.';
$_['msg_err_fill_in_valid_stock_update_mode'] = 'Trebuie să completezi un mod valid de actualizare a stocurilor.';
$_['msg_err_fill_in_valid_document_status_type'] = 'Trebuie să completezi un status valid de document.';
$_['msg_err_fill_in_valid_document_due_days'] = 'Trebuie să completezi un număr valid de zile de scadență.';
$_['msg_err_fill_in_valid_shipping_vat_quota'] = 'Trebuie să completezi o cotă validă de TVA pentru transport.';
$_['lbl_runtime_warehouse_field'] = 'Depozit';
$_['txt_placeholder_runtime_warehouse_field'] = 'Depozit';
$_['lbl_runtime_issue_doctype_field'] = 'Emite document';
$_['txt_placeholder_runtime_issue_doctype_field'] = 'Emite document în NextUp pentru fiecare comandă din magazin';
$_['lbl_runtime_issue_auto_order_status_field'] = 'Emite automat document din NextUp pentru aceste statusuri ale comenzilor din magazin';
$_['lbl_runtime_remove_auto_order_status_field'] = 'Șterge automat document din NextUp pentru aceste statusuri ale comenzilor din magazin';
$_['lbl_runtime_issue_doc_status_field'] = 'Status document emis';
$_['txt_placeholder_runtime_issue_doc_status_field'] = 'Documentele se emit cu acest status';
$_['lbl_runtime_issue_doc_due_days_field'] = 'Scadența document emis (în zile)';
$_['txt_placeholder_runtime_issue_doc_due_days_field'] = 'Scadența document emis (în zile)';
$_['lbl_runtime_use_company_billing_fields_field'] = 'Se folosesc câmpuri de facturare pe persoană juridică';
$_['lbl_runtime_shipping_vat_quota_field'] = 'Cota TVA transport';
$_['txt_placeholder_runtime_shipping_vat_quota_field'] = 'Cota TVA transport';
$_['lbl_runtime_stock_update_mode_field'] = 'Modalitate actualizare stoc';

$_['text_workflow_settings_form_heading'] = 'Opțiuni Flux de lucru NextUp ERP - OpenCart';
$_['msg_err_fill_in_valid_in_stock_status_id'] = 'Te rugăm să completezi o valoare validă pentru statusul produselor care se află-n stoc.';
$_['msg_err_fill_in_valid_out_of_stock_status_id'] = 'Te rugăm să completezi o valoare validă pentru statusul produselor care nu se află-n stoc.';
$_['lbl_add_vat_on_payment_to_document_field'] = 'Se aplică TVA la încasare';
$_['lbl_add_shipping_to_document_field'] = 'Evidențiază transportul pe document';
$_['lbl_in_stock_status_id_field'] = 'Status stoc pentru produse care se află-n stoc';
$_['lbl_out_of_stock_status_id_field'] = 'Status stoc pentru produse epuizate din stoc';
$_['lbl_pf_customer_group_id_field'] = 'Grup de clienți folosit pentru clienți persoane fizice';
$_['lbl_pj_customer_group_id_field'] = 'Grup de clienți folosit pentru clienți persoane juridice';
$_['lbl_vat_code_custom_field_id_field'] = 'Câmp custom folosit pentru CUI';
$_['lbl_reg_com_number_custom_field_id_field'] = 'Câmp custom folosit pentru nr. înreg. Reg. Com.';
$_['lbl_bank_account_custom_field_id_field'] = 'Câmp custom folosit pentru IBAN-ul contului bancar';
$_['lbl_bank_name_custom_field_id_field'] = 'Câmp custom folosit pentru numele băncii';
$_['lbl_new_tax_rate_customer_group_id_field'] = 'Grupul de clienți folosit pentru cotele de taxare importate din NextUp';
$_['lbl_new_tax_rate_geo_zone_id_field'] = 'Zona geografică folosită pentru produsele importate din NextUp';
$_['lbl_new_product_weight_class_id_field'] = 'Clasa de greutate folosită pentru produsele importate din NextUp';
$_['lbl_new_product_length_class_id_field'] = 'Clasa de lungime folosită pentru produsele importate din NextUp';

//
// CIEL product form tab
// 

$_['lbl_product_connected_to_ciel_erp'] = 'Conectat la NextUp ERP';
$_['lbl_product_ciel_erp_article_id'] = 'ID articol NextUp ERP';
$_['lbl_product_ciel_erp_vat_option_name'] = 'Opțiune TVA NextUp';
$_['lbl_product_ciel_erp_vat_quota_value'] = 'Cota TVA NextUp ERP';
$_['lbl_product_ciel_erp_batch_tracking_enabled'] = 'Se folosește urmărire pe loturi';
$_['lbl_product_actions'] = 'Acțiuni disponibile';
$_['lbl_product_action_update_full'] = 'Actualizează toate informațiile din NextUp ERP';
$_['lbl_product_action_update_stocks'] = 'Actualizează doar stocurile din NextUp ERP';
$_['lbl_product_action_connect'] = 'Conectează la NextUp ERP folosind codul SKU';
$_['msg_product_no_actions_available'] = 'Nicio acțiune disponibilă în acest moment.';
$_['msg_product_action_store_not_bound'] = 'Este necesară configurarea extensiei! Pentru a putea efectua sincronizarea produselor, trebuie mai întâi să configurezi această extensie.';
$_['msg_product_no_sku'] = 'Produsul nu are un SKU asociat, deci nu poate fi conectat de un articol corespunzător din NextUp ERP pe baza codului.';

//
// CIEL customer form tab
//

$_['lbl_customer_connected_to_ciel_erp'] = $_['lbl_product_connected_to_ciel_erp'];
$_['lbl_customer_ciel_erp_partner_code'] = 'Cod partener NextUp';
$_['lbl_customer_ciel_erp_partner_address_worksite_id'] = 'ID punct de lucru NextUp';
$_['msg_customer_action_store_not_bound'] = 'Este necesară configurarea extensiei! Pentru a putea efectua sincronizarea clientilor, trebuie mai întâi să configurezi această extensie.';

//
// CIEL order view panel
//

$_['lbl_subsection_products_not_connected_title'] = 'Produse neconectate la NextUp ERP ';
$_['lbl_subsection_integration_status_title'] = 'Informații stare integrare NExtUp ERP';
$_['lbl_missing_product_placeholder'] = 'Produsul cu ID-ul %d lipsește';
$_['msg_order_cant_issue_not_configured'] = 'Emiterea de documente este deazactivata prin configuratia modulului.';
$_['msg_order_cant_issue_not_all_products_connected'] = 'Nu poate fi emis documentul deoarece nu toate produsele sunt conectate la NextUp ERP.';
$_['msg_order_cant_issue_batch_tracking_not_posssible'] = 'Nu poate fi emis documentul deoarece comanda are produse care folosesc urmărire pe loturi, dar depozitul selectat nu este de tip En-Gross.';
$_['msg_order_cant_issue_batch_tracking_not_available'] = 'Nu poate fi emis documentul deoarece comanda are produse care folosesc urmărire pe loturi, dar licența ta nu permite emiterea de documente cu articole ce au urmărire pe loturi.';
$_['lbl_order_ciel_erp_document_issued'] = 'Document emis în NextUp';
$_['lbl_order_ciel_erp_document_type'] = 'Tip document emis în NextUp';
$_['lbl_order_actions'] = $_['lbl_product_actions'];
$_['lbl_order_action_issue_document'] = 'Emite document în NextUp';
$_['lbl_order_action_remove_document'] = 'Șterge document din NextUp';
$_['msg_order_no_actions_available'] = 'Nicio acțiune disponibilă în acest moment.';
$_['msg_order_action_store_not_bound'] = 'Este necesară configurarea extensiei! Pentru a putea efectua sincronizarea comenzilor, trebuie mai întâi să configurezi această extensie.';