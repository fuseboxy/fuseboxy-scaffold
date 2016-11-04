<?php
// default type
$arguments['type'] = isset($arguments['type']) ? $arguments['type'] : R::getCell('SELECT type FROM enum ORDER BY type');


// config
$scaffold = array(
	'beanType' => 'enum',
	'editMode' => 'inline',
	'allowDelete' => Auth::activeUserInRole('SUPER'),
	'paramNew' => "&type={$arguments['type']}",
	'paramEdit' => "&type={$arguments['type']}",
	'layoutPath' => F::config('appPath').'view/enum/layout.php',
	'listFilter' => isset($arguments['type']) ? " type = '{$arguments['type']}' " : ' type IS NULL ',
	'listField' => array(
		'id' => '7%',
		'key|type' => '15%',
		'value|remark' => '25%',
		'photo' => '35%',
		'seq' => '7%'
	),
	'displayName' => array(
		'id' => 'ID',
		'type' => 'Type',
		'key' => 'Key',
		'value' => 'Value',
		'remark' => 'Remark',
		'seq' => 'Seq'
	),
	'editField' => array(
		'id' => array(),
		'type' => array('placeholder' => 'Type', 'readonly' => !Auth::activeUserInRole('SUPER'), 'default' => isset($arguments['type']) ? $arguments['type'] : ''),
		'key' => array('placeholder' => 'Key'),
		'value' => array('placeholder' => 'Value'),
		'remark' => array('placeholder' => 'Remark'),
		'photo' => array('format' => 'file', 'filesize' => '4mb', 'filetype' => 'png,gif,jpg', 'preview' => true),
		'seq' => array('placeholder' => 'Seq')
	),
	'uploadBaseUrl' => F::config('baseUrl').'data/upload/',
);


// component
$layout['width'] = 'full';
include 'scaffold_controller.php';