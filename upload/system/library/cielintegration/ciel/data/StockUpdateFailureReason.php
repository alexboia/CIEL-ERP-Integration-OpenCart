<?php
namespace Ciel\Api\Data {
    class StockUpdateFailureReason {
        const NoStockData = 'no-stock-data';

        const NotConnected = 'not-connected';

        const NotManagingStock = 'not-managing-stock';
    }
}