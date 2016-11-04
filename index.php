<?php
// debug settings
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
// environment settings
date_default_timezone_set('Asia/Taipei');
// for disable ie-compatible mode
header("X-UA-Compatible: IE=Edge");
// session management
session_name('FUSEBOXY');
session_start();
// load framework and page
require_once 'app/framework/1.0/fuseboxy.php';