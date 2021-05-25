<?php

/*

**** This file defines the function responsible for sending selected affiliations to ORCID and saving the records in the local database.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localAffiliations : array contains all the affiliations associative with ORCID id.
	$accessToken :  unique token for each user from ORCID.
	$localPersonID : unique identifier for each person in the institution.


** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function addAffiliation($orcid,  $localAffiliations, $localPersonID, $accessToken)
{
	global $ioi;
	
	$putCodes = array();

	$localSourceRecordID  = '';
	$existingPutCodes = array();
	
	foreach($localAffiliations as $localAffiliation)
	{
	

			// check if the non-LDAP affiliation already has an ORCID putcode
			$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];
			$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = '".$localAffiliation['type']."' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');


		
		if(!empty($existingPutCode)) {
			
			$existingPutCodes[] = $existingPutCode;

		}
		else {
		
			// if the work was previously marked as ignored, we will mark the ignored entry as deleted
			$result = $ioi->query("SELECT `rowID` FROM `ignored` WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = $localSourceRecordID");

			if( !is_null($result)) {
	
				$update = $ioi->query("UPDATE `ignored` SET `deleted` = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '$localSourceRecordID'");

			}

			// before creating xml remove the unnecessary fields
			if(isset($localAffiliation['fields']['personOrgRelation']))
				unset($localAffiliation['fields']['personOrgRelation']);

			unset($localAffiliation['fields']['localSourceRecordID']);
			unset($localAffiliation['selected']);

			$xml = prepAffiliationXML($localAffiliation);
			$response = postToORCID($orcid, $accessToken, $localAffiliation['type'], $xml);
		
			//print_r($response);
		
			//failure returns array
			if(is_string($response))
			{
				$putCode = extractPutCode($response);
				
				$recordType = saveRecord($orcid, $localAffiliation['type'], $putCode, $localSourceRecordID, $xml, 'XML', $response);
				
				$putCodes[] = $putCode;			

				// If the person has an existing ldap-based affiliation entry, replace it with the new entry
				$ldap_localrecord = 'ldap_'.$_SESSION[LDAP_EMAIL_ATTRIBUTE];
				$ldap_rowID = getValues($ioi, "SELECT `rowID` FROM `putCodes` where `localSourceRecordID` = '".$ldap_localrecord."'  AND deleted IS NULL AND `replacedByRowID` IS NULL and type ='".$localAffiliation['type']."'", array('rowID'), 'singleValue');

				if(!empty($ldap_rowID)) {

					// get the row of the corresponding putCode
					$putCodeRowID = getValues($ioi, "SELECT `rowID` FROM `putCodes` where `localSourceRecordID` = '".$localSourceRecordID."' and type ='".$localAffiliation['type']."'", array('rowID'), 'singleValue');

					// check if there is a local database entry for this person ID
					$result = $ioi->query("SELECT * FROM `metadata` WHERE `field`= 'local.person.name' and `idInSource` = 'person_$localPersonID'");

					if(!empty($result)) 
					{// update the record, put the replace row and mark it as deleted
						$update = $ioi->query("UPDATE `putCodes` SET `deleted` = '".date("Y-m-d H:i:s")."' , `replacedByRowID` =  'local_person_".$localPersonID."' where `rowID` = '".$ldap_rowID."' and type ='".$localAffiliation['type']."'");
					}		
				}

				// if the user have old id must be replace with the new one 
				$oldIdRows = getValues($ioi, "SELECT `rowID` FROM  `putCodes` WHERE `orcid` = '$orcid' AND `deleted` IS NULL  AND `localSourceRecordID`= 'local_person_".$localPersonID."'", array('rowID'), 'arrayOfValues');

				if(!empty($oldIdRows)){

					// get the row for the new id
					$putCodeRowID = getValues($ioi, "SELECT `rowID` FROM `putCodes` where `localSourceRecordID` =  '".$localSourceRecordID."' and `type` = '".$localAffiliation['type']."' ", array('rowID'), 'singleValue');

					// putCode to delete from ORCID
					$putCodeForOldRow = getValues($ioi, "SELECT `putCode` FROM `putCodes` where `localSourceRecordID` =  '".$localSourceRecordID."' and  `rowID` = '".$putCodeRowID."' and `type` = '".$localAffiliation['type']."'", array('putCode'), 'singleValue');

					// update the old id record, put the replace row and mark it as deleted
					$update = $ioi->query("UPDATE `putCodes` SET `deleted` = '".date("Y-m-d H:i:s")."' , `replacedByRowID` = '".$putCodeRowID."' where `rowID` = '".$oldIdRows[0]."' and type = '".$localAffiliation['type']."' ");

					// delete the record from the ORCID 
					deleteFromORCID($orcid, $accessToken, $localAffiliation['type'], $putCodeForOldRow);

				}
			}
		}			
	}

	echo '<hr><h4><b>Affiliation</b></h4>';

	echo '<li style="font-color:Black;">'.count($putCodes).' '.INSTITUTION_ABBREVIATION.' affiliation(s) successfully added to your ORCID record.</li>';
	echo '<li style="font-color:Black;">'.count($existingPutCodes).' '.INSTITUTION_ABBREVIATION.' affiliation(s) already exist in your ORCID record and were not changed.</li>';
	
}