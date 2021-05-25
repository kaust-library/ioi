<?php

/*

**** This file is responsible for getting an access token for a new user or a new token if the user changed their permissions.

** Parameters :
	No paramaters required

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function getNewAccessToken()
{	
	global $ioi;

	//get the code, state, email, and personID from the session
	$code = $_GET['code'];

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
	
	//send context to ORCID
	$result = file_get_contents(OAUTH_TOKEN_URL, false, $context);		
	
	$response = json_decode($result, true);	
	
	return $response;
}
