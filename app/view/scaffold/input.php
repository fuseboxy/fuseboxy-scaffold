<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" comments="for [format=file] field" />
				<string name="ajaxUploadProgress" comments="for [format=file] field" />
			</structure>
			<structure name="$field">
				<string name="name" />
				<string name="format" comments="text|hidden|output|textarea|radio|checkbox|file|date|time|datetime|one-to-many|many-to-many|wysiwyg" default="text" />
				<string name="value" optional="yes" />
				<string name="default" optional="yes" />
				<array name="options" comments="show dropdown when no {format} specified; it can also serve {format=radio|checkbox}">
					<string name="~key is option-value~" comments="value is option-text" />
				</array>
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="placeholder" />
				<string name="style" />
				<string name="pre-help" comments="help text show before input field" />
				<string name="help" comments="help text show after input field" />
				<!-- below are for [format=file] only -->
				<string name="filesize" optional="yes" comments="max file size in bytes" />
				<number name="filesize_numeric" optional="yes" comments="use this for comparison" />
				<list name="filetype" optional="yes" delim="," comments="comma-delimited list of allowed file types (e.g. filetype=gif,jpg,png)" />
				<boolean name="preview" optional="yes" />
			</structure>
			<object name="$bean" comments="for field value" />
		</in>
		<out />
	</io>
