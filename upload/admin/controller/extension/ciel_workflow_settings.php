<?php
use CielIntegration\CielController;

class ControllerExtensionCielWorkflowSettings extends CielController {
	public function index($data = array()) {
		$data = array_merge($data, 
			$this->_getWorkflowSettingsFormData());

		$data = array_merge($data, array(
			'text_workflow_settings_form_heading' => $this->_t('text_workflow_settings_form_heading')
		));

		return $this->_renderView('extension/ciel_workflow_settings_form', 
			$data);
	}

	private function _getWorkflowSettingsFormData() {
		$workflow = $this->_getWorkflow();
		$storeBinding = $this->_getStoreBinding();

		$data = array(
			'has_connection' => false,
			'wf_in_stock_status_id' => 0,
			'wf_out_of_stock_status_id' => 0,
			'stock_statuses' => $this->_getOpenCartStockStatuses()
		);

		if ($storeBinding->hasConnectionInfo()) {
			$data = array_merge($data, array(
				'has_connection' => true,
				'wf_in_stock_status_id' => $workflow->getInStockStatusId(),
				'wf_out_of_stock_status_id' => $workflow->getOutOfStockStatusId()
			));
		}

		return $data;
	}
}