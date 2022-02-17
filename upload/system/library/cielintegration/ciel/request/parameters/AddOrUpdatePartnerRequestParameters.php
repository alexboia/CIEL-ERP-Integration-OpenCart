<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class AddOrUpdatePartnerRequestParameters extends CielRequestParameters {
		private $_modifyExistingPartners = false;

		private $_partner = null;

		public function setModifyExistingPartners($val) {
			$this->_modifyExistingPartners = $val;
			return $this;
		}

		public function setPartner($val) {
			$this->_partner = $val;
			return $this;
		}

		public function getParams() {
			return array(
				'modifyExistingPartners' => $this->_modifyExistingPartners,
				'partner' => $this->_partner
			);
		}
	}
}