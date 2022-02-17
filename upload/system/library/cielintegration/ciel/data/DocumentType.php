<?php
namespace Ciel\Api\Data {
    class DocumentType {
        const None = 'none';

        const SaleOrder = 'sale-order';

        const SaleInvoice = 'sale-invoice';

        const SalePreInvoice = 'sale-preinvoice';

        public static function getSupportedTypeIds() {
            return array(
                self::None,
                self::SaleOrder, 
                self::SaleInvoice,
                self::SalePreInvoice
            );
        }

        public static function isTypeIdSupported($typeId) {
            return in_array($typeId, self::getSupportedTypeIds());
        }
    }
}