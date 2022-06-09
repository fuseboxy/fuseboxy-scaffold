<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
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
		class="custom-select <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		name="<?php echo $dataFieldName; ?>"
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'disabled'; ?>
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
	><?php
		// empty first item
		?><option value=""><?php 
			if ( !empty($fieldConfig['placeholder']) ) echo $fieldConfig['placeholder']; 
		?></option><?php
		// user-defined items
		foreach ( $fieldConfig['options'] as $optValue => $optText ) :
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
if ( !empty($fieldConfig['readonly']) ) :
	?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="<?php echo htmlspecialchars($fieldValue); ?>" /><?php
endif;