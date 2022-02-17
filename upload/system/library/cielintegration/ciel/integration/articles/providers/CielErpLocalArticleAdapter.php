<?php
namespace Ciel\Api\Integration\Articles\Providers {
	interface CielErpLocalArticleAdapter {
		function createArticleFromRemoteData($remoteArticleData);

		function connectArticleAndUpdateWithRemoteData($localId, $remoteArticleData);

		function disconnectArticle($localId);

		function isArticleConnected($localId);

		function updateArticleFromRemoteData($localId, $remoteArticleData);

		function updateStocksForConnectedArticle($localId, $remoteArticleStockData);

		function updateStocksForAllConnectedArticles($remoteArticlesStockData);

		function updateAllConnectedArticlesFromRemoteData($remoteArticlesData);

		function lookupLocalArticleCode($localId);

		function getBatchTrackingStatusForSingleArticle($localId);

		function disconnectAllArticles();

		function getAllLocalArticles();

		function getAllLocalArticlesForExport();
	}
}