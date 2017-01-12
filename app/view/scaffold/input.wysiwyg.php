<?php /*
<fusedoc>
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
*/
$uniqid = uuid();
$editor_uniqid = "{$scaffold['beanType']}-input-{$field['name']}-{$uniqid}";
$hidden_uniqid = "{$scaffold['beanType']}-hidden-{$field['name']}-{$uniqid}";
?>
<!-- ckeditor will auto-transform div[contenteditable=true] in document! -->
<!-- sync value of html-editor and hidden-field by javascript -->
<div
	id="<?php echo $editor_uniqid; ?>"
	class="form-control input-sm"
	<?php if ( empty($field['readonly']) ) : ?>
		contenteditable="true"
		onblur="$('#<?php echo $hidden_uniqid; ?>').val( $(this).html() );"
	<?php endif; ?>
	style="height: auto; <?php echo isset($field['style']) ? $field['style'] : 'min-height: 10em;'; ?>"
><?php echo $field['value']; ?></div>


<!-- make it pseudo-invisible in order to keep [required] attribute working -->
<?php if ( empty($field['readonly']) ) : ?>
	<div style="position: relative; overflow: hidden;">
		<textarea
			id="<?php echo $hidden_uniqid; ?>"
			name="data[<?php echo $field['name']; ?>]"
			style="position: absolute; top: 0; height: 1px;"
			<?php if ( !empty($field['required']) ) echo 'required'; ?>
		><?php echo $field['value']; ?></textarea>
	</div>
<?php endif; ?>


<!-- transform ckeditor explicitly (when ajax request) -->
<?php if ( empty($field['readonly']) and F::ajaxRequest() ) : ?>
	<script>
		// wait row or modal to appear
		window.setTimeout(function(){
			CKEDITOR.inline('<?php echo $editor_uniqid; ?>');
		}, 1000);
	</script>
<?php endif; ?>