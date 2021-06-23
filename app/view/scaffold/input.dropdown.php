<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<string name="value" />
				<structure name="options">
					<string name="~optionValue~" value="~optionText~" optional="yes" />
					<structure name="~optGroup~" optional="yes">
						<structure name="~optionValue~" value="~optionText~" />
					</structure>
				</structure>
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
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
	?><select
		class="custom-select <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( !empty($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
	><?php
		// empty first item
		?><option value=""><?php 
			if ( !empty($field['placeholder']) ) echo $field['placeholder']; 
		?></option><?php
		// user-defined items
		foreach ( $field['options'] as $optValue => $optText ) :
			// optgroup
			if ( is_array($optText) ) :
				$optGroupLabel = $optValue;
				$optGroupItems = $optText;
				?><optgroup label="<?php echo $optGroupLabel; ?>"><?php
					// optgroup-option
					foreach ( $optGroupItems as $optValue => $optText ) :
						include F::appPath('view/scaffold/input.dropdown.item.php');
					endforeach;
				?></optgroup><?php
			// option
			else :
				include F::appPath('view/scaffold/input.dropdown.item.php');
			endif;
		endforeach;
	?></select>
</div><?php
if ( !empty($field['readonly']) ) :
	?><input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" /><?php
endif;