<?php
/*

**** This file is responsible for getting all the affiliations that match the local person ID.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	
	$localPersonID : unique identifier for each person in the institution.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function getAllAffiliations($orcid, $localPersonID)
{		
	global $ioi;
	
	$affiliations = array();

	$localAffiliation = array();

	// get all the affiliations that match the user name from the database
	// exclude orgRelation entries that lack a job title
	$localPersonOrgRelationRowIDs = getValues($ioi, "SELECT `rowID` FROM `metadata` m WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND deleted IS NULL AND EXISTS (SELECT * FROM `metadata` WHERE `parentRowID` = m.`rowID` AND `field` = 'local.person.title' AND deleted IS NULL ORDER BY `metadata`.`added` DESC)", array('rowID'), 'arrayOfValues');

	foreach($localPersonOrgRelationRowIDs as $localPersonOrgRelationRowID)
	{
		//format affiliation 
		$localAffiliation = prepAffiliationAsArray($localPersonID, $localPersonOrgRelationRowID);
		
		$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];

		// check if the entry has been ignored and not deleted
		$ignored = getValues($ioi, "SELECT `ignored` FROM `userSelections` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND ignored IS NOT NULL AND deleted IS NULL", array('ignored'), 'singleValue');

		// that means it is currently unselected so make the flag = false
		if(!empty($ignored)) 
		{					
			$localAffiliation['selected'] = FALSE; 
		}
		else
		{
			$localAffiliation['selected'] = TRUE;
		}
		$affiliations[$localSourceRecordID] = $localAffiliation;
	}		
	
	return $affiliations;
}
