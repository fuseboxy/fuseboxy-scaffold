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
				<string name="icon" optional="yes" />
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
<div class="input-group"><?php
	// icon
	if ( !empty($field['icon']) ) :
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="fa-fw <?php echo $field['icon']; ?>"></i>
			</span>
		</div><?php
	endif;
	// field
	?><select
		class="custom-select custom-select-sm <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
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
						?><option
							value="<?php echo $optValue; ?>"
							<?php if ( $field['value'] == $optValue ) echo 'selected'; ?>
						><?php echo $optText; ?></option><?php
					endforeach;
				?></optgroup><?php
			// option
			else :
				?><option
					value="<?php echo $optValue; ?>"
					<?php if ( $field['value'] == $optValue ) echo 'selected'; ?>
				><?php echo $optText; ?></option><?php
			endif;
		endforeach;
	?></select>
</div><?php
if ( !empty($field['readonly']) ) :
	?><input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" /><?php
endif;