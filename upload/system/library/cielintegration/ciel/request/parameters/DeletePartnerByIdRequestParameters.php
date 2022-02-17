<?php
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class DeletePartnerByIdRequestParameters extends CielRequestParameters {
        private $_id = null;

        public function setId($val) {
            $this->_id = $val;
            return $this;
        }

        public function getParams() {
           return array(
               'id' => $this->_id
           );
        }
    }
}