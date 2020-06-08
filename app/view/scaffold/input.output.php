<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$field">
				<string name="icon" optional="yes" />
				<string name="value" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div class="form-control-plaintext form-control-sm <?php if ( isset($field['class']) ) echo $field['class']; ?>"
	<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
><?php echo $field['value']; ?></div>