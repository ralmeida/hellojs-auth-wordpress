//Hello.js auth login callback
hello.on('auth.login', function(auth) {
	var auth_code = auth.authResponse.access_token;
	// Call user information, for the given network
	hello(auth.network).api('/me').then(function(returnData) {
		//grab the access_token
		returnData.access_token = auth_code;
		//post to our endpoint
		jQuery.post('/hellojsauth/login/'+ auth.network +'/', returnData, 'json').always(function(data, req_success, req_error) {
			//if our request was successful and we returned true
			if(req_success == 'success' && data.success) {
				//redirect to the wp-admin
				//console.log("location = '/wp-admin/'");
				location = '/wp-admin/';
			} 
			else {
				localStorage.removeItem('hello');
				alert('There was an issue logging you in: ' + data.message);
			}
		});
	});
});