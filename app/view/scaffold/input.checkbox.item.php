<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<array name="$fieldValue">
				<string name="+" />
			</array>
			<structure name="$fieldConfig">
				<array name="options">
					<string name="~optionValue~" value="~optionText~" optional="yes" />
					<structure name="~optGroup~" optional="yes">
						<structure name="~optionValue~" value="~optionText~" />
					</structure>
				</array>
				<string name="icon" optional="yes" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<boolean name="disabled" optional="yes" />
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
		name="<?php echo $dataFieldName; ?>[]"
		value="<?php echo htmlspecialchars($optValue); ?>"
		<?php if ( in_array($optValue, $fieldValue) ) echo 'checked'; ?>
		<?php if ( !empty($fieldConfig['required']) and $optIndex == 0 ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['readonly']) or !empty($fieldConfig['disabled']) ) echo 'disabled'; ?>
	 />
	<label 
		for="<?php echo $checkboxID; ?>" 
		class="form-check-label small"
	><?php echo $optText; ?></label>
</div>