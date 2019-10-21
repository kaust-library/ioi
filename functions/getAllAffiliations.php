<?php
/*


**** This file is responsible of getting all the affiliations that match the user name.

** Parameters :
	$orcid : unique identifier for each user in ORCID.	
	$localPersonID : unique identifier for each person in the institution.

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

	function getAllAffiliations($orcid, $localPersonID)
	{		
		global $ioi;
		
		$affiliations = array();

		$localAffiliation = array();

		$localPersonID = $localPersonID;

		// get all the affiliations that match the user name from the database
		// exclude orgRelation entries that lack a job title
		$localPersonOrgRelationRowIDs = getValues($ioi, "SELECT `rowID` FROM `metadata` m WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND deleted IS NULL AND EXISTS (SELECT * FROM `metadata` WHERE `parentRowID` = m.`rowID` AND `field` = 'local.person.title' AND deleted IS NULL ORDER BY `metadata`.`added` DESC)", array('rowID'), 'arrayOfValues');

		//if not matched in database use LDAP session info
		if(empty($localPersonOrgRelationRowIDs))
		{
			if(!empty($_SESSION[LDAP_TITLE_ATTRIBUTE]))
			{
				$localSourceRecordID = 'ldap_'.$_SESSION[LDAP_EMAIL_ATTRIBUTE];
				$localAffiliation['fields']['role-title'] = $_SESSION[LDAP_TITLE_ATTRIBUTE];
				$localAffiliation['fields']['localSourceRecordID'] = $localSourceRecordID;

				if(strpos($_SESSION['dn'], LDAP_STUDENT_DN_STRING) !== FALSE)
				{
					$localAffiliation['type'] = 'education';
				}
				else
				{					
					$localAffiliation['type'] = 'employment';
				}
	
				// check if the entry has been ignored and not deleted 
				$ignored = getValues($ioi, "SELECT `ignored` FROM `ignored` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('ignored'), 'singleValue');

				// that means it is currently unselected so make the flag = false
				if(!empty($ignored)) 
				{					
					$localAffiliation['selected'] = FALSE; 
				}
				else
				{
					$localAffiliation['selected'] = TRUE;
				}

				$affiliations[$localSourceRecordID] = $localAffiliation;
			}
			else{
				
				echo '<br><br><h4><b>Affiliations</b></h4>';
				echo '<li>There are no existing affilliation records in the '.INSTITUTION_ABBREVIATION.' system, so no work records have been added to your ORCID record. When new records are added to the '.INSTITUTION_ABBREVIATION.' system they will automatically be sent to your ORCID record.</li>';
				
			}
			
		}
		//if database match
		else 
		{
			foreach($localPersonOrgRelationRowIDs as $localPersonOrgRelationRowID)
			{	
				
				//format affiliation 
				$localAffiliation = prepAffiliationAsArray($localPersonID, $localPersonOrgRelationRowID);
				
				$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];
		
				// check if the entry has been ignored and not deleted 
				$ignored = getValues($ioi, "SELECT `ignored` FROM `ignored` WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('ignored'), 'singleValue');

				// that means it is currently unselected so make the flag = false
				if(!empty($ignored)) 
				{					
					$localAffiliation['selected'] = FALSE; 
				}
				else
				{
					$localAffiliation['selected'] = TRUE;
				}
				$affiliations[$localSourceRecordID] = $localAffiliation;
			}					
		}
		
		return $affiliations;
	}
