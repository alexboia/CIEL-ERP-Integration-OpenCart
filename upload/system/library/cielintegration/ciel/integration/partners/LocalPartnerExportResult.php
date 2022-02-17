<?php
namespace Ciel\Api\Integration\Partners {
    class LocalPartnerExportResult {
        private $_remotePartnerData;

        private $_remotePartnerShopBillingAddress;

        public function __construct($remotePartnerData, $remotepartnerShopBillingAddress) {
            $this->_remotePartnerData = $remotePartnerData;
            $this->_remotePartnerShopBillingAddress = $remotepartnerShopBillingAddress;
        }

        public function getRemotePartnerData() {
            return $this->_remotePartnerData;
        }

        public function getRemotePartnerShopBillingAddressData() {
            return $this->_remotePartnerShopBillingAddress;
        }
    }
}