<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" />
				<string name="ajaxUploadProgress" />
			</structure>
			<structure name="$field">
				<string name="name" />
				<string name="format" comments="normal|output|textarea|checkbox|radio" default="normal" />
				<array name="options" comments="show dropdown when specified">
					<string name="~key is option-value~" comments="value is option-text" />
				</array>
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="placeholder" />
				<string name="style" />
				<string name="help" />
				<!-- below are for [format=file] only -->
				<string name="filesize" optional="yes" comments="max file size in bytes" />
				<number name="filesize_numeric" optional="yes" comments="use this for comparison" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
				<boolean name="preview" optional="yes" />
				<string name="previewBaseUrl" comments="has trailing slash" />
				<!-- below are for [format=checkbox] -->
				<boolean name="many-to-many" optional="yes" />
			</structure>
			<object name="$bean" comments="for field value" />
		</in>
		<out />
	</io>
</fusedoc>
*/

// checkbox
// ===> get value from shared-list (many-to-many)
// ===> get value from own-list (one-to-many)
if ( isset($field['options']) and isset($field['format']) and $field['format'] == 'checkbox' ) {
	$field['_value_'] = array();
	$associateName = str_replace('_id', '', $field['name']);
	$propertyName = ( !empty($field['many-to-many']) ? 'shared' : 'own' ) . ucfirst($associateName);
	foreach ( $bean->{$propertyName} as $tmp ) $field['_value_'][] = $tmp->id;

// other type
// ===> simple value
} elseif ( isset($bean[$field['name']]) ) {
	$field['_value_'] = $bean[$field['name']];

// no value
// ===> apply default value
} elseif ( isset($field['default']) ) {
	$field['_value_'] = $field['default'];

// empty value
} else {
	$field['_value_'] = '';
}
?>

<!-- output -->
<?php if ( isset($field['format']) and $field['format'] == 'output' ) : ?>
	<p class="form-control-static input-sm"><?php echo $field['_value_']; ?></p>


<!-- radio -->
<?php elseif ( isset($field['options']) and isset($field['format']) and $field['format'] == 'radio' ) : ?>
	<?php $optIndex = 0; ?>
	<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
		<div class="radio">
			<label>
				<input
					type="radio"
					name="data[<?php echo $field['name']; ?>]"
					value="<?php echo $optValue; ?>"
					<?php if ( $field['_value_'] == $optValue ) echo 'checked'; ?>
					<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
					<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
				 /><?php echo $optText; ?>
			</label>
		</div>
		<?php $optIndex++; ?>
	<?php endforeach; ?>
	<?php if ( !empty($field['readonly']) ) : ?>
		<input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo $field['_value_']; ?>" />
	<?php endif; ?>


<!-- checkbox (submit array value) -->
<?php elseif ( isset($field['options']) and isset($field['format']) and $field['format'] == 'checkbox' ) : ?>
	<?php $optIndex = 0; ?>
	<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
		<div class="checkbox">
			<label>
				<input
					type="checkbox"
					name="data[<?php echo $field['name']; ?>][]"
					value="<?php echo $optValue; ?>"
					<?php if ( in_array($optValue, $field['_value_']) ) echo 'checked'; ?>
					<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
					<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
				 /><?php echo $optText; ?>
			</label>
		</div>
		<?php $optIndex++; ?>
	<?php endforeach; ?>
	<?php if ( !empty($field['readonly']) ) : ?>
		<?php foreach ( $field['_value_'] as $val ) : ?>
			<input type="hidden" name="data[<?php echo $field['name']; ?>][]" value="<?php echo $val; ?>" />
		<?php endforeach; ?>
	<?php endif; ?>


<!-- listbox -->
<?php elseif ( isset($field['options']) ) : ?>
	<select
		class="form-control input-sm"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
	>
		<option value="">
			<?php if ( isset($field['placeholder']) ) echo $field['placeholder']; ?>
		</option>
		<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
			<option value="<?php echo $optValue; ?>" <?php if ( $field['_value_'] == $optValue ) echo 'selected'; ?>>
				<?php echo $optText; ?>
			</option>
		<?php endforeach; ?>
	</select>
	<?php if ( !empty($field['readonly']) ) : ?>
		<input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo $field['_value_']; ?>" />
	<?php endif; ?>


<!-- textarea -->
<?php elseif ( isset($field['format']) and $field['format'] == 'textarea' ) : ?>
	<textarea
		class="form-control input-sm"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
	><?php echo $field['_value_']; ?></textarea>


<!-- file -->
<?php elseif ( isset($field['format']) and $field['format'] == 'file' ) : ?>
	<?php include 'input.file.php'; ?>


<!-- normal -->
<?php else : ?>
	<input
		type="<?php echo isset($field['format']) ? $field['format'] : 'text'; ?>"
		class="form-control input-sm"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo $field['_value_']; ?>"
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />


<?php endif; ?>


<!-- help -->
<?php if ( !empty($field['help']) ) : ?>
	<code class="help-block"><?php echo $field['help']; ?></code>
<?php endif; ?>