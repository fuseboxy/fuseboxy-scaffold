<?php
//F::redirect('site', !Auth::activeUserInRole('SUPER,ADMIN'));


// default role
if ( !isset($arguments['role']) ) {
	$arguments['role'] = R::getCell("SELECT role FROM user ORDER BY role ");
}


// config
$scaffold = array(
	'beanType' => 'user',
	'editMode' => 'inline',
	'allowDelete' => true,  //Auth::activeUserInRole('SUPER'),
	'paramNew' => "&role={$arguments['role']}",
	'paramEdit' => "&role={$arguments['role']}",
	'layoutPath' => F::config('appPath').'view/user/layout.php',
	'listFilter' => "role = '{$arguments['role']}' ",
	'listOrder' => 'ORDER BY username',
	'listField' => array(
		'id' => '7%',
		'role|full_name' => '20%',
		'username|password' => '20%',
		'email|tel' => ''
	),
	'displayName' => array(
		'id' => 'ID',
		'role' => 'Role',
		'full_name' => 'Full Name',
		'username' => 'Login',
		'password' => 'Password',
		'email' => 'Email',
		'tel' => 'Phone'
	),
	'editField' => array(
		'id' => array(),
		'username' => array('placeholder' => 'Login'),
		'password' => array('placeholder' => 'Password'),
		'role' => array('default' => $arguments['role'], 'readonly' => true /*!Auth::activeUserInRole('SUPER')*/),
		'full_name' => array('placeholder' => 'Full Name'),
		'email' => array('placeholder' => 'Email'),
		'tel' => array('placeholder' => 'Phone')
	)
);


// component
$layout['width'] = 'full';
include 'scaffold_controller.php';