<?php

class HelloJSProviders
{
	public static $data = array(
		'amazon' => array(
			'label' => 'Amazon',
			'icon' => 'amazon',
			'settings' => array()
		),
		/*'appsquared' => array(
			'label' => 'AppSquared',
			'icon' => 'lkdto',
			'settings' => array()
		),*/
		/*'bikeindex' => array(
			'label' => 'Bikeindex',
			'icon' => 'bikeindex',
			'settings' => array()
		),
		'box' => array(
			'label' => 'Box',
			'icon' => 'box',
			'settings' => array()
		),*/
		'dropbox' => array(
			'label' => 'Dropbox',
			'icon' => 'dropbox',
			'settings' => array()
		),
		'facebook' => array(
			'label' => 'Facebook',
			'icon' => 'facebook',
			'settings' => array(
				//TODO: as a mroe secure way of verification if the api_urls that are used for the /me calls are captured here
				//		then it would be fairly easy to make an API call on the server side to validate the access_token / user
				'api_url' => ''
			)
		),
		'flickr' => array(
			'label' => 'Flickr',
			'icon' => 'flickr',
			'settings' => array()
		),
		'foursquare' => array(
			'label' => 'FourSquare',
			'icon' => 'foursquare',
			'settings' => array()
		),
		'github' => array(
			'label' => 'GitHub',
			'icon' => 'github',
			'settings' => array()
		),
		'google' => array(
			'label' => 'Google',
			'icon' => 'google',
			'settings' => array()
		),
		'instagram' => array(
			'label' => 'Instagram',
			'icon' => 'instagram',
			'settings' => array()
		),
		'linkedin' => array(
			'label' => 'LinkedIn',
			'icon' => 'linkedin',
			'settings' => array()
		),
		'soundcloud' => array(
			'label' => 'SoundCloud',
			'icon' => 'soundcloud',
			'settings' => array()
		),
		'tumblr' => array(
			'label' => 'Tumblr',
			'icon' => 'tumblr',
			'settings' => array()
		),
		'twitter' => array(
			'label' => 'Twitter',
			'icon' => 'twitter',
			'settings' => array()
		),
		'windows' => array(
			'label' => 'Windows',
			'icon' => 'windows',
			'settings' => array()
		),
		'yahoo' => array(
			'label' => 'Yahoo!',
			'icon' => 'yahoo',
			'settings' => array()
		)
	);
}
