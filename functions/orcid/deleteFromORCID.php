<?php

/*

**** This file is responsible of deleting putcode from ORCID.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$accessToken : unique token for each user from ORCID.
	$type: (work, employment, education)
	$putCode: unique number for each (work, affiliation) in ORCID.

	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------


	function deleteFromORCID($orcid, $accessToken, $type, $putCode)
	{
		$successHeader = 'HTTP/1.1 201 Created';
		$successResponsePortionNeeded = 'headers';
		
		$options = array(
		  CURLOPT_URL => ORCID_API_URL.$orcid.'/'.$type.'/'.$putCode,
		  CURLOPT_CUSTOMREQUEST => "DELETE",
		  CURLOPT_POSTFIELDS => "",
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Content-Type: application/xml",
			"Authorization: Bearer $accessToken"
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;

	}