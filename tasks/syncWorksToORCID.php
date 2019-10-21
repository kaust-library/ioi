<?php
/*

**** This file is responsible of synchronizing works from the repository to ORCID.

** Parameters :
	No parameters required

** Created by : Daryl Grenz and Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM

*/

//------------------------------------------------------------------------------------------------------------

//assume that application home directory is the parent directory
set_include_path('../');

//include core configuration and common function files
include_once 'include.php';

// counts for reporting
$recordTypeCounts = array('all'=>0,'new'=>0,'updated'=>0,'deleted'=>0,'skipped - no permission token'=>0,'skipped - no create scope'=>0,'skipped - no update scope'=>0,'skipped - failed to update'=>0,'skipped - failed to create work'=>0);

$taskName = 'syncWorksToORCID';

// init report variable
$report = '';
$errors = array();

if(!isset($_GET['fromDate']))
{
	// select the timestamp of the last sync message from messages table
	$fromDate = getValues($ioi, "SELECT timestamp FROM messages WHERE `process` = '$taskName' ORDER BY `timestamp` DESC LIMIT 1", array('timestamp'), 'singleValue');
}
else
{
	// set fromDate via GET variable
	$fromDate = $_GET['fromDate'];
}
$report .= 'From Date: '.$fromDate.PHP_EOL;

//get all orcids and handles for updated and new items
$result = $ioi->query("SELECT `value`, `idInSource` from `metadata` WHERE `source` LIKE 'dspace'
	AND `field` = 'dc.identifier.orcid'
	AND deleted IS NULL

	AND `parentRowID` IN (
		SELECT `rowID` FROM `metadata` WHERE `source` LIKE 'dspace'
			AND `field` = 'dspace.authority.key'
			AND deleted IS NULL
			AND `parentRowID` IN (
				SELECT `rowID` FROM `metadata` WHERE `source` LIKE 'dspace'
					AND `field` = 'dc.contributor.author'
					AND deleted IS NULL
				)
		)

	AND idInSource in (
		SELECT DISTINCT idInSource FROM `metadata` WHERE `source` LIKE 'dspace'
			AND field IN (SELECT `sourceField` FROM `mappings` WHERE `source` = 'dspace' AND `entryType` = 'work')
			AND `deleted` IS NULL
			AND `added` > '$fromDate' )

	AND `value` IN (SELECT `orcid` FROM `orcids`)

	ORDER BY `idInSource` ASC");

$items = mysqli_fetch_all($result, MYSQLI_ASSOC );

// if items were returned
if(!empty($items))
{
	$recordTypeCounts['all'] = count($items);

	// for each row
	foreach($items as $item){

		// assign orcid
		$orcid = $item['value'];

		if(is_null($orcid)){
			continue;
		}

		// assign handle
		$localWorkID = $item['idInSource'];

		//check if the user gave us permissions
		$permission = getValues($ioi, "SELECT `scope`, `access_token` FROM `tokens` WHERE `expiration` >= '".TODAY."' AND `orcid` = '$orcid' ORDER by `created` DESC LIMIT 1", array('scope', 'access_token'), 'arrayOfValues');

		// check if there is a putcode for this handle for this user in the database
		$putcode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = 'repository_$localWorkID' AND `deleted` IS NULL", array('putCode'), 'singleValue');

		//check if the work not in ignore table
		$ignored = getValues($ioi, "SELECT `localSourceRecordID` FROM `ignored` where `orcid` = '$orcid' and `deleted` IS  NULL", array('localSourceRecordID'), 'singleValue');

		// if they gave us permissions
		if(!empty($permission) && empty($ignored)){

			// Check for the proper scope
			if(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/orcid-works/create') !== FALSE){

				//Get the work metadata
				$work = prepWorkAsArray($localWorkID);

				// Check that there is no existing putcode
				if(empty($putcode) ) {

					//send it to ORCID
					$xml = prepWorkXML($work);

					$response = postToORCID($orcid, $permission[0]['access_token'], 'work', $xml);

					//failure returns array
					if(is_string($response)){
						$location = str_replace('||','',explode('/', explode('Location: ', $response)[1]));
						$putCode = trim($location[count($location)-1]);
						$recordType = saveRecord($orcid, 'work', $putCode, 'repository_'.$localWorkID, $xml, 'XML', $response);
						//increase the count
						$recordTypeCounts['new']++;

					} // end of if statement
					else{
						$recordTypeCounts['skipped - failed to create work']++;
						$report .= print_r($response, TRUE);
					}
				} // end of if statement

				//By explicitly checking for the /orcid-works/update scope we can skip items that have only the /orcid-works/create scope
				elseif(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/orcid-works/update') !== FALSE){

					//convert the work to xml
					$xml = prepWorkXML($work, $orcid, $putcode);

					//update the orcid record
					$response = updateSingleItem($orcid, $permission[0]['access_token'], 'work', $putcode, $xml);

					//failure returns array
					if(is_string($response)){

						$recordType = saveRecord($orcid, 'work', $putcode, 'repository_'.$localWorkID, $xml, 'XML', $response);

						//increase the count
						$recordTypeCounts['updated']++;
					}
					else{
						$recordTypeCounts['skipped - failed to update']++;
						$report .= print_r($response, TRUE);
					}
				}
				else{
					$recordTypeCounts['skipped - no update scope']++;
				} // end of else statement
			}
			else{
				$recordTypeCounts['skipped - no create scope']++;
			}
		}
		else {
			$recordTypeCounts['skipped - no permission token']++;
		}
	} // end of foreach

	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

	echo $summary;
} // end of if statement
