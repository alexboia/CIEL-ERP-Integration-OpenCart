<?php if (!empty($breadcrumbs)): ?>
	<ul class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb): ?>
			<li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
		<?php endforeach; ?>
	</ul>
<?php endif; ?>