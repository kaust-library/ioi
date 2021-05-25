#!/usr/bin/php-cgi

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
set_include_path(dirname(__DIR__).'/');

//include core configuration and common function files
include_once 'include.php';

// counts for reporting
$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'ignored - user deselected'=>0,'skipped - no permission token'=>0,'skipped - no create scope'=>0,'skipped - no update scope'=>0,'skipped - previously submitted XML was identical'=>0,'skipped - failed to update'=>0,'skipped - failed to create work'=>0);

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
	AND (
		idInSource IN (
		SELECT DISTINCT idInSource FROM `metadata` WHERE `source` LIKE 'dspace'
			AND field IN (
				SELECT `sourceField` FROM `mappings` WHERE `source` = 'dspace' AND `entryType` = 'work')
				AND `deleted` IS NULL
				AND `added` > '$fromDate'
			)
		OR
		idInSource IN (
		SELECT DISTINCT idInSource FROM `metadata` WHERE `source` LIKE 'dspace'
			AND field IN ('dc.contributor.author','dc.identifier.orcid')
				AND `deleted` IS NULL
				AND `added` > '$fromDate'
			)
		)
	AND `value` IN (SELECT `orcid` FROM `orcids`)
	ORDER BY `idInSource` ASC");

$items = mysqli_fetch_all($result, MYSQLI_ASSOC );

// if items were returned
if(!empty($items))
{
	$recordTypeCounts['all'] = count($items);

	// for each row
	foreach($items as $item)
	{
		$response = '';

		// assign orcid
		$orcid = $item['value'];

		if(is_null($orcid)){
			continue;
		}

		// assign handle
		$localWorkID = $item['idInSource'];
		$report .= PHP_EOL.$localWorkID.PHP_EOL;
		$report .= '- '.$orcid.PHP_EOL;

		//check if the user gave us permissions
		$permission = getValues($ioi, "SELECT `scope`, `access_token` FROM `tokens` WHERE `expiration` >= '".TODAY."' AND `orcid` = '$orcid' ORDER by `created` DESC LIMIT 1", array('scope', 'access_token'), 'arrayOfValues');

		// if they gave us permissions
		if(!empty($permission))
		{
			//check if the work was ignored
			$ignored = getValues($ioi, "SELECT `localSourceRecordID` FROM `userSelections` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = 'repository_$localWorkID' AND ignored IS NOT NULL AND `deleted` IS  NULL", array('localSourceRecordID'), 'singleValue');

			if(empty($ignored))
			{
				// Check for the proper scope
				if(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/orcid-works/create') !== FALSE)
				{
					//Get the work metadata
					$work = prepWorkAsArray($localWorkID);

					// check if there is already a putcode for this handle for this user in the database
					$putCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = 'repository_$localWorkID' AND `deleted` IS NULL", array('putCode'), 'singleValue');

					// If no existing putcode, it should be a new entry
					if(empty($putCode) )
					{
						//send it to ORCID
						$xml = prepWorkXML($work);

						$response = postToORCID($orcid, $permission[0]['access_token'], 'work', $xml);

						//failure returns array
						if(is_string($response))
						{
							$putCode = extractPutCode($response);

							$recordType = saveRecord($orcid, 'work', $putCode, 'repository_'.$localWorkID, $xml, 'XML', $response);

							$report .= '-- New putCode: '.$putCode.PHP_EOL;

							//Set putCode to empty so that we don't update the item that we just posted
							$putCode = '';
						} // end of if statement
						else
						{
							if(strpos($response['response'], 'You have already added this activity')!==FALSE&&preg_match('/put-code \d{8}/', $response['response'], $matches))
							{
								$putCode = explode(' ', $matches[0])[1];

								saveRecord($orcid, 'work', $putCode, 'repository_'.$localWorkID, $xml, 'XML', $response['response']);

								$report .= '-- Record saved with conflict putCode: '.$putCode.PHP_EOL;
							}
							else
							{
								$recordType = 'skipped - failed to create work';
							}
						}
					} // end of if statement

					//Check again in case putCode was retrieved from conflict response
					if(!empty($putCode))
					{
						$report .= '-- Existing putCode: '.$putCode.PHP_EOL;

						//By explicitly checking for the /orcid-works/update scope we can skip items that have only the /orcid-works/create scope
						if(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/orcid-works/update') !== FALSE)
						{
							//convert the work to xml
							$xml = prepWorkXML($work, $orcid, $putCode);

							// check if the previously submitted data is identical
							$submittedData = getValues($ioi, "SELECT `submittedData` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `putCode` = '$putCode' AND `localSourceRecordID` = 'repository_$localWorkID' AND `deleted` IS NULL", array('submittedData'), 'singleValue');

							$toCompare1 = preg_replace('/<work:work(.*)">/', '', $xml);
							$toCompare2 = preg_replace('/<work:work(.*)">/', '', $submittedData);

							//Compare strings after removal of the header (which may be different even though the record content is the same)
							if($toCompare1 !== $toCompare2)
							{
								//update the orcid record
								$response = updateSingleItem($orcid, $permission[0]['access_token'], 'work', $putCode, $xml);

								//failure returns array
								if(is_string($response))
								{
									$recordType = saveRecord($orcid, 'work', $putCode, 'repository_'.$localWorkID, $xml, 'XML', $response);
								}
								else
								{
									$recordType = 'skipped - failed to update';
								}
							}
							else
							{
								$recordType = 'skipped - previously submitted XML was identical';
							}
						}
						else
						{
							$recordType = 'skipped - no update scope';
						} // end of else statement
					}
				}
				else
				{
					$recordType = 'skipped - no create scope';
				}
			}
			else
			{
				$recordType = 'ignored - user deselected';
			}
		}
		else
		{
			$recordType = 'skipped - no permission token';
		}
		$recordTypeCounts[$recordType]++;
		$report .= '-- '.$recordType.PHP_EOL;

		if(is_array($response))
		{
			$report .= '-- Error response: '.print_r($response, TRUE).PHP_EOL;
		}
	} // end of foreach

	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

} // end of if statement
