<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$layout">
				<string name="width" comments="normal|full|narrow|(specific)" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
$contentClass = '';
$contentStyle = '';
if ( empty($layout['width']) or $layout['width'] == 'normal' ) {
	$contentClass = 'container';
} elseif ( $layout['width'] == 'full' ) {
	$contentClass = 'container-fluid';
} elseif ( $layout['width'] == 'narrow' ) {
	$contentClass = 'container-narrow';
} else {
	$contentStyle = "width: {$layout['width']}";	
}
?>
<div id="global-layout">
	<!-- header -->
	<?php include 'layout.topflash.php'; ?>
	<?php include 'layout.header.php'; ?>
	<!-- content -->
	<div id="content" class="<?php echo $contentClass; ?>" style="<?php echo $contentStyle; ?>">
		<?php include 'layout.flash.php'; ?>
		<?php include 'layout.title.php'; ?>
		<?php include 'layout.breadcrumb.php'; ?>
		<?php if ( !empty($layout['content']) ) echo "<div>{$layout['content']}</div>"; ?>
		<?php include 'layout.pagination.php'; ?>
	</div>
	<!-- footer -->
	<br /><br />
	<?php include 'layout.footer.php'; ?>
</div>