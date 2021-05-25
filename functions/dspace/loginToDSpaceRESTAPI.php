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
		if(DSPACE_VERSION === '5')
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

			$response = makeCurlRequest($options, 'HTTP/1.1 200', 'response');
			
			if(is_string($response))
			{
				$response = 'rest-dspace-token: '.$response;
			}
		}
		elseif(DSPACE_VERSION === '6')
		{
			$options = array(
			  CURLOPT_URL => REPOSITORY_API_URL."login",
			  CURLOPT_FOLLOWLOCATION => true,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => 'email='.urlencode(REPOSITORY_USER).'&password='.REPOSITORY_PW,
			  CURLOPT_HTTPHEADER => array(
				"Content-Type: application/x-www-form-urlencoded",
			  )
			);

			$response = makeCurlRequest($options, 'HTTP/1.1 200', 'headers');
			
			if(is_string($response))
			{
				$headers = explode('||', $response);
				
				foreach($headers as $header)
				{
					if(strpos($header, 'Set-Cookie: JSESSIONID=')!==FALSE)
					{
						$header = str_replace('Set-Cookie: ', '', $header);
						
						$headerParts = explode('; ', $header);
						
						$response = 'Cookie: '.$headerParts[0];
					}
				}
			}
		}

		return $response;
	}