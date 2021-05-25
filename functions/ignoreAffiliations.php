<?php
/*

**** This file is responsible for marking unselected affiliations as ignored.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localAffiliations : array contains all the affiliations associative with ORCID id.
	$accessToken :  unique token for each user from ORCID.
	$localPersonID : unique identifier for each person in the institution.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function ignoreAffiliations($orcid, $localAffiliations, $accessToken,  $localPersonID)
{
	global $ioi;
	
	// message to display to the user
	$message = '';

	foreach($localAffiliations as $localAffiliation)
	{		
		$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];
		
		// check if there is an existing entry in the userSelections table
		$existingEntryRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND `ignored` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');

		// if it's in the userSelections table already, leave it unchanged, a new entry will not be saved if the user resubmits the form
		if(empty($existingEntryRowID))
		{
			$update = $ioi->query("INSERT INTO `userSelections`(`orcid`, `type`, `localSourceRecordID`, `ignored`) VALUES ('$orcid','". $localAffiliation['type']."','$localSourceRecordID','".date("Y-m-d H:i:s")."')");				
		}

		// check if an entry was previously made in the ORCID record
		$existingPutCode =  getValues($ioi,"SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = '". $localAffiliation['type']."' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');

		if(!empty($existingPutCode))
		{	
			// delete the entry from the ORCID record 
			deleteFromORCID($orcid, $accessToken, $localAffiliation['type'], $existingPutCode);

			// mark the putCode as deleted in the putCodes table
			$query = "UPDATE `putCodes` SET deleted = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' ";
			$deleted = $ioi-> query($query);
		}
	}

	$message .= '<li style="font-color:Black;">'.count($localAffiliations).' '.INSTITUTION_ABBREVIATION.' affiliation(s) were ignored.</li>';
	
	return $message;
}