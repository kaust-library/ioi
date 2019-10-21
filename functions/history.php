<?php 


/*


**** This file is responsible of diplaying the history data for each user.

** Parameters :
	$localPersonID : unique identifier for each person in the institution.



** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------




function history($localPersonID){

	global $ioi;
	$displayname = $_SESSION[LDAP_NAME_ATTRIBUTE];

	$name = getName($localPersonID, $displayname);

	// get all the works 
	$localWorkIDs = getValues($ioi, "SELECT DISTINCT idInSource,`added` FROM `metadata` m WHERE `source` LIKE 'dspace' AND `field` LIKE 'dc.contributor.author' AND `value` LIKE '$name' AND deleted IS NULL ORDER BY `added` DESC", array('idInSource'), 'arrayOfValues');

	// get the orcid id using the user email
	$orcid = getValues($ioi, "SELECT `orcid` FROM `orcids` WHERE `email` LIKE '".$_SESSION[LDAP_EMAIL_ATTRIBUTE]."'", array('orcid'), 'singleValue');

	// get only the orcid work(s) that not deleted
	$existingPutCodes = getValues($ioi, "SELECT `localSourceRecordID` FROM `putCodes` WHERE  `type` = 'work' AND `orcid` = '$orcid' AND deleted IS NULL ", array('localSourceRecordID'), 'arrayOfValues');

	// get all the work(s) that ignored
	$ignoreds = getValues($ioi, "SELECT `ignored` FROM `ignored`  WHERE `type` = 'work' AND `orcid` = '$orcid' AND `ignored` IS NOT NULL AND deleted IS NULL", array('ignored'), 'arrayOfValues');

	echo '<br><h5><b>Publications</b></h5>';
		
	echo '<li style="font-color:Black;">'.count($localWorkIDs).' Total records for work(s) in the '.INSTITUTION_ABBREVIATION.' repository matching your name as an author.</li>';

	echo '<li style="font-color:Black;">'.count($existingPutCodes).' Records for work(s) in the '.INSTITUTION_ABBREVIATION.' repository were successfully added to your ORCID record.</li>';

	echo '<li style="font-color:Black;">'.count($ignoreds).' Records for work(s) in the '.INSTITUTION_ABBREVIATION.' repository were ignored.</li>';
	//------------------------------------------------------------------------------------------------------------------

	// get all the affiliations for the user for which a job title is listed
	$localPersonOrgRelationRowIDs = getValues($ioi, "SELECT `rowID` FROM `metadata` m WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.personOrgRelation.id' AND deleted IS NULL AND EXISTS (SELECT * FROM `metadata` WHERE `parentRowID` = m.`rowID` AND `field` = 'local.person.title' AND deleted IS NULL ORDER BY `metadata`.`added` DESC)", array('rowID'), 'arrayOfValues');

	//if it's empty check the session
	if(empty($localPersonOrgRelationRowIDs) && !empty($_SESSION[LDAP_TITLE_ATTRIBUTE]))
		array_push($localPersonOrgRelationRowIDs, $_SESSION[LDAP_TITLE_ATTRIBUTE]);

	// get all the affiliation(s) that were sent to orcid
	$existingPutCodes = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE  `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'local_person_$localPersonID%' AND deleted IS NULL", array('putCode'), 'arrayOfValues');

	//if it's empty check the session
	if(empty($existingPutCodes ))
		$existingPutCodes = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE  `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'ldap_".$_SESSION[LDAP_EMAIL_ATTRIBUTE]."' AND deleted IS NULL", array('putCode'), 'arrayOfValues');

	// get all the affiliation(s) that were ignored
	$ignoreds = getValues($ioi, "SELECT `ignored`  FROM `ignored`  WHERE `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'local_person_$localPersonID%' AND `ignored` IS NOT NULL AND deleted IS  NULL", array('ignored'), 'arrayOfValues');

	if(empty($ignoreds))
		$ignoreds = getValues($ioi, "SELECT `ignored`  FROM `ignored`  WHERE `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'ldap_".$_SESSION[LDAP_EMAIL_ATTRIBUTE]."'  AND `ignored` IS NOT NULL AND deleted IS  NULL", array('ignored'), 'arrayOfValues');

	echo '<hr><h5><b>Affiliation</b></h5>';

	echo '<li style="font-color:Black;">'.count($localPersonOrgRelationRowIDs).' Total records for affiliation(s) in '.INSTITUTION_ABBREVIATION.'.</li>';

	echo '<li style="font-color:Black;">'.count($existingPutCodes).' Records for affiliation(s) in '.INSTITUTION_ABBREVIATION.' were successfully added to your ORCID record.</li>';

	echo '<li style="font-color:Black;">'.count($ignoreds).' Records for affiliation(s) in '.INSTITUTION_ABBREVIATION.' were ignored.</li><br>';

}