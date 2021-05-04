<?php
	
/*


**** This file is responsible for handling curl requests that combine default options with custom options .

** Parameters :
	$customOptions : the array of curl options specific to the request .
	$successHeader : the expected header to indicate that the request was successful .
	$successResponsePortionNeeded : whether we want to return the headers or body of the response on success.

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

	function makeCurlRequest($customOptions, $successHeader, $successResponsePortionNeeded)
	{
		$curl = curl_init();
		$headers = [];
		
		$defaultOptions = array(
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_HEADERFUNCTION => function($curl, $header) use (&$headers)
			{
				$len = strlen($header);
				
				$headers[]=$header;

				return $len;
			}
		);
		
		$options = $customOptions + $defaultOptions;
		
		curl_setopt_array($curl, $options);

		$response = curl_exec($curl);
		$error = curl_error($curl);
		$headers = implode('||',$headers);

		curl_close($curl);

		if($error) 
		{
			return array('error'=>"cURL Error #:" . $error,'response'=>$response,'headers'=>$headers);
		}
		elseif(strpos($headers, $successHeader)!==FALSE)
		{
			if($successResponsePortionNeeded === 'headers')
			{
				return $headers;
			}
			elseif($successResponsePortionNeeded === 'response')
			{
				return $response;
			}				
		}
		else
		{
			return array('response'=>$response,'headers'=>$headers);
		}
	}
