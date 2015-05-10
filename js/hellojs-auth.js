//Hello.js auth login callback
hello.on('auth.login', function(auth) {
	//pull the auth_code for posting
	var auth_code = auth.authResponse.access_token;
	// Call user information, for the given network
	hello(auth.network).api('/me').then(function(returnData) {
		//grab the access_token
		returnData.access_token = auth_code;
		//grab the req_key
		returnData.itsit = hellojsauth_req_key;
		//clear any user input
		jQuery("#user_login").val("");
		jQuery("#user_pass").val("");
		//post to our endpoint
		jQuery.post('/hellojsauth/login/'+ auth.network +'/', returnData, 'json').always(function(data, req_success, req_error) {
			//if our request was successful and we returned true
			if(req_success == 'success' && data.success) {
				//redirect to the wp-admin
				location = '/wp-admin/';
			} 
			else {
				//if we get an error we shold remove our local storage
				localStorage.removeItem('hello');
				//notify the user
				alert('There was an issue logging you in: ' + data.message);
			}
			jQuery("#loginform").slideDown();
		});
	});
});