<?php
namespace CielIntegration\TabPanelContentsRewriter {

    use voku\helper\SimpleHtmlDomInterface;

	interface TabContentsPlacement {
		function addTabContents($header, $content);

		function render(SimpleHtmlDomInterface $headerContainer, SimpleHtmlDomInterface $contentsContainer);

		function clear();
	}
}