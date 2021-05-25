<?php

/*

**** This file defines the function responsible for sending selected affiliations to ORCID and saving the records in the local database.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localAffiliations : array contains all the affiliations associative with ORCID id.
	$localPersonID : unique identifier for each person in the institution.
	$accessToken :  unique token for each user from ORCID.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function addAffiliations($orcid, $localAffiliations, $localPersonID, $accessToken)
{
	global $ioi;
	
	// message to display to the user
	$message = '';
	
	// report and errors to save in the messages table for troubleshooting
	$recordTypeCounts = array('all'=>0, 'selected'=>0, 'previouslySelected'=>0, 'addedToORCID'=>0, 'previouslyAddedToORCID'=>0, 'errorAddingToORCID'=>0);
	$report = 'ORCID: '.$orcid.PHP_EOL;
	$errors = array();
	
	$putCodes = array();
	$existingPutCodes = array();
	
	foreach($localAffiliations as $localAffiliation)
	{
		$recordTypeCounts['all']++;
				
		$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];
		
		$report .= '- '.$localSourceRecordID.PHP_EOL;
		
		// check if the affiliation already has an ORCID putcode
		$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = '".$localAffiliation['type']."' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');
		
		if(!empty($existingPutCode))
		{			
			$recordTypeCounts['previouslyAddedToORCID']++;
			
			$report .= '-- Existing putcode: '.$existingPutCode.PHP_EOL;
		}
		else
		{
			// if the affiliation was previously marked as ignored, we will mark the ignored entry as deleted
			$ignoredRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND`localSourceRecordID` = '$localSourceRecordID' AND `ignored` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');
		
			if(!empty($ignoredRowID))
			{
				$update = $ioi->query("UPDATE `userSelections` SET `deleted` = '".date("Y-m-d H:i:s")."' WHERE `rowID` = '$ignoredRowID'");
			}
			
			// if the affiliation was previously selected, leave it unchanged, otherwise make a new entry
			$selectedRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND`localSourceRecordID` = '$localSourceRecordID' AND `selected` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');
			
			if(empty($selectedRowID))
			{
				$recordTypeCounts['selected']++;
				
				$update = $ioi->query("INSERT INTO `userSelections`(`orcid`, `type`, `localSourceRecordID`, `selected`) VALUES ('$orcid','".$localAffiliation['type']."','$localSourceRecordID','".date("Y-m-d H:i:s")."')");
			}
			else
			{
				$recordTypeCounts['previouslySelected']++;
			}

			// before creating xml remove the unnecessary fields
			if(isset($localAffiliation['fields']['personOrgRelation']))
			{
				unset($localAffiliation['fields']['personOrgRelation']);
			}

			unset($localAffiliation['fields']['localSourceRecordID']);
			unset($localAffiliation['selected']);

			$xml = prepAffiliationXML($localAffiliation);
			$response = postToORCID($orcid, $accessToken, $localAffiliation['type'], $xml);
		
			//print_r($response);
		
			//failure returns array
			if(is_string($response))
			{
				$recordTypeCounts['addedToORCID']++;
				
				$putCode = extractPutCode($response);
				
				$report .= '-- New putcode: '.$putCode.PHP_EOL;
				
				$recordType = saveRecord($orcid, $localAffiliation['type'], $putCode, $localSourceRecordID, $xml, 'XML', $response);
			}
			else
			{
				$recordTypeCounts['skipped']++;
				
				$report .= '-- Error posting affiliation to ORCID'.PHP_EOL;
					
				$errors[] = array('type'=>'postToORCID','message'=>' - error response from ORCID API: '.print_r($response, TRUE));
			}
		}
	}
	
	$summary = saveReport('addAffiliations', $report, $recordTypeCounts, $errors);

	$message .= '<hr><h4><b>Affiliation</b></h4>';

	if($recordTypeCounts['previouslyAddedToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['previouslyAddedToORCID'].' '.INSTITUTION_ABBREVIATION.' affiliation(s) already exist in your ORCID record and were not changed.</li>';
	}
	
	if($recordTypeCounts['addedToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['addedToORCID'].' '.INSTITUTION_ABBREVIATION.' affiliation(s) were successfully added to your ORCID record.</li>';		
	}
	
	if($recordTypeCounts['errorAddingToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['errorAddingToORCID'].' '.INSTITUTION_ABBREVIATION.' affiliation(s) failed to send to your ORCID record. Please email <a href="'.IR_EMAIL.'">'.IR_EMAIL.'</a> for assistance.</li>';
	}	
	
	return $message;
}