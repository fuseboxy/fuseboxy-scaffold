<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$optValue" />
			<string name="$optText" />
			<structure name="$fieldConfig">
				<string name="value" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?><option
	value="<?php echo $optValue; ?>"
	<?php if ( $fieldConfig['value'] == $optValue ) echo 'selected'; ?>
><?php echo $optText; ?></option>