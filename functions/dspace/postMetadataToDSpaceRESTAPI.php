<?php
/*


**** This file is responsible of posting the metadata for the item to DSpace.

** Parameters :
	$token : DSpace token for admin user.
	$itemID : unique id for each item in DSpace.
	$json : the item metadata in json format.


** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

	function postMetadataToDSpaceRESTAPI($token, $itemID, $json)
	{
		$curl = curl_init();
		
		$curlArray = array(
		  CURLOPT_URL => DSPACE_REST_API."items/$itemID/metadata",
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "$json",
		  CURLOPT_HTTPHEADER => array(
		    "Accept: application/json",
		    "Content-Type: application/json",
		    "cache-control: no-cache",
		    "rest-dspace-token: $token"
		  ),
		);

		$response = makeCurlRequest($curlArray, '200 OK');
		
		return $response;
	}