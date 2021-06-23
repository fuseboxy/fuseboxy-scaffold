<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<structure name="$field">
				<string name="name" />
				<array name="value">
					<string name="+" />
				</array>
				<array name="options">
					<string name="~optionValue~" value="~optionText~" optional="yes" />
					<structure name="~optGroup~" optional="yes">
						<structure name="~optionValue~" value="~optionText~" />
					</structure>
				</array>
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
$checkboxID = Util::uuid();
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
</div>