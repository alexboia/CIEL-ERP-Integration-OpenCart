<?php
namespace MyClar\ManualBuilder {

    use Exception;

	class Template {
		/**
		 * @var Manifest
		 */
		private $_manifest;

		public function __construct(Manifest $manifest) {
			$this->_manifest = $manifest;	
		}

		public function render(string $type, string $name, array $data): string {
			$filePath = $this->_locateTemplateFile($type, $name);
			if (empty($filePath)) {
				throw new Exception('Template ' . $name . ' not found.');
			}

			if (empty($data) || empty($data['contents'])) {
				$data['contents'] = '';
			}

			ob_start();

			extract($data, EXTR_PREFIX_ALL, 'mp_');
			require $filePath;

			return ob_get_clean();
		}

		private function _locateTemplateFile(string $type, string $name): string {
			$dir = $this->_manifest->locateTemplateDirectory($type);
			if (!empty($dir)) {
				$filePath = realpath($dir . DIRECTORY_SEPARATOR . $name . '.phtml');
				if (!$filePath) {
					$filePath = realpath($dir . DIRECTORY_SEPARATOR . 'page.phtml');
				}
			} else {
				$filePath = null;
			}

			return $filePath;
		}
	}
}