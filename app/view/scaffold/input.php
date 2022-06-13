<?php /*
<fusedoc>
	<io>
		<in>
			<object name="$bean"/>
			<string name="$fieldName"/>
			<string name="$dataFieldName" />
			<mixed name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" />
				<string name="pre-help" comments="help text show before input field" />
				<string name="help" comments="help text show after input field" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<mixed name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="scaffold-input form-group mb-1"><?php

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

?></div><!--/.form-group-->