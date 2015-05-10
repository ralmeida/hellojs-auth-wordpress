<?php

class HelloJSAuthSettings
{
	var $enabled = false;
	
	var $update_user_on_login = false;

	var $source = array(
		'jquery' => '//code.jquery.com/jquery-1.11.3.min.js',
		'hellojs' => '//cdnjs.cloudflare.com/ajax/libs/hellojs/1.5.1/hello.all.min.js',
		'zocial' => '//cdnjs.cloudflare.com/ajax/libs/zocial/0/zocial.css',
		'google_font' => '//fonts.googleapis.com/css?family=Pompiere',
	);

	//TODO: we could add in custom settngs per provider as functionality is expanded
	var $providers = array();
}

?>