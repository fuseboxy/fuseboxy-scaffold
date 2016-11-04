<?php /*
<fusedoc>
	<io>
		<in>
			<array name="nav|navRight" scope="$arguments">
				<structure name="+">
					<string name="name" optional="yes" />
					<string name="url" optional="yes" />
					<string name="navHeader" optional="yes" />
					<boolean name="newWindow" optional="yes" />
					<list name="divider" optional="yes" delim=",">
						<string name="before|after" />
					</list>
					<array name="menus" optional="yes" comments="same data structure as base" />
					<string name="className" optional="yes" />
					<boolean name="active" />
				</structure>
			</array>
			<string name="topFlash" scope="$arguments" optional="yes" />
			<structure name="$layout">
				<string name="brand" optional="yes" />
			</structure>
			<structure name="$xfa">
				<string name="brand" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<?php if ( !function_exists('__renderGlobalNav') ) : ?>
	<?php function __renderGlobalNav($menus, $level=1) { ?>
		<?php foreach ( $menus as $m ) : ?>
			<!-- divider -->
			<?php if ( !empty($m['divider']) and stripos($m['divider'], 'before') !== false ) : ?>
				<li class="divider"></i>
			<?php endif; ?>
			<!-- header -->
			<?php if ( !empty($m['navHeader']) ) : ?>
				<li class="dropdown-header"><?php echo $m['navHeader']; ?></li>
			<?php endif; ?>
			<!-- submenu -->
			<?php if ( isset($m['menus']) ) : ?>
				<li class="<?php echo ( $level == 1 ) ? 'dropdown' : 'dropdown-menu'; ?> <?php if ( !empty($m['className']) ) echo $m['className']; ?> <?php if ( !empty($m['active']) ) echo 'active'; ?>">
					<?php if ( $level == 1 ) : ?>
						<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $m['name']; ?> <b class="caret"></b></a>
					<?php else : ?>
						<a tabindex="-1" href="#"><?php echo $m['name']; ?></a>
					<?php endif; ?>
					<ul class="dropdown-menu">
						<?php __renderGlobalNav($m['menus'], $level+1); ?>
					</ul>
				</li>
			<!-- menu item -->
			<?php elseif ( !empty($m['name']) ) : ?>
				<li class="<?php if ( !empty($m['className']) ) echo $m['className']; ?> <?php if ( !empty($m['active']) ) echo 'active'; ?>">
					<a
						<?php if ( !empty($m['url']) ) : ?>href="<?php echo $m['url']; ?>"<?php endif; ?>
						<?php if ( !empty($m['newWindow']) ) : ?>target="_blank"<?php endif; ?>
					><?php echo $m['name']; ?></a>
				</li>
			<?php endif; ?>
			<!-- divider -->
			<?php if ( !empty($m['divider']) and stripos($m['divider'], 'after') !== false ) : ?>
				<li class="divider"></i>
			<?php endif; ?>
		<?php endforeach; ?>
	<?php } ?>
<?php endif; ?>


<header id="header" class="navbar navbar-default navbar-fixed-top" <?php if ( isset($arguments['topFlash']) ) : ?>style="top: 34px;"<?php endif; ?>>
	<!-- logo -->
	<div class="navbar-header">
		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav">
			<i class="fa fa-bars"></i>
		</button>
		<?php if ( !empty($layout['brand']) ) : ?>
			<?php if ( isset($xfa['brand']) ) : ?>
				<a href="<?php echo F::url($xfa['brand']); ?>" class="navbar-brand"><?php echo $layout['brand']; ?></a>
			<?php else : ?>
				<span class="navbar-brand"><?php echo $layout['brand']; ?></span>
			<?php endif; ?>
		<?php endif; ?>
	</div>
	<!-- menu -->
	<nav id="nav" class="navbar-collapse collapse">
		<!-- left menu -->
		<ul class="nav navbar-nav">
			<?php if ( !empty($arguments['nav']) ) __renderGlobalNav($arguments['nav']); ?>
		</ul>
		<!-- right menu -->
		<ul class="nav navbar-nav navbar-right">
			<?php if ( !empty($arguments['navRight']) ) __renderGlobalNav($arguments['navRight']); ?>
			<!-- placeholder for [navbar-fixed-top] bug -->
			<li style="width: 20px;"></li>
		</ul>
	</nav>
</header>
<div id="header-placeholder" class="navbar">&nbsp;</div>