<?php

/*

**** This file is responsible of getting single work from ORCID.

** Parameters :
	No parameters required	

** Created by : Yasmeen alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019- 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

function getSingleActivity($orcid, $accessToken, $type, $putCode){

		$successHeader = 'HTTP/1.1 201 Created';
		$successResponsePortionNeeded = 'headers';
		
		$options = array(
		  CURLOPT_URL => ORCID_API_URL.$orcid.'/'.$type.'/'.$putCode,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
		    "Accept: */*",
		    "Cache-Control: no-cache",
		    "Authorization: Bearer $accessToken",
		    "Connection: keep-alive",
		    "accept-encoding: gzip, deflate",
		    "cache-control: no-cache",
  ),
  );

		echo ORCID_API_URL.$orcid.'/'.$type.'/'.$putCode;

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;

	}




