<?php

/*

**** This file is responsible of getting single work from ORCID.

** Parameters :
	$localWorkID : work handle in the database.
	

** Created by : Daryl Grenz, Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------
function prepAffiliationAsArray($localPersonID, $localPersonOrgRelationRowID ){

	global $ioi;

	$localAffiliation = array();

	$localOrgID = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `parentRowID` = '$localPersonOrgRelationRowID' AND `field` = 'local.org.id' AND deleted IS NULL", array('value'), 'singleValue');

	$localOrgType = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'org_$localOrgID' AND `field` = 'local.org.type' AND deleted IS NULL", array('value'), 'singleValue');

	// get the unique value 
	$personOrgRelationID = getValues($ioi,"SELECT `value`  FROM `metadata` WHERE `source` LIKE 'local' AND `idInSource` = 'person_$localPersonID' and `field` = 'local.personOrgRelation.id' and `rowID` = '$localPersonOrgRelationRowID'", array('value'), 'singleValue');
	$localAffiliation['fields']['personOrgRelation'] = $personOrgRelationID;

	if(in_array($localOrgType, array('corelab','division','office','program','researchcenter','sector','university')))
	{
		$localAffiliation['fields']['department-name'] = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = CONCAT('org_', $localOrgID) AND `field` = 'local.org.name' AND deleted IS NULL", array('value'), 'singleValue');
	}
	else
	{
		$localOrgName = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = CONCAT('org_', $localOrgID) AND `field` = 'local.org.name' AND deleted IS NULL", array('value'), 'singleValue');
		
		$parentOrgName = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = CONCAT('org_', (SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'org_$localOrgID' AND `field` = 'local.org.parent' AND deleted IS NULL)) AND `field` = 'local.org.name' AND deleted IS NULL", array('value'), 'singleValue');
		
		$localAffiliation['fields']['department-name'] = "$parentOrgName ($localOrgName)";
	}

	// make a unique id for each employment or education record
	$localSourceRecordID = "local_person_".$localPersonID."_".$personOrgRelationID;
	$localAffiliation['fields']['localSourceRecordID'] = $localSourceRecordID ;

	$localAffiliation['fields']['role-title'] = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `parentRowID` = '$localPersonOrgRelationRowID' AND `field` = 'local.person.title' AND deleted IS NULL", array('value'), 'singleValue');

	$localAffiliation['dates']['start-date'] = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `parentRowID` = '$localPersonOrgRelationRowID' AND `field` = 'local.date.start' AND deleted IS NULL", array('value'), 'singleValue');

	$localAffiliation['dates']['end-date'] = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'local' AND `idInSource` = 'person_$localPersonID' AND `parentRowID` = '$localPersonOrgRelationRowID' AND `field` = 'local.date.end' AND deleted IS NULL", array('value'), 'singleValue');

	if(in_array($localAffiliation['fields']['role-title'], explode('||', getValues($ioi, "SELECT `titles` FROM `groups` WHERE `label` = 'Students'", array('titles'), 'singleValue'))))
	{
		$localAffiliation['type'] = 'education';
	}
	else
	{
		$localAffiliation['type'] = 'employment';
	}

	return $localAffiliation;

}