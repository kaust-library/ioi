<?php


/*


**** This file is responsible of editting the item metadata using item id.

** Parameters :
	$itemID : unique id for each item in DSpace.
	$item : the item metadata in JSON format.
	$dSpaceAuthHeader : DSpace token for admin user.



** Created by : Daryl Grenz and Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

	function putItemMetadataToDSpaceRESTAPI($itemID, $item, $dSpaceAuthHeader)
	{
		$successHeader = 'HTTP/1.1 200';
		$successResponsePortionNeeded = 'response';

		$options = array(
		  CURLOPT_URL => REPOSITORY_API_URL.'items/'.$itemID.'/metadata',
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS => "$item",
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
