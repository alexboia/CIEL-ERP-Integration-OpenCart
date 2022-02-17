<?php 
namespace Ciel\Api {
    interface CielConfig {
        function getShippingArticleCode();

        function getDiscountArticleCodeFormat();

        function getDiscountArticleNameFormat();

        function getWarehousesCacheDuration();

        function getVatQuotasCacheDuration();

        function getWarehouseDisplayLabelFormat();

        function isPriceUpdateEnabled();

        function isTaxRatesUpdateEnabled();

        function isStockUpdateEnabled();

        function usePhoneForPartnerMatching();
    }
}