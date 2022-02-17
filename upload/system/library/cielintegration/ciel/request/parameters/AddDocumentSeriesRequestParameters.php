<?php 
namespace Ciel\Api\Request\Parameters {
    use Ciel\Api\Request\CielRequestParameters;

    class AddDocumentSeriesRequestParameters extends CielRequestParameters {
        private $_prefix;

        private $_startNumber = 1;

        private $_endNumber = 1000000;

        private $_nextNumber = 1;

        private $_blocked = false;

        private $_description = null;

        public function setPrefix($prefix) {
            $this->_prefix = $prefix;
            return $this;
        }

        public function setStartNumber($startNumber) {
            $this->_startNumber = $startNumber;
            return $this;
        }

        public function setEndNumber($endNumber) {
            $this->_endNumber = $endNumber;
            return $this;
        }

        public function setNextNumber($nextNumber) {
            $this->_nextNumber = $nextNumber;
            return $this;
        }

        public function setBlocked($blocked) {
            $this->_blocked = $blocked;
            return $this;
        }

        public function setDescription($description) {
            $this->_description = $description;
            return $this;
        }

        public function getParams() {
            $params = array(
                'Prefix' => $this->_prefix,
                'StartNumber' => $this->_startNumber,
                'EndNumber' => $this->_endNumber,
                'NextNumber' => $this->_nextNumber,
                'Blocked' => $this->_blocked
            );

            if (!empty($this->_description)) {
                $params['Description'] = $this->_description;
            }

            return $params;
        }
    }
}