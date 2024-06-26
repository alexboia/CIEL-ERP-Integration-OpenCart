<?php
namespace MyClar\ManualBuilder {

    use ParsedownExtra;

	class ContentProvider {
		const DEFAULT_ENCODING = 'UTF-8';

		/**
		 * @var Manifest
		 */
		private $_manifest;

		/**
		 * @var ParsedownExtra
		 */
		private $_parser;

		public function __construct(Manifest $manifest) {
			$this->_manifest = $manifest;	
			$this->_parser = $this->_createParser();
		}

		private function _createParser(): ParsedownExtra {
			$parser = new ParsedownExtra();
			$parser->setBreaksEnabled(true);
			$parser->setUrlsLinked(true);
			return $parser;
		}

		public function readPages(): ManualPageCollection {
			$pages = new ManualPageCollection();
			$pageDescriptors = $this->_manifest
				->getPageDescriptors();

			foreach ($pageDescriptors as $pd) {
				$filePath = $this->_manifest->locatePage($pd);
				if (!$filePath) {
					continue;
				}

				$pageTitle = !empty($pd['title'])
					? $pd['title']
					: '';

				$pageOrder = $pages->getCount() 
					+ 1;

				$pageName = $this->_determinePageName($pd);
				$pageContent = $this->_getHtmlPageContent($filePath);

				if (!empty($pageContent)) {
					$page = new ManualPage(
						$pageName,
						$pageTitle, 
						$pageOrder, 
						$pageContent
					);

					$pages->addPage($page);
				}
			}

			return $pages;
		}

		private function _determinePageName(array $pageDescriptor):string {
			return str_ireplace('.md', '', $pageDescriptor['file']);
		}

		private function _getHtmlPageContent($filePath): string {
			$pageContent = file_get_contents($filePath);
			$pageContent = mb_convert_encoding($pageContent, self::DEFAULT_ENCODING);
			return $this->_parser->parse($pageContent);
		}
	}
}