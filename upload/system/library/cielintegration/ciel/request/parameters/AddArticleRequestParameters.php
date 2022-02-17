<?php
namespace Ciel\Api\Request\Parameters {
	use Ciel\Api\Request\CielRequestParameters;

	class AddArticleRequestParameters extends CielRequestParameters {
		private $_article = null;

		public function setArticle($val) {
			$this->_article = $val;
			return $this;
		}

		public function getParams() {
			return array(
				'article' => $this->_article
			);
		}
	}
}