<?php
/**
 *  RedBean was chosen to include in the package because of its simplicity
 *  ===> To use it, includes RedBean core file and this configuration file in <index.php>
 *  ===> RedBean will create/modify SQLite database & table if it is not freezed
 *  ===> More databases are supported. For details, please visit <http://redbeanphp.com/>
 **/


/**
 *  Use MySQL database
 **/
//R::setup('mysql:host=localhost;dbname=mydatabase','user','password');


/**
 *  Use SQLite database
 *  ===> Browse database by phpLiteAdmin when necessary
 **/
R::setup('sqlite:'.dirname(dirname(dirname(__FILE__))).'/data/sqlite.db');


/**
 *  Freeze RedBean in production environment
 *  ===> Set to TRUE it if you don't want RedBean to CREATE/ALERT table automatically
 **/
//R::freeze( !in_array(strtolower($_SERVER['SERVER_NAME']), array('127.0.0.1','localhost')) );
R::freeze(false);
R::debug(false);