</fusedoc>
*/
// force using user-defined value (when specified)
if ( isset($field['value']) ) {
	// ~~~ do nothing ~~~

// checkbox (one-to-many|many-to-many)
// ===> one-to-many  : get value from own-list
// ===> many-to-many : get value from shared-list
} elseif ( isset($field['format']) and in_array($field['format'], array('one-to-many','many-to-many')) ) {
	$field['value'] = array();
	$associateName = str_replace('_id', '', $field['name']);
	$propertyName = ( ( $field['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
	foreach ( $bean->{$propertyName} as $tmp ) $field['value'][] = $tmp->id;

// checkbox (normal)
// ===> turn pipe-delimited list into array
} elseif ( isset($field['format']) and $field['format'] == 'checkbox' ) {
	$field['value'] = explode('|', $bean[$field['name']]);

// other type
// ===> simple value
} elseif ( isset($bean[$field['name']]) ) {
	$field['value'] = $bean[$field['name']];

// no value
// ===> apply default value
} elseif ( isset($field['default']) ) {
	$field['value'] = $field['default'];

// empty value
} else {
	$field['value'] = '';
}

// fix options (when necessary)
// ===> when options was not specified
// ===> use field value as options
if ( isset($field['format']) and in_array($field['format'], array('radio','checkbox','one-to-many','many-to-many')) and !isset($field['options']) ) {
	$field['options'] = array();
	if ( $field['format'] == 'radio' ) {
		$field['options'][$field['value']] = $field['value'];
	} else {
		foreach ( $field['value'] as $val ) $field['options'][$val] = $val;
	}
}
?>


<!-- pre-help -->
<?php if ( !empty($field['pre-help']) ) : ?>
	<small class="form-text text-muted mb-1"><?php echo $field['pre-help']; ?></small>
<?php endif; ?>


<!-- output -->
<?php if ( isset($field['format']) and $field['format'] == 'output' ) : ?>
	<p class="form-control-static form-control-sm"><?php echo $field['value']; ?></p>


<!-- radio -->
<?php elseif ( isset($field['format']) and $field['format'] == 'radio' ) : ?>
	<?php $optIndex = 0; ?>
	<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
		<?php $radioID = uuid(); ?>
		<div class="form-check">
			<input
				id="<?php echo $radioID; ?>"
				class="form-check-input"
				type="radio"
				name="data[<?php echo $field['name']; ?>]"
				value="<?php echo htmlspecialchars($optValue); ?>"
				<?php if ( $field['value'] == $optValue ) echo 'checked'; ?>
				<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
				<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
			 />
			<label 
				for="<?php echo $radioID; ?>" 
				class="form-check-label small"
			><?php echo $optText; ?></label>
		</div>
		<?php $optIndex++; ?>
	<?php endforeach; ?>
	<?php if ( !empty($field['readonly']) ) : ?>
		<input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" />
	<?php endif; ?>


<!-- checkbox (submit array value) -->
<?php elseif ( isset($field['format']) and in_array($field['format'], array('checkbox','one-to-many','many-to-many')) ) : ?>
	<input type="hidden" name="data[<?php echo $field['name']; ?>][]" value="" />
	<?php $optIndex = 0; ?>
	<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
		<?php $checkboxID = uuid(); ?>
		<div class="form-check">
			<input
				id="<?php echo $checkboxID; ?>"
				class="form-check-input"
				type="checkbox"
				name="data[<?php echo $field['name']; ?>][]"
				value="<?php echo htmlspecialchars($optValue); ?>"
				<?php if ( in_array($optValue, $field['value']) ) echo 'checked'; ?>
				<?php if ( !empty($field['required']) and $optIndex == 0 ) echo 'required'; ?>
				<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
			 />
			<label 
				for="<?php echo $checkboxID; ?>" 
				class="form-check-label small"
			><?php echo $optText; ?></label>
		</div>
		<?php $optIndex++; ?>
	<?php endforeach; ?>
	<?php if ( !empty($field['readonly']) ) : ?>
		<?php foreach ( $field['value'] as $val ) : ?>
			<input type="hidden" name="data[<?php echo $field['name']; ?>][]" value="<?php echo htmlspecialchars($val); ?>" />
		<?php endforeach; ?>
	<?php endif; ?>


<!-- textarea -->
<?php elseif ( isset($field['format']) and $field['format'] == 'textarea' ) : ?>
	<textarea
		class="form-control form-control-sm"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
	><?php echo $field['value']; ?></textarea>


<!-- html editor -->
<?php elseif ( isset($field['format']) and $field['format'] == 'wysiwyg' ) : ?>
	<?php include 'input.wysiwyg.php'; ?>


<!-- file -->
<?php elseif ( isset($field['format']) and $field['format'] == 'file' ) : ?>
	<?php include 'input.file.php'; ?>


<!-- listbox -->
<?php elseif ( isset($field['options']) ) : ?>
	<select
		class="custom-select custom-select-sm"
		name="data[<?php echo $field['name']; ?>]"
		<?php if ( !empty($field['readonly']) ) echo 'disabled'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
	>
		<option value="">
			<?php if ( isset($field['placeholder']) ) echo $field['placeholder']; ?>
		</option>
		<?php foreach ( $field['options'] as $optValue => $optText ) : ?>
			<option
				value="<?php echo $optValue; ?>"
				<?php if ( $field['value'] == $optValue ) echo 'selected'; ?>
			><?php echo $optText; ?></option>
		<?php endforeach; ?>
	</select>
	<?php if ( !empty($field['readonly']) ) : ?>
		<input type="hidden" name="data[<?php echo $field['name']; ?>]" value="<?php echo htmlspecialchars($field['value']); ?>" />
	<?php endif; ?>


<!-- text -->
<?php else : ?>
	<input
		type="<?php echo ( empty($field['format']) or in_array($field['format'], array('date', 'time', 'datetime')) ) ? 'text' : $field['format']; ?>"
		class="form-control form-control-sm scaffold-input-<?php echo empty($field['format']) ? 'text' : $field['format']; ?>"
		name="data[<?php echo $field['name']; ?>]"
		value="<?php echo htmlspecialchars($field['value']); ?>"
		<?php if ( isset($field['style']) ) : ?>style="<?php echo $field['style']; ?>"<?php endif; ?>
		<?php if ( isset($field['placeholder']) ) : ?>placeholder="<?php echo $field['placeholder']; ?>"<?php endif; ?>
		<?php if ( !empty($field['readonly']) ) echo 'readonly'; ?>
		<?php if ( !empty($field['required']) ) echo 'required'; ?>
	 />


<?php endif; ?>


<!-- help -->
<?php if ( !empty($field['help']) ) : ?>
	<small class="form-text py-1 px-2 rounded bg-light text-info"><?php echo $field['help']; ?></small>
<?php endif; ?>