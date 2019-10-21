<?php
/*


**** This file is responsible of getting the access token from database.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

	function getAccessTokenFromDB($orcid)
	{
	

		global $ioi;
		$accesstoken = null;
		$result = $ioi->query("select access_token from tokens where orcid = '".$orcid."' AND `deleted` IS NULL order by `created` DESC limit 1");


		if( !is_null($result)) {
		$resultlist = mysqli_fetch_row($result);
		$accesstoken = $resultlist[0];
		}


		return $accesstoken;


	}