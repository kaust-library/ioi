<?php

/*


**** This file is responsible of getting an access token for a new user or a new token if the user changed their permissions.

** Parameters :
	No paramaters required

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function getNewAccessToken(){
	
	global $ioi;

	//get the code, stats and email from the session
	$code = $_GET['code'];
	$state = $_GET['state'];
	$email = $_SESSION[LDAP_EMAIL_ATTRIBUTE];

	// get the oauth token url 
	$url = OAUTH_TOKEN_URL;

	// prepare the client data
	$data = 'client_id='.OAUTH_CLIENT_ID.'&client_secret='.OAUTH_CLIENT_SECRET.'&grant_type=authorization_code&code='.$code.'&redirect_uri='.OAUTH_REDIRECT_URI;
	
	$opts = array(
	  'http'=>array(
		'method'=>'POST',
		'header'=>'Accept: application/json',
		'header'=>'Content-type: application/x-www-form-urlencoded',
		'content' => $data
	  )
	);
	
	$context = stream_context_create($opts);
	
	//send context to the ORCID
	$result = file_get_contents($url, false, $context);		
	
	$response = json_decode($result, true);	

	//get the information from response
	$orcid = $response['orcid'];
	$access_token = $response['access_token'];
	$expires_in = $response['expires_in'];
	$scope = $response['scope'];
	$name = $response['name'];
	$refresh_token =  $response['refresh_token'];
	$today = date('Y-m-d H:i:s');
	$expiration = date('Y-m-d H:i:s', time()+$expires_in);
	
	// New user ?
	$check = $ioi->query("SELECT * FROM `orcids` WHERE email = '$email'");
			
	if($check->num_rows === 1)
	{
		//update the name and the orcid for the user
		$ioi->query("UPDATE orcids SET orcid = '$orcid', name = '$name' WHERE email = '$email'");
	}
	else
	{
		// insert the data from the new user
		$ioi->query("INSERT INTO orcids (email, orcid, name) VALUES ('$email', '$orcid', '$name')");
	}
	
	return [$orcid, $access_token, $expires_in, $scope, $name, $refresh_token, $today, $expiration ];
}
