<?php
/**
 *  Define fusebox configuration
 *  ===> all things defined here could be accessed by {$fusebox->config} or F::config() later
 **/

return array(


	/**
	 *  Default page (REQUIRED)
	 *  ===> command is in [controller].[action] format
	 *  ===> if no [action] was specified, fusebox will automatically resolve it to 'index'
	 **/
	'defaultCommand' => 'user',


	/**
	 *  For resolving command parameter (REQUIRED)
	 *  ===> use 'fuseaction' in remembrance of original Fusebox framework
	 *  ===> feel free to use another other name
	 **/
	'commandVariable' => 'fuseaction',


	/**
	 *  Delimiter of command parameter (REQUIRED)
	 *  ===> should match {defaultCommand} config
	 *  ===> only support dot (.), dash (-), and underscore (_)
	 **/
	'commandDelimiter' => '.',


	/**
	 *  Directory to load controller, model, view, etc. (REQUIRED)
	 **/
	'appPath' => dirname(dirname(__FILE__)).'/',


	/**
	 *  For path of stylesheet, script, etc. (OPTIONAL)
	 **/
	'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) ),


	/**
	 *  Directories or files to auto-include (OPTIONAL)
	 *  ===> if directory is specified, only PHP will be included
	 *  ===> if directory is specified, the include will be non-recursive
	 *  ===> full path is required
	 *  ===> wildcard is allowed
	 **/
	'autoLoad' => array(
		dirname(dirname(__FILE__)).'/model/*',
		dirname(dirname(dirname(__FILE__))).'/lib/redbeanphp/4.3.3/rb.php',
		dirname(dirname(__FILE__)).'/config/rb_config.php',
	),


	/**
	 *  Create an associative array which combines $_GET and $_POST (OPTIONAL)
	 *  ===> allow evaluating $_GET and $_POST variables by a single token without including $_COOKIE in the mix
	 **/
	'formUrl2arguments' => array($_GET, $_POST),


	/**
	 * Controller to handle error (OPTIONAL)
	 * ===> use by F::error() and F::pageNotFound()
	 * ===> if not defined, only text message will be displayed
	 * ===> controller will receive {$fusebox->error} as argument
	 **/
	'errorController' => dirname(dirname(__FILE__)).'/controller/error_controller.php',


	/**
	 * Upload directory (OPTIONAL)
	 * ===> for scaffold file upload
	 * ===> set it to 777 mode
	 **/
	'uploadDir' => dirname(dirname(dirname(__FILE__))).'/data/upload/',


	/**
	 * Temp directory (OPTIONAL)
	 * ===> for cache or log
	 * ===> set it to 777 mode
	 **/
	'tmpDir' => dirname(dirname(dirname(__FILE__))).'/tmp/',


	/**
	 *  Use beauty-url (OPTIONAL)
	 *  ===> apply F::url() to all links
	 *  ===> there will be no script name
	 *  ===> controller (if any) and action (if any) will be the first two items after base path
	 *  ===> remember to modify .htaccess if doing url-rewrite (uncomment the line 'RewriteEngine on')
	 **/
	'urlRewrite' => false,


	/**
	 * Route setting (OPTIONAL)
	 * ===> only applicable when {urlRewrite=true}
	 * ===> using regex and back-reference to turn path-like-query-string into query-string (forward-slash will be escaped)
	 * ===> mapped parameters will go to {$_GET} scope; un-mapped string will not be parsed
	 * ===> first match expression will be used; so please take the sequence into consideration
	 * ===> array-key is pattern which match {$_SERVER['PATH_INFO']} (with or without leading slash)
	 * ===> array-value is transformed query-string (without leading question mark)
	 **/
	'route' => array(
//		'/article/(\d)' => 'fuseaction=article.view&id=$1',
//		'([\s\S]+)' => 'fuseaction=cms.view&path=$1',
	),


	/**
	 * SMTP config (OPTIONAL)
	 * ===> for Util::sendMail
	 **/
	'smtp' => file_exists(dirname(__FILE__).'/smtp_config.php') ? include 'smtp_config.php' : false,


	/**
	 * reCAPTCHA config (OPTIONAL)
	 * ===> for Auth and WebForm
	 **/
	'reCAPTCHA' => file_exists(dirname(__FILE__).'/recaptcha_config.php') ? include 'recaptcha_config.php' : false,


);