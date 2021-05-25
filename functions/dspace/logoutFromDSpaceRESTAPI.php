<?php

/*


**** This file is responsible of logining out DSpace system.

** Parameters :
	$dSpaceAuthHeader : DSpace token for admin user.

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------


	function logoutFromDSpaceRESTAPI($dSpaceAuthHeader)
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL."logout",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			$dSpaceAuthHeader
		  )
		);

		$response = makeCurlRequest($options);

		return $response;
	}
