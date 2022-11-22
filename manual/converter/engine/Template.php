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

		public function render(string $outputType, string $name, array $data): string {
			$filePath = $this->_locateTemplateFile($outputType, $name);
			if (empty($filePath)) {
				throw new Exception('Template ' . $name . ' not found.');
			}

			if (empty($data) || empty($data['contents'])) {
				$data['contents'] = '';
			}

			if (empty($data) || empty($data['page_contents'])) {
				$data['page_contents'] = '';
			}

			$viewVariables = $this->_getViewVariablesToSet($outputType);	
			$data = $this->_preRenderPageContents($data, 
				$viewVariables);
			$data = array_merge($viewVariables, 
				$data);

			ob_start();

			extract($data, EXTR_PREFIX_ALL, 'mp');
			require $filePath;

			return ob_get_clean();
		}

		private function _getViewVariablesToSet(string $outputType): array {
			$viewVariables = $this->_manifest->getViewVariablesToSet();
			if ($outputType == OutputType::Pdf) {
				$viewVariables['img_base_url'] = $this->_manifest->getInputImagesDirectory();
			}
			return $viewVariables;
		}

		private function _preRenderPageContents(array $data, array $viewVariables) {
			if (!empty($data['page_contents'])) {
				foreach ($viewVariables as $key => $value) {
					$search = sprintf('$%s$', $key);
					$data['page_contents'] = str_replace($search, $value, $data['page_contents']);
				}
			}
			return $data;
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