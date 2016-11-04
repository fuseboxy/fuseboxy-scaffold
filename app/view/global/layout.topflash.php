<?php /*
<fusedoc>
	<io>
		<in>
			<string name="topFlash" scope="$arguments|$_SESSION" />
			<structure name="topFlash" scope="$arguments|$_SESSION">
				<string name="type" optional="yes" default="warning" comments="success|info|warning|danger" />
				<string name="title" optional="yes" />
				<string name="message" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// cross-page
if ( isset($_SESSION['topFlash']) ) {
	$arguments['topFlash'] = $_SESSION['topFlash'];
	unset($_SESSION['topFlash']);
}
// default
if ( isset($arguments['topFlash']) ) {
	if ( !is_array($arguments['topFlash']) ) {
		$arguments['topFlash'] = array('message' => $arguments['topFlash']);
	}
	if ( empty($arguments['topFlash']['type']) ) {
		$arguments['topFlash']['type'] = 'warning';
	}
}
?>
<?php if ( isset($arguments['topFlash']) ) : ?>
	<div
		id="top-flash"
		class="text-center btn btn-<?php echo $arguments['topFlash']['type']; ?>"
		style="border-radius: 0; margin-bottom: 0; position: fixed; top: 0; width: 100%; z-index: 1030;"
	>
		<?php if ( !empty($arguments['topFlash']['icon']) ) : ?>
			<i class="<?php echo $arguments['topFlash']['icon']; ?>"></i>
		<?php endif; ?>
		<?php if ( !empty($arguments['topFlash']['title']) ) : ?>
			<strong><?php echo $arguments['topFlash']['title']; ?></strong>
		<?php endif; ?>
		<?php echo $arguments['topFlash']['message']; ?>
	</div>
	<div id="top-flash-placeholder" class="btn btn-block">&nbsp;</div>
<?php endif; ?>