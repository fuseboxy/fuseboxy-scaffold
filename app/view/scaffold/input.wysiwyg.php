<?php /*
<fusedoc>
	<description>
		please load summernote libraries at html head if you are using [format=wysiwyg] field
		===> https://summernote.org/getting-started
	</description>
	<io>
		<in>
			<structure name="$field">
				<string name="format" comments="wysiwyg" />
				<string name="name" />
				<string name="value" />
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
// editable
if ( empty($field['readonly']) ) :
	?><textarea
		name="data[<?php echo $field['name']; ?>]"
		class="scaffold-input-wysiwyg form-control form-control-sm <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		style="min-height: 10em; <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	><?php echo $field['value']; ?></textarea><?php

// readonly
else :
	// hidden field to submit data
	?><input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" /><?php
	// display html
	?><div 
		class="scaffold-input-wysiwyg form-control form-control-sm <?php if ( !empty($field['class']) ) echo $field['class']; ?>"
		style="overflow: auto; <?php if ( !empty($field['style']) ) echo $field['style']; ?>"
	><?php echo $field['value']; ?></div><?php

endif;
