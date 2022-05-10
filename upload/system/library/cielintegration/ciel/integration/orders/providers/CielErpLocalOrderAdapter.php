<?php
namespace Ciel\Api\Integration\Orders\Providers {

	interface CielErpLocalOrderAdapter {
		function setDocumentRemovedForOrder($localId);

		function setRemoteDocumentCancelledForOrder($localId);

		function isDocumentIssuedForOrder($localId);

		function canDocumentBeIssuedForOrder($localId);

		function canOrderItemsBeAddedToDocument($localId);

		function setDocumentIssuedForOrder($localId, $documentId, $documentType);

		function lookupRemoteDocumentDataForOrder($localId);

		function getOrderData($localId);

		/**
		 * @param int $localId 
		 * @return \Ciel\Api\Integration\Orders\DocumentPreRequisiteStatus
		 */
		function determineOrderDocumentPreRequisitesStatus($localId);

		/**
		 * @return bool
		 */
		function isBatchDischargeSupported();

		/**
		 * @return bool
		 */
		function isDocumentCancellationSupported();
	}
}