<?php
$layout['metaTitle'] = 'Fuseboxy';
$layout['brand'] = "Fuseboxy <sup>1.0</sup>";


// left menu (none)
$arguments['nav'] = array(
	array('name' => 'Documentation', 'url' => F::url('doc'), 'active' => F::is('doc.*')),
	array('name' => 'Demo', 'menus' => array(
		array('name' => 'Auth', 'url' => F::url('auth'), 'active' => F::is('auth.*')),
		array('name' => 'CMS', 'url' => F::url('cms'), 'active' => F::is('cms.*')),
		array('name' => 'WebForm', 'url' => F::url('survey'), 'active' => F::is('survey.*')),
	)),
);


// right menu : settings
$arguments['navRight'] = array();
$arguments['navRight'][] = array(
	'name' => '<i class="fa fa-cog"></i>',
	'active' => F::is('user.*'),
	'menus' => array(
		array('navHeader' => '<strong>SETTINGS</strong>', 'divider' => 'after'),
		array('name' => 'Log', 'url' => F::url('log'), 'active' => F::is('log.*')),
		array('name' => 'Enum', 'url' => F::url('enum'), 'active' => F::is('enum.*')),
		array('name' => 'User', 'url' => F::url('user'), 'active' => F::is('user.*')),
	),
);


// right menu : user-sim
/*if ( Auth::userInRole('SUPER') ) {
	$sim_users = R::find('user', "id != ? AND role != 'SUPER' AND IFNULL(disabled, 0) = 0 ORDER BY username ASC", array(Auth::user('id')));
	$sim_menus = array();
	if ( !empty($sim_users) ) {
		$sim_menus[] = array('navHeader' => '<strong>USER SIMULATION</strong>', 'divider' => 'after');
		foreach ( $sim_users as $u ) {
			$sim_menus[] = array('name' => $u->username, 'url' => F::url("auth.start_sim&user_id={$u->id}"));
		}
	}
	if ( Sim::user() ) {
		$sim_menus[] = array('name' => '<i class="fa fa-eye-slash"></i> End Sim', 'url' => F::url('auth.end_sim'), 'divider' => 'before');
	}
	if ( !empty($sim_menus) ) {
		$arguments['navRight'][] = array(
			'name' => Sim::user() ? '<i class="fa fa-eye"></i>' : '<i class="fa fa-eye-slash"></i>',
			'menus' => $sim_menus, 'active' => Sim::user()
		);
	}
}*/


// right menu : logout
$arguments['navRight'][] = array(
	'name' => "<img src='//ssl.gstatic.com/accounts/ui/avatar_2x.png' class='img-rounded' style='height: 32px; width: 32px; margin: -10px 0;' />",
	'menus' => array(
//		array('navHeader' => strtoupper('<strong>'.Auth::user('role').' : '.Auth::user('username').'</strong>'), 'divider' => 'after'),
		array('name' => '<i class="fa fa-power-off"></i> Sign Out', 'url' => F::url('auth.logout'))
	)
);


// user-sim notification
/*if ( Sim::user() ) {
	$arguments['topFlash'] = array('type' => 'info', 'message' => 'You are simulating <u><strong>'.Sim::user('username').'</strong></u>');
}*/


// display altogether
ob_start();
include 'layout.body.php';
include 'modal.php';  // modal in different sizes
$layout['content'] = ob_get_clean();


// wrap by html & body
include 'layout.basic.php';