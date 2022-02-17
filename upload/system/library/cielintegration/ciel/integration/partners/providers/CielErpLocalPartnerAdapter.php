<?php
namespace Ciel\Api\Integration\Partners\Providers {
	interface CielErpLocalPartnerAdapter {
		function getAllLocalPartnersForExport();

		function removeCustomAddressBillingDataForAllCustomers();

		function connectWithRemotePartner($localPartnerId, $remotePartnerData, $remoteShopBillingAddressData);

		function connectOrderWithRemotePartner($localOrderId, $remotePartnerData, $remoteShopBillingAddressData);

		function connectOrderFromLocalPartnerConnectionInfo($localOrderId);

		function getPartnerDataForOrder($localOrderId);

		function getPartnerData($localPartnerId);
	}
}