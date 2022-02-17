<?php 
namespace Ciel\Api\Exception {
    class ArticleCodeAlreadyExistsException extends CielException {
        private $_articleCode;

        public function __construct($articleCode) {
            parent::__construct();
            $this->_articleCode = $articleCode;
        }

        public function getArticleCode() {
            return $this->_articleCode;
        }
    }
}