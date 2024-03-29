<?php /*
<fusedoc>
	<description>
		please load summernote libraries at html head if you are using [format=wysiwyg] field
		===> https://summernote.org/getting-started
	</description>
	<io>
		<in>
			<string name="$fieldName" />
			<string name="$dataFieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" comments="wysiwyg" />
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<boolean name="disabled" optional="yes" />
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
if ( empty($fieldConfig['readonly']) ) :
	?><textarea
		name="<?php echo $dataFieldName; ?>"
		class="scaffold-input-wysiwyg form-control form-control-sm <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		style="min-height: 10em; <?php if ( !empty($fieldConfig['style']) ) echo $fieldConfig['style']; ?>"
		<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
		<?php if ( !empty($fieldConfig['disabled']) ) echo 'disabled'; ?>
	><?php echo $fieldValue; ?></textarea><?php

// readonly
else :
	// hidden field to submit data
	?><input type="hidden" name="<?php echo $dataFieldName; ?>" value="<?php echo htmlspecialchars($fieldValue); ?>" /><?php
	// display html
	?><div 
		class="scaffold-input-wysiwyg form-control form-control-sm <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
		style="overflow: auto; <?php if ( !empty($fieldConfig['style']) ) echo $fieldConfig['style']; ?>"
	><?php echo $fieldValue; ?></div><?php

endif;
