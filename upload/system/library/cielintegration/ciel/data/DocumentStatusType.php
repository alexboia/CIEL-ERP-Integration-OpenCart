<?php
namespace Ciel\Api\Data {
    class DocumentStatusType {
        const Valid = 0x01;
        
        const Temporary = 0x02;

        public static function getSupportedDocumentStatusTypeIds() {
            return array(
                self::Valid, 
                self::Temporary
            );
        }

        public static function isDocumentStatusTypeSupported($typeId) {
            return !empty($typeId) && in_array($typeId, self::getSupportedDocumentStatusTypeIds());
        }
    }
}