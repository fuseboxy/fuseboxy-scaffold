<?php
// class combination for nav
$tabNavClass = "nav nav-{$tabLayout['style']}s ";
if ( !empty($tabLayout['justify']) ) {
	$tabNavClass .= "nav-justified ";
}
if ( $tabLayout['position'] == 'left' or $tabLayout['position'] == 'right' ) {
	$tabNavClass .= "nav-stacked col-sm-{$tabLayout['navWidth']} ";
}
if ( $tabLayout['position'] == 'right' ) {
	$tabNavClass .= 'pull-right ';
}

// quick fix for tab nav
$tabNavStyle = "margin-bottom: 1em;";
if ( $tabLayout['style'] == 'tab' ) {
	$tabNavStyle .= 'margin-left: -1px; margin-right: -1px; padding-right: 0;';
}
?>


<ul class="<?php echo $tabNavClass; ?>" style="<?php echo $tabNavStyle; ?>">
	<!-- nav : header -->
	<?php if ( !empty($tabLayout['header']) ) : ?>
		<li class="tab-header"><?php echo $tabLayout['header']; ?><br /></li>
	<?php endif; ?>
	<!-- nav : tabs -->
	<?php if ( !empty($tabLayout['nav']) ) : ?>
		<?php foreach ( $tabLayout['nav'] as $t ) : ?>
			<li class="<?php if ( !empty($t['active']) ) echo 'active'; ?> <?php if ( !empty($t['menus']) ) echo 'dropdown'; ?>">
				<!-- show drop-down then url -->
				<a <?php if ( !empty($t['menus']) ) : ?>class="dropdown-toggle" data-toggle="dropdown" href="#"<?php elseif ( !empty($t['url']) ) : ?>href="<?php echo $t['url']; ?>"<?php endif; ?>>
					<!-- buttons -->
					<?php if ( isset($t['button']) ) : ?>
						<div class="pull-right" style="margin-left: 1em;">
							<?php foreach ( $t['button'] as $buttonName => $buttonURL ) : ?>
								<button
									class="btn btn-xs btn-default"
								 	onclick="document.location.href='<?php echo $buttonURL; ?>';"
								 	onmousedown="var parent = $(this).closest('a'); $(parent).attr('data-href', $(parent).attr('href')).removeAttr('href');"
								 	onmouseup="var parent = $(this).closest('a'); window.setTimeout(function(){ $(parent).attr('href', $(parent).attr('data-href')).removeAttr('data-href'); }, 0);">
									<?php echo $buttonName; ?>
								</button>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
					<!-- item name -->
					<?php echo $t['name']; ?>
					<!-- arrow for dropdown -->
					<?php if ( !empty($t['menus']) ) : ?>
						<span class="caret"></span>
					<?php endif; ?>
				</a>
				<!-- dropdown menu -->
				<?php if ( !empty($t['menus']) ) : ?>
					<ul class="dropdown-menu">
						<?php foreach ( $t['menus'] as $m ) : ?>
							<!-- divider -->
							<?php if ( !empty($m['divider']) and stripos($m['divider'], 'before') !== false ) : ?>
								<li class="divider"></i>
							<?php endif; ?>
							<!-- header -->
							<?php if ( !empty($m['navHeader']) ) : ?>
								<li class="dropdown-header"><?php echo $m['navHeader']; ?></li>
							<?php endif; ?>
							<!-- menu item -->
							<?php if ( !empty($m['name']) ) : ?>
								<li class="<?php if ( !empty($m['active']) ) echo 'active'; ?>">
									<a <?php if ( !empty($m['url']) ) : ?>href="<?php echo $m['url']; ?>"<?php endif; ?> <?php if ( !empty($m['newWindow']) ) : ?>target="_blank"<?php endif; ?>><?php echo $m['name']; ?></a>
								</li>
							<?php endif; ?>
							<!-- divider -->
							<?php if ( !empty($m['divider']) and stripos($m['divider'], 'after') !== false ) : ?>
								<li class="divider"></i>
							<?php endif; ?>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	<?php endif; ?>
	<!-- nav : footer -->
	<?php if ( !empty($tabLayout['footer']) ) : ?>
		<li class="tab-footer"><br /><?php echo $tabLayout['footer']; ?></li>
	<?php endif; ?>
</ul>