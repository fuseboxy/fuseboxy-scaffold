<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
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
<<<<<<< HEAD
	// field
	?><textarea
		class="form-control <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		name="data[<?php echo $fieldName; ?>]"
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
		<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
	><?php echo $fieldValue; ?></textarea><?php
=======
	?><textarea
		class="form-control <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( !empty($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( !empty($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
	><?php echo $field['value']; ?></textarea><?php
>>>>>>> 775e16460c04656706f51cd6274a91351bce7280
?></div>