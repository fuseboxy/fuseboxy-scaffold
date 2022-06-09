<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
				<string name="inline-label" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
if ( !empty($fieldConfig['icon']) or !empty($fieldConfig['inline-label']) ) :
	?><div class="input-group-prepend">
		<span class="input-group-text"><?php
			if ( !empty($fieldConfig['icon']) ) :
				?><i class="fa-fw <?php echo $fieldConfig['icon']; ?>"></i><?php
			endif;
			if ( !empty($fieldConfig['inline-label']) ) :
				?><small><?php echo $fieldConfig['inline-label']; ?></small><?php
			endif;
		?></span>
	</div><?php
endif;