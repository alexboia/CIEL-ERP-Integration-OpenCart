<?php
namespace MyClar\ManualBuilder {
	class ContentProvider {
		public function __construct(Manifest $manifest) {
			
		}

		public function readPages(): PageCollection {
			return new PageCollection();
		}
	}
}