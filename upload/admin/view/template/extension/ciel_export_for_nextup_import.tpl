<?php echo $html_header; ?>
<?php echo $html_column_left; ?>

<div id="content">
	<div class="page-header">
		<div class="container-fluid">
			<div class="pull-right">
				<a href="<?php echo $url_cancel_action; ?>" 
					data-toggle="tooltip" 
					title="<?php echo $txt_cancel_action; ?>" 
					class="btn btn-default"><i class="fa fa-reply"></i></a>
			</div>
			<h1><?php echo $ciel_export_for_nextup_import_title; ?></h1>
			<?php echo $html_breadcrumbs; ?>
		</div>

		<div class="container-fluid">
			<div class="alert alert-info" role="alert">
				<span class="myc-tooltip" data-toggle="tooltip" title="<?php echo $ciel_export_for_nextup_import_explanation; ?>"><?php echo $ciel_export_for_nextup_import_info; ?></span>
			</div>

			<div id="myc-export-actions" class="sync-info-actions">
				<a id="myc-exportt-start" type="button" class="btn btn-primary" href="<?php echo $ciel_export_for_nextup_import_btn_action; ?>"><?php echo $ciel_export_for_nextup_import_btn_text; ?></a>
			</div>
		</div>
	</div>
</div>

<?php echo $html_footer; ?>