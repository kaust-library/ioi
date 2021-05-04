<?php	
/*

**** This file is responsible for saving a record of selections made and entries transmitted to ORCID.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$type : (work, employment, education).
	$putCode : unique number for each (work, affiliation) in ORCID.
	$localSourceRecordID : the handle for works and the localOrgRelation ID for affiliations.
	$submittedData : item data.
	$format : JSON or XML
	$apiResponse : the response of the API.

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

function saveRecord($orcid, $type, $putCode, $localSourceRecordID, $submittedData, $format, $apiResponse)
{			
	global $ioi, $message, $errors;
	
	//check for existing entry
	$check = select($ioi, "SELECT rowID, localSourceRecordID, submittedData, apiResponse FROM putCodes WHERE orcid LIKE ? AND type LIKE ? AND putCode LIKE ? AND deleted IS NULL", array($orcid, $type, $putCode));
	
	//if not existing
	if(mysqli_num_rows($check) === 0)
	{								
		$recordType = 'new';
		
		if(!insert($ioi, 'putCodes', array('orcid', 'type', 'putCode', 'localSourceRecordID', 'submittedData', 'format', 'apiResponse'), array($orcid, $type, $putCode, $localSourceRecordID, $submittedData, $format, $apiResponse)))
		{
			$error = end($errors);
			$message .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
		}
	}		
	else
	{
		$row = $check->fetch_assoc();
		$existingRowID = $row['rowID'];
		
		$recordType = 'modified';
		
		if(!insert($ioi, 'putCodes', array('orcid', 'type', 'putCode', 'localSourceRecordID', 'submittedData', 'format', 'apiResponse'), array($orcid, $type, $putCode, $localSourceRecordID, $submittedData, $format, $apiResponse)))
		{
			$error = end($errors);
			$message .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
		}
		$newRowID = $ioi->insert_id;

		if(!update($ioi, 'putCodes', array("deleted", "replacedByRowID"), array(date("Y-m-d H:i:s"), $newRowID, $existingRowID), 'rowID'))
		{
			$error = end($errors);
			$message .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
		}
	}
	
	return $recordType;
}	
