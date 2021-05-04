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
set_include_path('../');

//include core configuration and common function files
include_once 'include.php';

$recordTypeCounts = array('all'=>0,'new'=>0,'updated'=>0,'skipped - no permission token'=>0,'skipped - no create or update scope'=>0, 'skipped - Ignored affiliation'=>0, 'skipped - failed to create affiliation' =>0, 'skipped - failed to update' =>0);

$taskName = 'syncAffiliationsToORCID';

// init report variable
$report = '';
$errors = array();

//Set fromDate as GET variable
if(!isset($_GET['fromDate']))
{
	// select the last sync message from messages table
	$fromDate = getValues($ioi, "SELECT timestamp FROM messages m WHERE m.`process` = '$taskName' ORDER BY m.`timestamp` DESC LIMIT 1", array('timestamp'), 'singleValue');
}
else
{
	//Manually set fromDate as GET variable
	$fromDate = $_GET['fromDate'];
}

//get all the orcid
$orcids = getValues($ioi, "SELECT `orcid` FROM orcids", array('orcid'), 'arrayOfValues');

//for each orcid
foreach($orcids as $orcid) {

	//get local person id
	$localPersonID = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = CONCAT('person_', (SELECT `value` where `idInSource` = (SELECT `idInSource` FROM `metadata` WHERE  (`field` = 'dc.identifier.orcid' or `field` = 'local.person.orcid' ) AND `value` = '$orcid' limit 1)))", array('value'), 'singleValue');

	if( is_null($localPersonID)){

		continue;
	}
	//check if the user gave us permissions that are still valid
	$permission = getValues($ioi, "SELECT `scope`, `access_token` FROM `tokens` WHERE `expiration` >= '".TODAY."' AND `orcid` = '$orcid' ORDER by `created` DESC LIMIT 1", array('scope', 'access_token'), 'arrayOfValues');

	if(!empty($permission)) {

		// Check for the proper scope
		// always affiliations/update came with /affiliations/create
		if(strpos($permission[0]['scope'], '/activities/update') !== FALSE || strpos($permission[0]['scope'], '/affiliations/create') !== FALSE  )
		{

			//get all the affiliation for this orcid ordered from the oldest to the newest
			$newaffiliation = getValues($ioi, "SELECT value, rowID  FROM `metadata`  WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND deleted IS NULL AND `added` >= '".TODAY."' ORDER BY `metadata`.`added` ASC",  array('value', 'rowID'), 'arrayOfValues');

			$recordTypeCounts['all'] += count($newaffiliation);

			//for new each affiliation
			foreach($newaffiliation as $newitem) {

				// set the new affilation id
				$personOrgRelation =  $newitem['value'];
				$rowID =  $newitem['rowID'];
				$localSourceRecordID = 'local_person_'.$localPersonID.'_'.$personOrgRelation;

				//check if the affiliation exists in the database ( as new id )
				$putcode = getValues($ioi, "SELECT `putCode` FROM `putCodes` where `localSourceRecordID` = '$localSourceRecordID' and `deleted` IS NULL limit 1", array('putCode'), 'singleValue');

				//check if the affiliation not ignored
				$ignored = getValues($ioi, "SELECT `localSourceRecordID` FROM `ignored` where `localSourceRecordID` = '$localSourceRecordID' and `deleted` IS NULL limit 1", array('localSourceRecordID'), 'singleValue');

				//if the affiliation is exists and not ignore, and the user gave us the permission to update
				if(!is_null($putcode) && is_null($ignored) && (strpos($permission[0]['scope'], '/affiliations/update') !== FALSE || strpos($permission[0]['scope'], '/activities/update') !== FALSE)) {

					//format affiliation
					$localAffiliation = prepAffiliationAsArray($localPersonID, $rowID);

					// before creating xml remove the unnecessary field using unset
					if(isset($localAffiliation[$rowID]['fields']['personOrgRelation']))
						unset($localAffiliation[$rowID]['fields']['personOrgRelation']);

					unset($localAffiliation[$rowID]['fields']['localSourceRecordID']);

					//convert the affiliation to XML
					$xml = prepAffiliationXML($localAffiliation[$rowID], $orcid, $putcode);
					//update the affiliation
					$response = updateSingleItem($orcid, $permission[0]['access_token'], $localAffiliation[$rowID]['type'], $putcode, $xml);

					//update the record in the system
					if(is_string($response)){

						$recordType = saveRecord($orcid, $localAffiliation[$rowID]['type'], $putcode, $localSourceRecordID, $xml, 'XML', $response);

						//increase the count
						$recordTypeCounts['updated']++;

					}else{

						$recordTypeCounts['skipped - failed to update']++;
						$report .= print_r($response, True);
					}
				}// end of update new id

				//else check if there is an old id affilation ( local_person_$localPersonID or ldap_$email )
				elseif( is_null($putcode) && is_null($ignored) ) {

					// get the local email to check if the person have id with ldap
					$email = getValues($ioi, "SELECT value FROM `metadata` WHERE `field` = 'local.person.email' and `idInSource` = 'person_$localPersonID'", array('value'), 'singleValue' );

					//check if the affiliation exists in the database ( old id ) ordered from the oldest to the newest
					$putcode = getValues($ioi, "SELECT `putCode` FROM `putCodes` where (`localSourceRecordID` = 'local_person_$localPersonID' or `localSourceRecordID` = 'ldap_$email') and `deleted` IS NULL ORDER BY `added` ASC limit 1", array('putCode'), 'singleValue');

					//format adffiliation
					$localAffiliation = prepAffiliationAsArray($localPersonID, $rowID);


					// before send it to xml remove the unnecessary field using set
					if(isset($localAffiliation[$rowID]['fields']['personOrgRelation']))
						unset($localAffiliation[$rowID]['fields']['personOrgRelation']);

					unset($localAffiliation[$rowID]['fields']['localSourceRecordID']);

					//if the old id is exits in the database and the user gave us the permission to update
					if(!is_null($putcode) && (strpos($permission[0]['scope'], '/affiliations/update') !== FALSE || strpos($permission[0]['scope'], '/activities/update') !== FALSE) ){

						//covert the affiliation to XML
						$xml = prepAffiliationXML($localAffiliation[$rowID], $orcid, $putcode);

						//update the affiliation
						$response = updateSingleItem($orcid, $permission[0]['access_token'], $localAffiliation[$rowID]['type'], $putcode, $xml);

						//failure returns array
						if(is_string($response)){

							$recordType = saveRecord($orcid, $localAffiliation[$rowID]['type'], $putcode, $localSourceRecordID, $xml, 'XML', $response);
							//increase the count
							$recordTypeCounts['updated']++;
							//get the first putcode and update it with this affiliation
							$ioi->query("UPDATE `putCodes` SET `localSourceRecordID` = '$localSourceRecordID' AND `type` = '".$localAffiliation[$rowID]['type']."' AND `submittedData` = '$xml' where `putCode` = '$putcode'");


						}else{

							$recordTypeCounts['skipped - failed to update']++;
							$report .= print_r($response, True);
						}
					} // end of if id exits

					elseif(is_null($putcode) && (strpos($permission[0]['scope'], '/affiliations/create') !== FALSE || strpos($permission[0]['scope'], '/activities/update') !== FALSE) ){

						//convert the affiliation to XML
						$xml = prepAffiliationXML($localAffiliation[$rowID]);
						$response = postToORCID($orcid,$permission[0]['access_token'], $localAffiliation[$rowID]['type'], $xml);

						//failure returns array
						if(is_string($response))
						{
							//echo $response;
							$location = str_replace('||','',explode('/', explode('Location: ', $response)[1]));
							$putCode = trim($location[count($location)-1]);
							$recordType = saveRecord($orcid, $localAffiliation[$rowID]['type'], $putCode, $localSourceRecordID, $xml, 'XML', $response);
							$recordTypeCounts['new']++;

						}//end of response
						else{

							$recordTypeCounts['skipped - failed to create affiliation']++;
							$report .= print_r($response, TRUE);
						}
					} //end of if the old id not exists

					else{

						$recordTypeCounts['skipped - no create or update scope']++;
					}
				} //end of if is_null($pucode) && is_null($ignored)
				elseif(!empty($ignored)){

					//they items ignored
					$recordTypeCounts['skipped - Ignored affiliation']++;

				} else{

					$recordTypeCounts['skipped - no create or update scope']++;

				}
			} //end of for each $newaffiliation
		} // end of permission if statament
	} // if the permission is empty

	else {

		//the user hasn't granted permission
		$recordTypeCounts['skipped - no permission token']++;
	}

	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

	echo $summary;
} //end of for each $orcids
