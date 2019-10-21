<?php
	/*


**** This file is responsible of getting all the works that match the user name.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	
	$localPersonID : unique identifier for each person in the institution.
	$displayname: user name from the session.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

	function getAllWorks($orcid, $localPersonID, $displayname)
	{		
		global $ioi;

		$works = array();

		$name = getName($localPersonID, $displayname);

		// If name found
		if(!empty($name))
		{
			$localWorkIDs = getValues($ioi, "SELECT DISTINCT idInSource,`added` FROM `metadata` WHERE `source` LIKE 'dspace' AND `field` LIKE 'dc.contributor.author' AND `value` LIKE '$name' AND deleted IS NULL ORDER BY `added` DESC", array('idInSource'), 'arrayOfValues');
		}

		//if no matching works
		if(empty($localWorkIDs))
		{
			echo '<br><h4><b>Publications</b></h4>';
			echo '<li>There are no existing work records in the '.INSTITUTION_ABBREVIATION.' repository which match your name, so no work records have been added to your ORCID record. When new records are added to the '.INSTITUTION_ABBREVIATION.' repository with your ORCID iD or your name, they will automatically be sent to your ORCID record.</li>';
		}
		else
		{
			foreach($localWorkIDs as $localWorkID)
			{				
				//format work 
				$work = prepWorkAsArray($localWorkID);

				// check if the entry has been ignored and not deleted 
				$ignored = getValues($ioi, "SELECT `ignored` FROM `ignored` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = 'repository_$localWorkID' AND deleted IS NULL", array('ignored'), 'singleValue');

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
		}

		return $works;
	}
