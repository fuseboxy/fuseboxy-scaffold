<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<structure name="$field">
				<string name="value" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?><option
	value="<?php echo $optValue; ?>"
	<?php if ( $field['value'] == $optValue ) echo 'selected'; ?>
><?php echo $optText; ?></option>