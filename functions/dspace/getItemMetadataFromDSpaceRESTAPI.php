<?php

/*
**** This file is responsible of returning specific item metadata from DSpase using item id.

** Parameters :
	$itemID : unique id for each item in DSpace.
	$token :  DSpace token for admin user.
	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------	

	function getItemMetadataFromDSpaceRESTAPI($itemID, $token)
	{
		$successHeader = 'HTTP/1.1 200 OK';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/metadata',
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			"rest-dspace-token: $token"
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}
