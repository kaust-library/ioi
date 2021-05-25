<?php

/*


**** This file is responsible of returning the status of the DSpace token.

** Parameters :
	$dSpaceAuthHeader : DSpace token for admin user.


** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------


	function statusOfTokenForDSpaceRESTAPI($dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL."status",
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options, $successHeader, $successResponsePortionNeeded);

		return $response;
	}
