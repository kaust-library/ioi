<?php
/*

**** This funcation is responsible of sending works to ORCID, and updating the ignored table if the work was unselected by the user 

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$works : array contains all the work handles associated with the ORCID.
	$accessToken :  unique token for each user from ORCID.
	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 April 2019- 11:30 AM 

*/


//------------------------------------------------------------------------------------------------------------

function addWorks($orcid, $works, $accessToken)
{
	global $ioi;

	// message to display to the user
	$message = '';
	
	// report and errors to save in the messages table for troubleshooting
	$recordTypeCounts = array('all'=>0, 'selected'=>0, 'previouslySelected'=>0, 'addedToORCID'=>0, 'previouslyAddedToORCID'=>0, 'errorAddingToORCID'=>0);
	$report = 'ORCID: '.$orcid.PHP_EOL;
	$errors = array();

	$newPutCodes = array();
	$existingPutCodes = array();

	foreach($works as $work)
	{
		$recordTypeCounts['all']++;
		
		$localSourceRecordID = "repository_".$work['idInSource'];
		
		$report .= '- '.$localSourceRecordID.PHP_EOL;
		
		// if the work was ignored, but is now selected ( make deleted = the current date on ignored entry)
		$ignoredRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND`localSourceRecordID` = '$localSourceRecordID' AND `ignored` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');
		
		if(!empty($ignoredRowID))
		{
			$update = $ioi->query("UPDATE `userSelections` SET `deleted` = '".date("Y-m-d H:i:s")."' WHERE `rowID` = '$ignoredRowID'");
		}
		
		// if the work was previously selected, leave it unchanged, otherwise make a new entry
		$selectedRowID = getValues($ioi, "SELECT `rowID` FROM `userSelections` WHERE `orcid` = '$orcid' AND`localSourceRecordID` = '$localSourceRecordID' AND `selected` IS NOT NULL AND `deleted` IS NULL", array('rowID'), 'singleValue');
		
		if(empty($selectedRowID))
		{
			$recordTypeCounts['selected']++;
			
			$update = $ioi->query("INSERT INTO `userSelections`(`orcid`, `type`, `localSourceRecordID`, `selected`) VALUES ('$orcid','work','$localSourceRecordID','".date("Y-m-d H:i:s")."')");
		}
		else
		{
			$recordTypeCounts['previouslySelected']++;
		}
		
		if(ORCID_MEMBER)
		{
			// check if the work was already sent to ORCID
			$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');
			
			if(!empty($existingPutCode))
			{
				$recordTypeCounts['previouslyAddedToORCID']++;
				
				$report .= '-- Existing putcode: '.$existingPutCode.PHP_EOL;
			}
			else
			{
				// before creating xml remove the selected key
				unset($work['selected']);

				$xml = prepWorkXML($work);

				$response = postToORCID($orcid, $accessToken, 'work', $xml);

				//failure returns array
				if(is_string($response))
				{
					$recordTypeCounts['addedToORCID']++;
					
					$putCode = extractPutCode($response);
					
					$report .= '-- New putcode: '.$putCode.PHP_EOL;
					
					$recordType = saveRecord($orcid, 'work', $putCode, $localSourceRecordID, $xml, 'XML', $response);
				}
				else
				{
					$recordTypeCounts['errorAddingToORCID']++;
					
					$report .= '-- Error posting work to ORCID'.PHP_EOL;
						
					$errors[] = array('type'=>'postToORCID','message'=>' - error response from ORCID API: '.print_r($response, TRUE));
				}
			}
		}
	}
	
	$summary = saveReport('addWorks', $report, $recordTypeCounts, $errors);

	$message .= '<h4><b>Publications</b></h4>';
	
	if($recordTypeCounts['previouslySelected'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['previouslySelected'].' works in the '.INSTITUTION_ABBREVIATION.' repository were previously selected and were left unchanged.</li>';
	}
	
	if($recordTypeCounts['selected'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['selected'].' works in the '.INSTITUTION_ABBREVIATION.' repository were selected and will have their records updated to link to your ORCID record.*</li>';
	}
	
	if($recordTypeCounts['previouslyAddedToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['previouslyAddedToORCID'].' records for works in the '.INSTITUTION_ABBREVIATION.' repository already exist in your ORCID record and were not changed.</li>';
	}
	
	if($recordTypeCounts['addedToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['addedToORCID'].' records for works in the '.INSTITUTION_ABBREVIATION.' repository were successfully added to your ORCID record.</li>';		
	}
	
	if($recordTypeCounts['errorAddingToORCID'] !== 0)
	{
		$message .= '<li style="font-color:Black;">'.$recordTypeCounts['errorAddingToORCID'].' records for works in the '.INSTITUTION_ABBREVIATION.' repository failed to send to your ORCID record. Please email <a href="'.IR_EMAIL.'">'.IR_EMAIL.'</a> for assistance.</li>';
	}
	
	return $message;
}