<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$scaffold">
				<string name="beanType" />
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
<div id="<?php echo $scaffold['beanType']; ?>-list" class="scaffold-list"><?php
	include $scaffold['scriptPath']['header'];
	foreach ($beanList as $bean) include $scaffold['scriptPath']['row'];
?></div>