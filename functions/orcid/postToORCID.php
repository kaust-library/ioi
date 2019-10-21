<?php
	/*

**** This file is responsible of posting the xml data to ORCID.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$accessToken : unique token for each user from ORCID.
	$type : (work, employment, education).
	$xml : data in xml format.


** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 


*/

//-----------------------------------------------------------------------------------------------------------

	
	function postToORCID($orcid, $accessToken, $type, $xml)
	{
		$successHeader = 'HTTP/1.1 201 Created';
		$successResponsePortionNeeded = 'headers';
		
		$options = array(
		  CURLOPT_URL => ORCID_API_URL.$orcid.'/'.$type,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "$xml",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/xml",
			"Authorization: Bearer $accessToken"
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}