<?php
namespace CielIntegration {
	use \Loader;

	/** @property \Loader $load */
	trait WithAdminLayoutLoader {
		protected function _loadAdminLayout() {
			$data = array();
			$data['header'] = $this->load->controller('common/header');
			$data['column_left'] = $this->load->controller('common/column_left');
			$data['footer'] = $this->load->controller('common/footer');
			return $data;
		}

		protected function _mergeAdminLayout(&$data) {
			$data = array_merge($data, $this->_loadAdminLayout());
			return $data;
		}
	}
}