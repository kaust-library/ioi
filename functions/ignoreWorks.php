<?php
/*

**** This file is responsible for marking unselected works as ignored.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localWorkIDs : array contains all the works associative with ORCID id.
	$accessToken :  unique token for each user from ORCID.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//---------------------------------------------------------------------------------------------------------
function ignoreWorks($orcid, $localWorkIDs, $accessToken)
{
	global $ioi;
	
	// message to display to the user
	$message = '';

	foreach($localWorkIDs as $localWorkID)
	{
		// if the unselected record is in ORCID 
		$localSourceRecordID = "repository_".$localWorkID;
		
		// check if there is an existing entry in the userSelections table
		$existingEntryRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND `ignored` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');

		// if it's in the userSelections table already, leave it unchanged, a new entry will not be saved if the user resubmits the form
		if(empty($existingEntryRowID))
		{
			$update = $ioi->query("INSERT INTO `userSelections`(`orcid`, `type`, `localSourceRecordID`, `ignored`) VALUES ('$orcid','work','$localSourceRecordID','".date("Y-m-d H:i:s")."')");				
		}
	
		if(ORCID_MEMBER)
		{
			$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');	

			if(!empty($existingPutCode))
			{			
				// delete the entry from the ORCID record
				deleteFromORCID($orcid, $accessToken, 'work', $existingPutCode);

				// mark the putCode as deleted in the putCodes table 
				$deleted = $ioi-> query("UPDATE `putCodes` SET deleted = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND `putCode` ='$existingPutCode' AND `deleted` IS NULL");
			}
		}
	}

	$message .= '<li style="font-color:Black;">'.count($localWorkIDs).' records for works in the '.INSTITUTION_ABBREVIATION.' repository were ignored.</li>';
	
	return $message;
}