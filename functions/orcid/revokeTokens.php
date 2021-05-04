<?php 
/*

**** This file is responsible of revoking the ORCID token.

** Parameters :
	$client_id : The client id value <client-id> from ORCID client application registration.
	$client_secret : The client secret value <client-secret> from ORCID client application registration.
	$token :  ORCID token for admin user..



** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------
function revokeTokens($client_id, $client_secret, $token){

	$successHeader = 'HTTP/1.1 200 OK';
	$successResponsePortionNeeded = 'headers';
	
	$options = array(
	  CURLOPT_URL => ORCID_API_URL."/oauth/revoke",
	  CURLOPT_CUSTOMREQUEST => "POST",
	  CURLOPT_POSTFIELDS => "client_id=$client_id&client_secret=$client_secret&token=$token",
	  CURLOPT_HTTPHEADER => array(
		"Accept: application/json",
		"Cache-Control: no-cache",
		"Connection: keep-alive",
		"Content-Type: application/x-www-form-urlencoded"
	  )
	);

	$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

	return $response;
}

?>