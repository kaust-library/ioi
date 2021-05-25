<?php

/*

**** This file is responsible of returning specific item metadata from DSpase using the handle.

** Parameters :
	$handle : unique id for each item from http://hdl.handle.net/.
	$dSpaceAuthHeader :  DSpace token for admin user.
	$expand : list of sections of response to expand with full detail, by default is null, if set to "metadata", then the full item metadata will be included in the response.	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------	

function getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader, $expand = NULL)
{
	if(is_null($expand))
	{
		$url = REPOSITORY_API_URL.'handle/'.$handle;
	}
	else
	{
		$url = REPOSITORY_API_URL.'handle/'.$handle.'?expand='.$expand;
	}
	
	$successHeader = 'HTTP/1.1 200';
	$successResponsePortionNeeded = 'response';

	$options = array(
	  CURLOPT_URL => $url,
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
