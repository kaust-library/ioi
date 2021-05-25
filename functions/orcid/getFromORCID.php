<?php

/*

**** This file is responsible for getting data from ORCID.

** Parameters :
	$orcid
	$accessToken
	$type = used if only entries of a given type should be retrieved
	$putCode = used if only a single entry should be retrieved

** Created by : Yasmeen alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019- 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

function getFromORCID($orcid, $accessToken, $type, $putCode){

	if(!is_null($putCode)&&!is_null($type))
	{
		$url = ORCID_API_URL.$orcid.'/'.$type.'/'.$putCode;
	}
	elseif(!is_null($type))
	{
		$url = ORCID_API_URL.$orcid.'/'.$type;
	}
	else
	{
		$url = ORCID_API_URL.$orcid;
	}

	$successHeader = 'HTTP/1.1 200';
	$successResponsePortionNeeded = 'response';
	
	$options = array(
	  CURLOPT_URL => $url,
	  CURLOPT_RETURNTRANSFER => true,
	  CURLOPT_ENCODING => "",
	  CURLOPT_MAXREDIRS => 10,
	  CURLOPT_TIMEOUT => 30,
	  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	  CURLOPT_CUSTOMREQUEST => "GET",
	  CURLOPT_HTTPHEADER => array(
		"Accept: application/json",
		"Cache-Control: no-cache",
		"Authorization: Bearer $accessToken",
		"Connection: keep-alive",
		"accept-encoding: gzip, deflate",
		"cache-control: no-cache",
		),
	);

	$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

	return $response;
}
