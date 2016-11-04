<?php
// tab layout config
$tabLayout = array(
	'style' => 'tab',
	'position' => 'left',
	'header' => '<h3>Settings</h3>',
	'nav' => array()
);


// tab : all existing types
$types = R::getCol('SELECT DISTINCT type FROM enum ORDER BY type');
foreach ( $types as $t ) {
	$tabLayout['nav'][] = array(
		'name' => ucwords( str_replace('_', ' ', strtolower($t) ) ),
		'url' => F::url("{$fusebox->controller}&type={$t}"),
		'active' => ( !empty($arguments['type']) and $arguments['type'] == $t )
	);
}


// tab : new type
$tabLayout['nav'][] = array(
	'name' => '<em class="text-muted">+ New Setting</em>',
	'url' => F::url("{$fusebox->controller}&type=undefined"),
	'active' => ( empty($arguments['type']) or $arguments['type'] == 'undefined' )
);


// tab layout
ob_start();
include F::config('appPath').'view/global/tab.php';
$layout['content'] = ob_get_clean();


// wrap by global layout
include F::config('appPath').'view/global/layout.php';