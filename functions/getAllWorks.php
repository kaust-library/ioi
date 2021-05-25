<?php
/*

**** This file is responsible for getting all the works that match the user name.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	
	$localPersonID : unique identifier for each person in the institution.
	$displayName: user name from the session.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function getAllWorks($orcid, $localPersonID, $displayName)
{		
	global $ioi;

	$works = array();

	$name = getName($localPersonID, $displayName);

	// If name found
	if(!empty($name))
	{
		$localWorkIDs = getValues($ioi, "SELECT DISTINCT idInSource,`added` FROM `metadata` WHERE `source` LIKE 'dspace' AND `field` LIKE 'dc.contributor.author' AND `value` LIKE '$name' AND deleted IS NULL ORDER BY `added` DESC", array('idInSource'), 'arrayOfValues');
	}
	
	foreach($localWorkIDs as $localWorkID)
	{				
		//format work 
		$work = prepWorkAsArray($localWorkID);

		// check if the entry has been ignored and not deleted 
		$ignored = getValues($ioi, "SELECT `ignored` FROM `userSelections` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = 'repository_$localWorkID' AND ignored IS NOT NULL AND deleted IS NULL", array('ignored'), 'singleValue');

		// that means it is currently unselected so make the flag = false
		if(!empty($ignored)) 
		{					
			$work['selected'] = FALSE; 
		}
		else
		{
			$work['selected'] = TRUE;
		}

		// push to the array 
		array_push($works, $work);
	}

	return $works;
}
