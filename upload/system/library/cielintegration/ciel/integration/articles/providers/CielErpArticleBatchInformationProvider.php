<?php
namespace Ciel\Api\Integration\Articles\Providers {
	interface CielErpArticleBatchInformationProvider {
		function getBatchesForArticle($code);

		function getBatchesForArticles(array $codes);
	}
}