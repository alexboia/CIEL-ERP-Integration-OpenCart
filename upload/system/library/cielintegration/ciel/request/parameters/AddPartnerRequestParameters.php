<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class AddPartnerRequestParameters extends CielRequestParameters {
        private $_partner = null;

        public function setPartner($val) {
            $this->_partner = $val;
            return $this;
        }

        public function getParams() {
            return array(
                'partner' => $this->_partner
            );
        }
    }
}