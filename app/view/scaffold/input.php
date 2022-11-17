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
				<string name="scriptPath" optional="yes" />
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
	if ( !empty($fieldConfig['pre-help']) ) include F::appPath('view/scaffold/input.pre_help.php');
	// determine path
	$defaultPath = F::appPath('view/scaffold/input.default.php');
	if ( !empty($fieldConfig['format']) and $fieldConfig['format'] == 'custom' ) $scriptPath = $fieldConfig['scriptPath'];
	elseif ( !empty($fieldConfig['format']) ) $scriptPath = F::appPath('view/scaffold/input.'.str_replace('-', '_', $fieldConfig['format']).'.php');
	else $scriptPath = $defaultPath;
	// display : field
	include is_file($scriptPath) ? $scriptPath : $defaultPath;
	// display : help
	if ( !empty($fieldConfig['help']) ) include F::appPath('view/scaffold/input.help.php');
?></div><!--/.form-group-->