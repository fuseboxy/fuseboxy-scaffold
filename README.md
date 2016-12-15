[dependencies]

redbeanphp (included)
bootstrap-extend
simple-ajax-uploader (included)
ckeditor 4.6.x (cdn)
jquery.validate (cdn)
formUrl2arguments (enabled)




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