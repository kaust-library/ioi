<?php


/*


**** This file is responsible of returning specific item from DSpase.

** Parameters :
	No parameters required
	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------	


	function loginToDSpaceRESTAPI()
	{
		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL."login",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => '{"email": "'.REPOSITORY_USER.'", "password": "'.REPOSITORY_PW.'"}',
		  CURLOPT_HTTPHEADER => array(
			"Accept: application/json",
			"Cache-Control: no-cache",
			"Content-Type: application/json",
		  )
		);

		$response = makeCurlRequest($options, 'HTTP/1.1 200 OK', 'response');

		return $response;
	}