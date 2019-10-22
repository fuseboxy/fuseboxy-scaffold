Fuseboxy Scaffold
=================

Component to generate user interface and business logic for CRUD operation.




## Third-party Components
The global layout module includes CDN of following JS and CSS libraries to provide a faster development environment:
* jQuery
* Bootstrap
* Font Awesome

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
aws sdk for php
* lib/aws/3.x/
redbeanphp (included)
* lib/redbeanphp/5.x/
simple-ajax-uploader (included)
* lib/simple-ajax-uploader/2.x/
bootstrap-extend (cdn)
* https://bbcdn.githack.com/henrygotmojo/bootstrap-extend/raw/4.0/bootstrap.extend.min.css
* https://bbcdn.githack.com/henrygotmojo/bootstrap-extend/raw/4.0/bootstrap.extend.min.js
summernote (cdn)
* https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.css
* https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.min.js
jquery-datetimepicker (cdn)
* https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.min.css
* https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js
fancybox (cdn)
* https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css
* https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js




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