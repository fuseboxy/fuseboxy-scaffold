<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<string name="value" />
				<string name="format" optional="yes" default="text" />
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="placeholder" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="$data" scope="form">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="input-group"><?php
	if ( !empty($field['icon']) ) :
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="fa-fw <?php echo $field['icon']; ?>"></i>
			</span>
		</div><?php
	endif;
	?><input
		type="<?php echo empty($field['format']) ? 'text' : $field['format']; ?>"
		class="form-control form-control-sm scaffold-input-<?php echo empty($field['format']) ? 'text' : $field['format']; ?> <?php if ( isset($field['class']) ) echo $field['class']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />
 </div>