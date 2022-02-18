<?php
namespace CielIntegration\ListingContentsRewriter {
    use voku\helper\SimpleHtmlDomInterface;

	interface ColumnContentsPlacement {
		function addColumnContents($columnContents);

		function render(SimpleHtmlDomInterface $row);

		function clear();
	}
}