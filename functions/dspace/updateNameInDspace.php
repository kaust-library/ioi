<?php

/*

**** This file is responsible of changing a name in the DSpace.

** Parameters :
	$authoritykey : The key that is associated with the person's orcid id in DSpace.
	$restdspacetoken : login token for DSpace.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 Sept - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function updateNameInDspace($authorityKey, $name, $restdspacetoken)
{
	$successHeader = 'HTTP/1.1 200 OK';
	$successResponsePortionNeeded = 'headers';
	
	$options = array(
	  CURLOPT_URL => REPOSITORY_API_URL."authorities/".$authorityKey."/value",
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_CUSTOMREQUEST => "PUT",
	  CURLOPT_POSTFIELDS => $name,
	  CURLOPT_HTTPHEADER => array(
		"cache-control: no-cache",
		"Content-Type: text/plain",
		"rest-dspace-token: ".$restdspacetoken
		)
	);

	$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

	return $response;

}