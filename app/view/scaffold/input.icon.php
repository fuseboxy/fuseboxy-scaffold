<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="icon" optional="yes" />
				<string name="inline-label" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="$data" scope="form">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
if ( !empty($field['icon']) or !empty($field['inline-label']) ) :
	?><div class="input-group-prepend">
		<span class="input-group-text"><?php
			if ( !empty($field['icon']) ) :
				?><i class="fa-fw <?php echo $field['icon']; ?>"></i><?php
			endif;
			if ( !empty($field['inline-label']) ) :
				?><small><?php echo $field['inline-label']; ?></small><?php
			endif;
		?></span>
	</div><?php
endif;