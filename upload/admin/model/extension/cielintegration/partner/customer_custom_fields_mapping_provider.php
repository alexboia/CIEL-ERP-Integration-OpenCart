<?php
namespace CielIntegration\Integration\Admin\Partner {

    use CielIntegration\Integration\Admin\IntegrationService;

	class CustomerCustomFieldsMappingProvider extends IntegrationService {
		public function getCustomFieldsMapping() {
			$workflow = $this->_getWorkflow();
			return array(
				'vat_code_field_id' => $workflow->getVatCodeCustomFieldId(),
				'reg_com_number_field_id' => $workflow->getRegComNumberCustomFieldId(),
				'bank_account_field_id' => $workflow->getBankAccountCustomFieldId(),
				'bank_name_field_id' => $workflow->getBankNameCustomFieldId()
			);
		}
	}
}