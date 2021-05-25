#!/usr/bin/php-cgi

<?php
/*

**** This file is responsible of synchronizing affiliations from the repository to ORCID.

** Parameters :
	No parameters required

** Created by : Daryl Grenz and Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 July 2019 - 8:30 PM

*/

//------------------------------------------------------------------------------------------------------------

//assume that application home directory is the parent directory
set_include_path(dirname(__DIR__).'/');

//include core configuration and common function files
include_once 'include.php';

$recordTypeCounts = array('all'=>0,'new'=>0,'updated'=>0,'skipped - no permission token'=>0,'skipped - no create or update scope'=>0, 'skipped - ignored affiliation'=>0, 'skipped - failed to create affiliation' =>0, 'skipped - failed to update' =>0, 'skipped - no role-title' => 0);

$taskName = 'syncAffiliationsToORCID';

// init report variable
$report = '';
$report .= 'From Date: '.date("Y-m-d H:i:s").PHP_EOL;
$errors = array();
$currentyear = date("Y");
$query = '';

if(isset($_GET['orcid']))
{	
	$query = "WHERE orcid = '".$_GET['orcid']."'";
}

//get all the orcids
$orcids = getValues($ioi, "SELECT `orcid` FROM orcids ".$query, array('orcid'), 'arrayOfValues');

//for each orcid
foreach($orcids as $orcid)
{
	//get local person id
	$localPersonID = getValues($ioi, "SELECT `localPersonID` FROM `orcids` where `orcid` = '".$orcid."'", array('localPersonID'), 'singleValue');

	if(empty($localPersonID))
	{
		continue;
	}	

	//check if the user gave us permissions that are still valid
	$permission = getValues($ioi, "SELECT `scope`, `access_token` FROM `tokens` WHERE `expiration` >= '".TODAY."' AND `orcid` = '$orcid' AND deleted IS NULL ORDER BY `created` DESC LIMIT 1", array('scope', 'access_token'), 'arrayOfValues');

	if(!empty($permission))
	{
		// Check for the proper scope
		// /activities/update is now used, in the past affiliations/update always came with /affiliations/create
		if(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/affiliations/create') !== FALSE  )
		{		
			$report .= '- '.$orcid.PHP_EOL;

			//get all the affiliations for this orcid ordered from the oldest to the newest
			$newAffiliation = getValues($ioi, "SELECT value, rowID  FROM `metadata`  WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND deleted IS NULL AND `added` LIKE '".$currentyear."%' ORDER BY `metadata`.`added` ASC",  array('value', 'rowID'), 'arrayOfValues');
			
			$updatedAffiliation = getValues($ioi, "SELECT value, rowID FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND rowID IN ( SELECT `parentRowID` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.date.end' AND `added` LIKE '".$currentyear."%' ORDER BY `metadata`.`added` ASC ) AND deleted IS NULL",  array('value', 'rowID'), 'arrayOfValues');
			
			$affiliations = array_merge($newAffiliation, $updatedAffiliation);
			$recordTypeCounts['all'] += count($affiliations);				
		
			//for new each affiliation
			foreach($affiliations as $affiliation)
			{				
				// set the new affilation id
				$personOrgRelation =  $affiliation['value'];
				$rowID =  $affiliation['rowID'];
				$localSourceRecordID = 'local_person_'.$localPersonID.'_'.$personOrgRelation;
				
				// save report
				$report .= PHP_EOL.$personOrgRelation.PHP_EOL;				
				
				//check if the affiliation exists in the database ( as new id )
				$putcode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `localSourceRecordID` = '$localSourceRecordID' AND `deleted` IS NULL", array('putCode'), 'singleValue');

				//check if the affiliation should be ignored
				$ignored = getValues($ioi, "SELECT `localSourceRecordID` FROM `ignored` WHERE `localSourceRecordID` = '$localSourceRecordID' AND ignored IS NOT NULL AND `deleted` IS NULL", array('localSourceRecordID'), 'singleValue');

				if(!empty($ignored))
				{
					//what about ignored affiliations already posted to ORCID?
					$recordTypeCounts['skipped - ignored affiliation']++;
					if(!empty($putcode))
					{
						$report .= '-- Ignored affiliation putCode: '.$putcode.PHP_EOL;
					}
				}
				else
				{
					//format affiliation
					$localAffiliation = prepAffiliationAsArray($localPersonID, $rowID);
				
					//before creating xml remove the unnecessary fields using unset
					if(isset($localAffiliation['fields']['personOrgRelation']))
					{
						unset($localAffiliation['fields']['personOrgRelation']);
					}

					unset($localAffiliation['fields']['localSourceRecordID']);

					if(!empty($putcode))
					{
						//convert the affiliation to XML
						$xml = prepAffiliationXML($localAffiliation, $orcid, $putcode);
						
						//update the affiliation
						$response = updateSingleItem($orcid, $permission[0]['access_token'], $localAffiliation['type'], $putcode, $xml);					
						
						//update the record in the system
						if(is_string($response))
						{
							$recordType = saveRecord($orcid, $localAffiliation['type'], $putcode, $localSourceRecordID, $xml, 'XML', $response);

							//increase the count
							$recordTypeCounts['updated']++;
							$report .= '-- Updated putCode: '.$putcode.PHP_EOL;
						}
						else
						{
							$recordTypeCounts['skipped - failed to update']++;
							$report .= '-- failed to update putCode : '.$putcode.PHP_EOL;
						}
					}
					else
					{
						// check if the entry has a role-title
						if(!empty($localAffiliation['fields']['role-title']))
						{
							//convert the affiliation to XML
							$xml = prepAffiliationXML($localAffiliation);
						
							$response = postToORCID($orcid,$permission[0]['access_token'], $localAffiliation['type'], $xml);
							$report .= '-- XML: '.$xml.PHP_EOL;
								
							//failure returns array 
							if(is_string($response))
							{
								//echo $response;	
								$putcode = extractPutCode($response);
								$recordType = saveRecord($orcid, $localAffiliation['type'], $putcode, $localSourceRecordID, $xml, 'XML', $response);
								$recordTypeCounts['new']++;
								$report .= '-- New putCode: '.$putcode.PHP_EOL;
							}//end of response
							else
							{
								$recordTypeCounts['skipped - failed to create affiliation']++;
								$report .= print_r($response, TRUE);
							}							
						}
						else
						{							
							$recordTypeCounts['skipped - no role-title']++;				
						}
					}
				}// end of update new id
			} //end of for each $newaffiliation
		} // end of if proper scope statement
		else
		{
			//improper scope
			$recordTypeCounts['skipped - no create or update scope']++;
			$report .= '-- skipped - no create or update scope';
		}
	}
	// if the permission is empty
	else
	{
		//the user hasn't granted permission
		$recordTypeCounts['skipped - no permission token']++;
		$report .= '-- skipped - no permission token';
	}
} //end of foreach $orcids

$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);
echo $summary;
