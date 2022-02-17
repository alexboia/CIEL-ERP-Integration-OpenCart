<?php
namespace Ciel\Api {
    interface CielCache {
        function set($key, $value, $secondsUntilExpiration);

        function get($key);

        function remove($key);

        function clear();
    }
}