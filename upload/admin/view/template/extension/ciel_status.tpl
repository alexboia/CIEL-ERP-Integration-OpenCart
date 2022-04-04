<?php echo $html_header; ?>
<?php echo $html_column_left; ?>

<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				
			</div>
			<h1><?php echo $ciel_status_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>
	</div>

	<form method="GET" class="form-horizontal">
		<div class="container-fluid">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-info"></i> <?php echo $ciel_status_box_heading; ?></h3>
				</div>
				<div class="panel-body">
					<div class="form-group">
						<div class="col-sm-2 myc-status-label"><?php echo $lbl_module_version; ?>:</div>
						<div class="col-sm-10"><?php echo $status['module_version']; ?></div>
					</div>
					<div class="form-group">
						<div class="col-sm-2 myc-status-label"><?php echo $lbl_php_version; ?>:</div>
						<div class="col-sm-10"><?php echo $status['php_version']; ?></div>
					</div>
					<div class="form-group">
						<div class="col-sm-2 myc-status-label"><?php echo $lbl_opencart_version; ?>:</div>
						<div class="col-sm-10"><?php echo $status['opencart_version']; ?></div>
					</div>
					<div class="form-group">
						<div class="col-sm-2 myc-status-label"><?php echo $lbl_module_configured; ?>:</div>
						<div class="col-sm-10">
							<?php echo $status['module_configured']; ?>
							<a href="<?php echo $view_config_link_action; ?>" target="_blank" class="myc-status-configure-link"><?php echo $view_config_link_text ?></a>
						</div>
					</div>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-bug"></i> <?php echo $ciel_status_box_debug_log; ?></h3>
				</div>
				<div class="panel-body">
					<?php if (!empty($debug_log_status['log_file_message'])): ?>
						<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $debug_log_status['log_file_message']; ?>
							<button type="button" class="close" data-dismiss="alert">&times;</button>
						</div>
					<?php endif; ?>

					<div id="myc_debug_log_operation_status_message" 
						style="display: none;"></div>

					<textarea wrap="off" 
						rows="15" 
						readonly="readonly" 
						id="myc-debug-log-display"
						class="form-control myc-log-window"><?php echo $debug_log_status['log_file_contents']; ?></textarea>

					<?php if ($debug_log_status['log_file_exists']): ?>
						<a href="<?php echo $download_debug_log_btn_action; ?>" 
							class="btn btn-info"
							id="myc-download-debug-log-btn"
							target="_blank"><?php echo $download_log_btn_text; ?></a>
						<a href="javascript:void(0)" 
							data-action-url="<?php echo $clear_debug_log_btn_action; ?>"
							id="myc-clear-debug-log-btn"
							class="btn btn-danger"><?php echo $clear_log_btn_text; ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-exclamation-triangle"></i> <?php echo $ciel_status_box_error_log; ?></h3>
				</div>
				<div class="panel-body">
					<?php if (!empty($error_log_status['log_file_message'])): ?>
						<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> <?php echo $error_log_status['log_file_message']; ?>
							<button type="button" class="close" data-dismiss="alert">&times;</button>
						</div>
					<?php endif; ?>

					<div id="myc_error_log_operation_status_message" 
						style="display: none;"></div>

					<textarea wrap="off" 
						rows="15" 
						readonly="readonly" 
						id="myc-error-log-display"
						class="form-control myc-log-window"><?php echo $error_log_status['log_file_contents']; ?></textarea>

					<?php if ($error_log_status['log_file_exists']): ?>
						<a href="<?php echo $download_error_log_btn_action; ?>" 
							class="btn btn-info"
							id="myc-download-error-log-btn"
							target="_blank"><?php echo $download_log_btn_text; ?></a>
						<a href="javascript:void(0)" 
							id="myc-clear-error-log-btn"
							data-action-url="<?php echo $clear_error_log_btn_action; ?>"
							class="btn btn-danger"><?php echo $clear_log_btn_text; ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</form>
</div>

<?php echo $html_footer; ?>