<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" optional="yes" default="text" />
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
		type="<?php echo empty($fieldConfig['format']) ? 'text' : $fieldConfig['format']; ?>"
		class="form-control scaffold-input-<?php echo empty($fieldConfig['format']) ? 'text' : $fieldConfig['format']; ?> <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		name="data[<?php echo $fieldName; ?>]"
		value="<?php echo htmlspecialchars($fieldValue); ?>"
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
	 />
 </div>