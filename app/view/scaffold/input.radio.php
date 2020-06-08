<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="name" />
				<string name="value" />
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
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
$optIndex = 0;
foreach ( $field['options'] as $optValue => $optText ) :
	$radioID = uuid();
	?><div class="form-check">
		<input
			id="<?php echo $radioID; ?>"
			class="form-check-input"
			type="radio"
			name="data[<?php echo $field['name']; ?>]"
			value="<?php echo htmlspecialchars($optValue); ?>"
			<?php if ( $field['value'] == $optValue ) echo 'checked'; ?>
			<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
			<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
		 />
		<label 
			for="<?php echo $radioID; ?>" 
			class="form-check-label small"
		><?php echo $optText; ?></label>
	</div><?php
	$optIndex++;
endforeach;
if ( !empty($field['readonly']) ) :
	?><input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" /><?php
endif;