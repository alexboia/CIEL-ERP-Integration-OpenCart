<?php
namespace Ciel\Api\Integration\Binding\Providers {
    interface CielErpToStoreBindingAdapter {
        /**
         * @param array $data 
         * @return bool
         */
        function saveBindingData(array $data);

        /**
         * @return array
         */
        function getBindingData();

        /**
         * @return void
         */
        function clearBindingData();

        /**
         * @return \Ciel\Api\CielCache
         */
        function getCache();

        /**
         * @return \Ciel\Api\CielConfig
         */
        function getConfig();

        /**
         * @return \Ciel\Api\Session\CielClientSessionProvider
         */
        function getClientSessionProvider();
    }
}