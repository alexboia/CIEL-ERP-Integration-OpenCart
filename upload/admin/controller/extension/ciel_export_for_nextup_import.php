<?php

use Ciel\Api\Integration\Articles\CielErpArticleExportParameters;
use CielIntegration\CielController;
use CielIntegration\CsvDownloader;
use CielIntegration\Integration\Admin\WithCielIntegration;
use CielIntegration\WithAdminLayoutLoader;

class ControllerExtensionCielExportForNextupImport extends CielController {
	use WithAdminLayoutLoader;
	use WithCielIntegration;

	const ARTICLES_CSV_BASE_FILE_NAME = 'magento-nextup-articles-export';

	public function __construct(\Registry $registry) {
		parent::__construct($registry);
		$this->_setTextDomain('extension/ciel_export_for_nextup_import');
	}

	public function index() {
		$this->_setDocumentTitleLangKey('ciel_export_for_nextup_import_title');
		$this->_addStylesheet('extension/ciel_common.css');

		$viewData = $this->_loadAdminLayout();

		$viewData['ciel_export_for_nextup_import_title'] = 
			$this->_t('ciel_export_for_nextup_import_title');
		$viewData['ciel_export_for_nextup_import_explanation'] = 
			$this->_t('ciel_export_for_nextup_import_explanation');
		$viewData['ciel_export_for_nextup_import_info'] = 
			$this->_t('ciel_export_for_nextup_import_info');

		$viewData['ciel_export_for_nextup_import_btn_action'] = 
			$this->_createRouteUrl('extension/ciel_export_for_nextup_import/export');
		$viewData['ciel_export_for_nextup_import_btn_text'] = 
			$this->_t('ciel_export_for_nextup_import_btn_text');

		$viewData['txt_cancel_action'] = 
			$this->_t('button_cancel');
		$viewData['url_cancel_action'] = 
			$this->_createRouteUrl('common/dashboard');

		$viewData['html_breadcrumbs'] = 
			$this->_renderBreadcrumbsToIndex();

		//Render view
		$this->_renderViewToResponseOutput('extension/ciel_export_for_nextup_import', 
			$viewData);
	}

	private function _renderBreadcrumbsToIndex() {
		return $this->_renderBreadcrumbs($this->_getBreadcrumbsData());
	}

	private function _getBreadcrumbsData() {
		$breadcrumbs = $this->_getBaseBreadcrumbs();
		$breadcrumbs[] = array(
			'text' => $this->_t('ciel_export_for_nextup_import_title'),
			'href' => $this->_createRouteUrl('extension/ciel_export_for_nextup_import')
		);

		return array(
			'breadcrumbs' => $breadcrumbs
		);
	}

	public function export() {
		$articlesCsvData = $this->_exportArticlesForCielImport();
		$this->_downloadExportedArticlesCsv($articlesCsvData);
	}

	private function _exportArticlesForCielImport() {
		$params = new CielErpArticleExportParameters();
		return $this->_getArticleIntegration()
			->exportArticlesForCielImport($params);
	}

	private function _downloadExportedArticlesCsv($csvData) {
		$this->_getCsvFileDownloader()
			->sendCsv(self::ARTICLES_CSV_BASE_FILE_NAME, $csvData);
	}

	private function _getCsvFileDownloader() {
		return new CsvDownloader();
	}
}