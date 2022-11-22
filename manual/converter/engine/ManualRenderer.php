<?php
namespace MyClar\ManualBuilder {
	interface ManualRenderer {
		function render(ManualPageCollection $pages): string;
	}
}