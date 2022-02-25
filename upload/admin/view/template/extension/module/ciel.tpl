<?php echo $html_header; ?>
<?php echo $html_column_left; ?>

<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<button type="submit" 
					id="myc_ciel_settings_save"
					form="myc_ciel_settings_form"
					data-toggle="tooltip" 
					data-save-settings-url="<?php echo $url_save_action; ?>"
					title="<?php echo $txt_save_action; ?>" 
					class="btn btn-primary"><i class="fa fa-save"></i></button>
				<a href="<?php echo $url_cancel_action; ?>" 
					data-toggle="tooltip" 
					title="<?php echo $txt_cancel_action; ?>" 
					class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $ciel_settings_page_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>
	</div>

	<form name="myc_ciel_settings_form" id="myc_ciel_settings_form" method="POST" class="form-horizontal">
		<div id="myc_operation_status_message" 
			class="container-fluid" 
			style="display: none;"></div>
		<div class="container-fluid">
			<?php echo $html_connection_settings_form; ?>		
		</div>
		<?php if (!empty($html_runtime_settings_form)): ?>
			<div class="container-fluid">
				<?php echo $html_runtime_settings_form; ?>
			</div>
		<?php endif; ?>
		<?php if (!empty($html_workflow_settings_form)): ?>
			<div class="container-fluid">
				<?php echo $html_workflow_settings_form; ?>
			</div>
		<?php endif; ?>
	</form>

	<?php echo $html_loading_indicator; ?>
</div>

<?php echo $html_footer; ?>