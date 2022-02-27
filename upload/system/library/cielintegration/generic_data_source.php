<?php
namespace CielIntegration {
	interface GenericDataSource {
		function getValueForKey($id, $key);
	}
}