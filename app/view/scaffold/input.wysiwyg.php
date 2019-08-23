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
				<boolean name="required" />
				<boolean name="readonly" />
				<string name="style" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<?php if ( empty($field['readonly']) ) : ?>
	<textarea
		name="data[<?php echo $field['name']; ?>]"
		class="scaffold-input-wysiwyg form-control form-control-sm"
		style="min-height: 10em; <?php if ( isset($field['style']) ) echo $field['style']; ?>"
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	><?php echo $field['value']; ?></textarea>
<?php else : ?>
	<div 
		class="scaffold-input-wysiwyg form-control form-control-sm"
		style="overflow: auto; <?php if ( isset($field['style']) ) echo $field['style']; ?>"
	><?php echo $field['value']; ?></div>
<?php endif; ?>