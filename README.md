Fuseboxy Scaffold
=================

Component to generate user interface and business logic for CRUD operation.




## Third-party Components
The global layout module includes CDN of following JS and CSS libraries to provide a faster development environment:
* jQuery
* Bootstrap
* Font Awesome
* HTML Shiv
* Respond JS

Please be noted that the Fuseboxy framework core does **NOT** depend on any one of these.

Therefore, developer could feel free to keep/remove any of these at `app/view/global/layout.basic.php` whenever applicable.




## Configuration & Installation

1. Modify **Fusebox Config**
	* Enable `formUrl2arguments`
	*

2. Add **ReadBeanPHP** ORM config

3. Load **javascript library** at global layout



1. Enable **output_buffering** of PHP settings:
	* e.g. `output_buffering = 4096`

2. Add following config into **app/config/fusebox_config.php** if not already exists:
	* `'baseUrl' => str_replace('//', '/', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']).'/' ) )`

3. Copy files from the package into your application:
	* `app/view/global/*`
	* `css/main.css` *- optional*
	* `js/main.js` *- optional*
	* `test/test_fuseboxy_global_layout.php` *- optional (Only if you want to perform a unit test)*

4. Open and edit following variables at **app/view/global/layout.php**:
	* `$layout['metaTitle']`
	* `$layout['brand']`

5. Done.




[dependencies]

formUrl2arguments (enabled)
redbeanphp (included)
bootstrap-extend
simple-ajax-uploader (included)
ckeditor 4.6.x (cdn)
jquery.validate (cdn)




[optional]
Log
Bean




[Config]

fusebox->config['autoLoad']

	dirname(dirname(dirname(__FILE__))).'/lib/redbeanphp/{VERSION}/rb.php',
	dirname(dirname(__FILE__)).'/config/rb_config.php',




[validation]

RedBean_SimpleModel
===> validate when update