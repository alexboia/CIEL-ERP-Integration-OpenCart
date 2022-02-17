<?php
namespace Ciel\Api\Data {
    class WarehouseType {
        const TypeIdEnDetail = 'En-Detail';

        const TypeIdEnGross = 'En-Gross';

        private $_typeId;

        private $_supportsSaleOrder;

        private $_supportsSaleInvoice;

        private function __construct($typeId, $supportsSaleOrder, $supportsSaleInvoice) {
            $this->_typeId = $typeId;
            $this->_supportsSaleOrder = $supportsSaleOrder;
            $this->_supportsSaleInvoice = $supportsSaleInvoice;
        }

        public static function enDetail() {
            return new WarehouseType(self::TypeIdEnDetail, false, true);
        }

        public static function enGross() {
            return new WarehouseType(self::TypeIdEnGross, true, true);
        }

        public static function getSupportedTypeIds() {
            return array(
                self::TypeIdEnDetail,
                self::TypeIdEnGross
            );
        }

        public static function isTypeIdSupported($typeId) {
            return in_array($typeId, self::getSupportedTypeIds());
        }

        public static function parse($typeId) {
            $typeId = strtolower($typeId);
            if ($typeId == strtolower(self::TypeIdEnDetail)) {
                return self::enDetail();
            } else if ($typeId == strtolower(self::TypeIdEnGross)) {
                return self::enGross();
            } else {
                return null;
            }
        }

        public function asPlainObject() {
            $data = new \stdClass();
            $data->typeId = $this->_typeId;
            $data->supportsSaleOder = $this->_supportsSaleOrder;
            $data->supportsSaleInvoice = $this->_supportsSaleInvoice;
            return $data;
        }

        public function typeId() {
            return  $this->_typeId;
        }

        public function supportsSaleOrder() {
            return  $this->_supportsSaleOrder;
        }

        public function supportsSaleInvoice() {
            return $this->_supportsSaleInvoice;
        }

        public function isEnGross() {
            return $this->typeId() == self::TypeIdEnGross;
        }

        public function isEnDetail() {
            return $this->typeId() == self::TypeIdEnDetail;
        }
    }
}