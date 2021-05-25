<?php


/*

**** This file is responsible for sending the user to ORCID to authenticate and approve creation of an access token for the requested scopes.

** Parameters :
	No parameters required

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//-----------------------------------------------------------------------------------------------------------

	if(isset($_GET['family_names']))
	{
		$family_names = $_GET['family_names'];
	}
	if(isset($_GET['given_names']))
	{
		$given_names = $_GET['given_names'];
	}
	if(isset($_GET['orcid']))
	{
		$orcid = $_GET['orcid'];
	}
	$email = $_GET['email'];

	$state = bin2hex(openssl_random_pseudo_bytes(16));
	setcookie('oauth_state', $state, time() + 3600, null, null, false, true);

	//set the appropriate scopes based on user selections
	$scopes = array('/authenticate');

	if(isset($_GET['read']))
	{
		array_push($scopes, '/read-limited');
	}
	
	if(isset($_GET['addActivities']))
	{
		array_push($scopes, '/activities/update');
	}	
	
	// save the scope in the session
	$_SESSION['scope'] = implode(' ', $scopes);
	
	//prepare URL with parameters when sending the user to ORCID
	$url = OAUTH_AUTHORIZATION_URL . '?' . http_build_query(array(
	'response_type' => 'code',
	'client_id' => OAUTH_CLIENT_ID,
	'redirect_uri' => OAUTH_REDIRECT_URI,
	'family_names' => $family_names,
	'given_names' => $given_names,
	'orcid' => $orcid,
	'email' => $email,
	'scope' => implode(' ', $scopes),
	'state' => $state,
	));

	header('Location: ' . $url);
	exit();
?>