<?php
namespace Ciel\Api\Request\Parameters {

    use Ciel\Api\Request\CielRequestParameters;

    class UpdateArticleRequestParameters extends CielRequestParameters {
        private $_id = null;
        
        private $_article = null;

        public function setId($val)  {
            $this->_id = $val;
            return $this;
        }

        public function setArticle($val) {
            $this->_article = $val;
            return $this;
        }

        public function getParams() {
            return array(
                'id' => $this->_id,
                'article' => $this->_article
            );
        }
    }
}