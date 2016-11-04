<?php
// class for tab-style
// ===> not stand bootstrap 3 class
// ===> please refer to {bootstrap.custom.css}
if ( $tabLayout['style'] == 'tab' ) {
	$tabLayoutClass = "tabbable  tabs-{$tabLayout['position']}";
} else {
	$tabLayoutClass = '';
}

// content class
$tabContentClass = 'tab-content ';
if ( $tabLayout['position'] != 'top' ) {
	$tabContentClass .= 'col-sm-'.(12-$tabLayout['navWidth']).' ';
}
?>


<div id="tab-layout" class="<?php echo $tabLayoutClass; ?>">
	<?php include 'tab.nav.php'; ?>
	<div class="<?php echo $tabContentClass; ?>">
		<div class="tab-pane active">
			<?php include 'layout.title.php'; ?>
			<?php include 'layout.breadcrumb.php'; ?>
			<?php include 'layout.flash.php'; ?>
			<div><?php echo $layout['content']; ?></div>
			<?php include 'layout.pagination.php'; ?>
		</div>
	</div>
</div>