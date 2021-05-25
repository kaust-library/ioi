<?php 


/*


**** This file is responsible of getting the authority key form th DB and send it to Dspace API.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	
	$name : String value.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 17 September 2019 - 2:09 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function getAuthorityKey($orcid, $name) {

	# database connection
	global $ioi;

	# set the message
	$message = '';

	# if the orcid is set
	if(isset($orcid)) 
	{
		# get the matching authority key
		$authorityKey = getValues($ioi, "SELECT DISTINCT authorityKey.value as value 
		FROM `metadata` orcid LEFT JOIN metadata authorityKey ON authorityKey.rowID = orcid.parentRowID 
		WHERE orcid.field = 'dc.identifier.orcid' 
		AND authorityKey.field = 'dspace.authority.key' 
		AND orcid.value ='$orcid'
		AND orcid.deleted IS NULL
		AND authorityKey.deleted IS NULL", array('value'), 'singleValue');

		if(!empty($authorityKey))
		{
			# login to DSpace to get the token 
			$dSpaceAuthHeader = loginToDSpaceRESTAPI();

			# send the new name with the authority key to DSpace
			$response = updateNameInDspace($authorityKey, $name, $dSpaceAuthHeader);

			if(is_string($response) )
			{
				$message = 'success';
			}
			else
			{
				$message = 'cannotUpdate';
			} 
		} 
		else 
		{
			$message = 'noAuthorityKey';
		}

		return $message;
	}
}