<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$field['value']" />
			<string name="$optValue" />
			<string name="$optText" />
		</in>
		<out />
	</io>
</fusedoc>
*/ ?><option
	value="<?php echo $optValue; ?>"
	<?php if ( $field['value'] == $optValue ) echo 'selected'; ?>
><?php echo $optText; ?></option>