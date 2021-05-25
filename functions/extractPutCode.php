<?php	
	
/*


**** This file defines a function to extract the putCode from the header string taken from the ORCID API response.

** Parameters :
	$response : header string taken from the ORCID API response.	
	
** Returns :
	$putCode : extracted putCode.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 17 September 2019 - 2:09 AM 

*/

//-----------------------------------------------------------------------------------------------------------
function extractPutCode($response)
{	

	$location = explode('/', explode('Location: ', $response)[1]);
	$putCode = trim(explode('||',$location[6])[0]);
	
	return $putCode;		
}	
