<?php /*
<fusedoc>
	<io>
		<in>
			<string name="controller" scope="$fusebox" />
			<structure name="$scaffold">
				<structure name="scriptPath">
					<string name="header" />
					<string name="row" />
				</structure>
			</structure>
			<array name="$beanList" />
		</in>
	</io>
</fusedoc>
*/ ?>
<div id="<?php echo F::command('controller'); ?>-list" class="scaffold-list"><?php
	include $scaffold['scriptPath']['header'];
	foreach ($beanList as $bean) include $scaffold['scriptPath']['row'];
?></div>