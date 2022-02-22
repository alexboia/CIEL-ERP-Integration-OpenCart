<style type="text/css">
	.ciel-loader {
		position: relative;
		text-align: center;
		margin: 35px auto 35px auto;
		z-index: 9999;
		display: block;
		width: 80px;
		height: 80px;
		border: 10px solid rgba(0, 0, 0, 0.3);
		border-radius: 50%;
		border-top-color: #000;
		animation: spin 1s ease-in-out infinite;
		-webkit-animation: spin 1s ease-in-out infinite;
	}

	@keyframes spin {
		to {
			-webkit-transform: rotate(360deg);
		}
	}

	@-webkit-keyframes spin {
		to {
			-webkit-transform: rotate(360deg);
		}
	}

	#ciel_modal_loading_indicator .modal-content {
		border-radius: 0px;
		box-shadow: 0 0 5px 2px rgba(0, 0, 0, 0.3);
		border-radius: 5px;
		margin-top: 150px;
	}

	.modal-backdrop.show {
  		opacity: 0.75;
	}
</style>
<div class="modal fade" id="ciel_modal_loading_indicator" tabindex="-1" role="dialog" aria-labelledby="ciel_modal_loading_indicator_label">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-body text-center">
				<div class="ciel-loader"></div>
				<div clas="ciel-loader-txt"></div>
			</div>
		</div>
	</div>
</div>