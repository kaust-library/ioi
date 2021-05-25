<?php
/*

**** This file is responsible for saving ldap data from the session to the metadata table.

** Parameters :
	No parameters required

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 14 October - 9:44 AM 

*/

//------------------------------------------------------------------------------------------------------------

function saveLdapDataToMetadata()
{
	global $ioi;
	
	$idInSource = 'person_'.$_SESSION[LDAP_PERSON_ID_ATTRIBUTE];
	
	//check if the user has a data entry already in the metadata table
	$existingEntry = getValues($ioi, "SELECT `rowID` FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` LIKE ' $idInSource' AND deleted IS NULL", array('rowID'), 'singleValue');
	
	if(empty($existingEntry))
	{
		// init 
		$input = array();
		$source = 'local';		

		// start date
		$year = substr($_SESSION[LDAP_START_DATE_ATTRIBUTE],0,4);
		$month = substr($_SESSION[LDAP_START_DATE_ATTRIBUTE],4,2);
		$day = substr($_SESSION[LDAP_START_DATE_ATTRIBUTE],6,2);
		
		$startDate = $year.'-'.$month.'-'.$day;
		
		$input['local.person.name'][]['value'] = getName($_SESSION[LDAP_PERSON_ID_ATTRIBUTE], $_SESSION[LDAP_NAME_ATTRIBUTE]);
		$input['local.person.id'][]['value'] = $_SESSION[LDAP_PERSON_ID_ATTRIBUTE];
		$input['local.person.email'][]['value'] = $_SESSION[LDAP_EMAIL_ATTRIBUTE];		
		
		//getting org ID from department name
		$orgID = getValues($ioi, "SELECT `idInSource` FROM `metadata` WHERE `value` = '". $_SESSION[LDAP_DEPARTMENT_ATTRIBUTE]."' AND field = 'local.org.name' AND source = 'local' AND deleted IS NULL", array('idInSource'), 'singleValue');
		
		if(!empty($orgID))
		{
			// Create personOrgRelation
			$orgID = str_replace('org_', '', $orgID);
			
			// add personOrgRelation
			$input['local.personOrgRelation.id'][0]['value'] = $_SESSION[LDAP_PERSON_ID_ATTRIBUTE].'_'.$orgID.'_'.$startDate;	
			$input['local.personOrgRelation.id'][0]['children']['local.org.id'][0]['value'] = $orgID;
			$input['local.personOrgRelation.id'][0]['children']['local.person.title'][0]['value'] = $_SESSION[LDAP_TITLE_ATTRIBUTE];
			$input['local.personOrgRelation.id'][0]['children']['local.date.start'][0]['value'] = $startDate;
		}
		
		$report = saveValues($source, $idInSource, $input, NULL);
	}
}