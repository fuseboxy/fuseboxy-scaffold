<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<string name="value" />
				<string name="format" comments="date|time|datetime" />
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
<div class="input-group input-group-sm"><?php
	include F::appPath('view/scaffold/input.icon.php');
	?><input
		type="text"
		class="form-control scaffold-input-<?php echo $field['format']; ?> <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		autocomplete="off"
		<?php if ( !empty($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( !empty($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />
</div>