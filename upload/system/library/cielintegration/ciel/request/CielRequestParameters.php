<?php
namespace Ciel\Api\Request {
    abstract class CielRequestParameters {
        /**
         * Returns key value array of request parameters
         * @return array 
         */
        abstract public function getParams();
    }
}