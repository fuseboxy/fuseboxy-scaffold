<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<array name="value">
					<string name="+" />
				</array>
				<array name="options">
					<string name="~optValue~" value="~optText~" />
				</array>
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out>
			<structure name="$data" scope="form">
				<array name="~fieldName~">
					<string name="+" />
				</array>
			</structure>
		</out>
	</io>
</fusedoc>
*/
// empty hidden field
// ===> avoid nothing submitted when no checkbox selected
?><input type="hidden" name="data[<?php echo $field['name']; ?>][]" value="" /><?php
// display
$optIndex = 0;
foreach ( $field['options'] as $optValue => $optText ) :
	$checkboxID = uuid();
	?><div class="form-check">
		<input
			id="<?php echo $checkboxID; ?>"
			class="form-check-input"
			type="checkbox"
			name="data[<?php echo $field['name']; ?>][]"
			value="<?php echo htmlspecialchars($optValue); ?>"
			<?php if ( in_array($optValue, $field['value']) ) echo 'checked'; ?>
			<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
			<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
		 />
		<label 
			for="<?php echo $checkboxID; ?>" 
			class="form-check-label small"
		><?php echo $optText; ?></label>
	</div><?php
	$optIndex++;
endforeach;
if ( !empty($field['readonly']) ) :
	foreach ( $field['value'] as $val ) :
		?><input type="hidden" name="data[<?php echo $field['name']; ?>][]" value="<?php echo htmlspecialchars($val); ?>" /><?php
	endforeach;
endif;