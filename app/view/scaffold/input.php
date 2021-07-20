<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$xfa">
				<string name="ajaxUpload" comments="for [format=file] field" />
				<string name="ajaxUploadProgress" comments="for [format=file] field" />
			</structure>
			<structure name="$fieldConfig">
				<string name="name" />
				<string name="format" comments="text|hidden|output|textarea|radio|checkbox|file|date|time|datetime|one-to-many|many-to-many|wysiwyg" default="text" />
				<string name="icon" optional="yes" />
				<string name="value" optional="yes" />
				<string name="default" optional="yes" />
				<array name="options" comments="show dropdown when no {format} specified; it can also serve {format=radio|checkbox}">
					<string name="~key is option-value~" comments="value is option-text" />
				</array>
				<boolean name="required" />
				<boolean name="readonly" comments="output does not pass value; readonly does" />
				<string name="placeholder" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
				<string name="pre-help" comments="help text show before input field" />
				<string name="help" comments="help text show after input field" />
				<!-- for [format=file|image] only -->
				<string name="filesize" optional="yes" comments="max file size in bytes" example="10MB|2000KB" />
				<list name="filetype" optional="yes" delim="," example="gif,jpg,jpeg,png" />
			</structure>
			<object name="$bean" comments="for field value" />
		</in>
		<out />
	</io>
</fusedoc>
*/
// force using user-defined value (when specified)
if ( isset($fieldConfig['value']) ) {
	// ~~~ do nothing ~~~

// checkbox (one-to-many|many-to-many)
// ===> one-to-many  : get value from own-list
// ===> many-to-many : get value from shared-list
} elseif ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], array('one-to-many','many-to-many')) ) {
	$fieldConfig['value'] = array();
	$associateName = str_replace('_id', '', $fieldConfig['name']);
	$propertyName = ( ( $fieldConfig['format'] == 'one-to-many' ) ? 'own' : 'shared' ) . ucfirst($associateName);
	foreach ( $bean->{$propertyName} as $tmp ) $fieldConfig['value'][] = $tmp->id;

// other type
// ===> simple value
} elseif ( isset($bean->{$fieldConfig['name']}) ) {
	$fieldConfig['value'] = $bean->{$fieldConfig['name']};

// no value
// ===> apply default value
} elseif ( isset($fieldConfig['default']) ) {
	$fieldConfig['value'] = $fieldConfig['default'];

// empty value
} else {
	$fieldConfig['value'] = '';
}


// fix options (when necessary)
// ===> when options was not specified
// ===> use field value as options
if ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], array('radio','checkbox','one-to-many','many-to-many')) and !isset($fieldConfig['options']) ) {
	$fieldConfig['options'] = array();
	if ( $fieldConfig['format'] == 'radio' ) {
		$fieldConfig['options'][$fieldConfig['value']] = $fieldConfig['value'];
	} else {
		foreach ( $fieldConfig['value'] as $val ) $fieldConfig['options'][$val] = $val;
	}
}


// fix checkbox value (when necessary)
// ===> turn pipe-delimited list into array
if ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'checkbox' and !is_array($fieldConfig['value']) ) {
	$fieldConfig['value'] = explode('|', $fieldConfig['value']);
}


// display : pre-help
if ( !empty($fieldConfig['pre-help']) ) {
	include F::appPath('view/scaffold/input.pre_help.php');
}


// display : output
if ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'output' ) {
	include F::appPath('view/scaffold/input.output.php');
// display : radio
} elseif ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'radio' ) {
	include F::appPath('view/scaffold/input.radio.php');
// display : checkbox (submit array value)
} elseif ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], ['checkbox','one-to-many','many-to-many']) ) {
	include F::appPath('view/scaffold/input.checkbox.php');
// display : textarea
} elseif ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'textarea' ) {
	include F::appPath('view/scaffold/input.textarea.php');
// display : html editor
} elseif ( isset($fieldConfig['format']) and $fieldConfig['format'] == 'wysiwyg' ) {
	include F::appPath('view/scaffold/input.wysiwyg.php');
// display : file upload
} elseif ( isset($fieldConfig['format']) and in_array($fieldConfig['format'], ['file','image']) ) {
	include F::appPath('view/scaffold/input.file.php');
// display : dropdown
} elseif ( isset($fieldConfig['options']) ) {
	include F::appPath('view/scaffold/input.dropdown.php');
// display : date & time
} elseif ( !empty($fieldConfig['format']) and in_array($fieldConfig['format'], ['date', 'time', 'datetime']) ) {
	include F::appPath('view/scaffold/input.datetime.php');
// display : normal text
} else {
	include F::appPath('view/scaffold/input.default.php');
}


// display : help
if ( !empty($fieldConfig['help']) ) {
	include F::appPath('view/scaffold/input.help.php');
}