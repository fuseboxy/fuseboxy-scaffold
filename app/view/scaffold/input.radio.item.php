<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="name" />
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
$radioID = Util::uuid();
?><div class="form-check">
	<input
		id="<?php echo $radioID; ?>"
		class="form-check-input"
		type="radio"
		name="data[<?php echo $fieldConfig['name']; ?>]"
		value="<?php echo htmlspecialchars($optValue); ?>"
		<?php if ( $fieldValue == $optValue ) echo 'checked'; ?>
		<?php if ( !empty($fieldConfig['required']) and $optIndex == 0 ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['readonly']) ) echo 'disabled'; ?>
	 />
	<label 
		for="<?php echo $radioID; ?>" 
		class="form-check-label small"
	><?php echo $optText; ?></label>
</div>