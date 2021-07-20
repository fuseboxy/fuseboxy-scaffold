<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<string name="$fieldValue" />
		</in>
		<out />
	</io>
</fusedoc>
*/ ?><option
	value="<?php echo $optValue; ?>"
	<?php if ( $fieldValue == $optValue ) echo 'selected'; ?>
><?php echo $optText; ?></option>