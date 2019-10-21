<?php
/*

**** This file is responsible of retrieving the metadata values for a single work.

** Parameters :
	$localWorkID : work handle in the database.
	

** Created by : Daryl Grenz, Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------
function prepWorkAsArray($localWorkID)
{
	//call databases
	global $ioi;

	$work = array();
	
	$work['idInSource'] = $localWorkID;

	$fields = getValues($ioi, "SELECT `sourceField`, `orcidField` FROM `mappings` WHERE `source` = 'dspace' AND `entryType` = 'work' ORDER BY place ASC", array('sourceField', 'orcidField'), 'arrayOfValues');

	foreach($fields as $field)
	{
		$sourceField = $field['sourceField'];
		
		$orcidField = $field['orcidField'];
		
		if(strpos($orcidField, '.')!==FALSE)
		{
			//This was set up for use with external-ids, where there may be more than 1 of a given idType on a single item.
			$fieldParts = explode('.', $orcidField);
			
			$values = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'dspace' AND `idInSource` = '$localWorkID' AND `field` = '$sourceField' AND deleted IS NULL", array('value'), 'arrayOfValues');
			
			$work[$fieldParts[0]][$fieldParts[1]] = $values;
		}
		else
		{
			$value = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `source` = 'dspace' AND `idInSource` = '$localWorkID' AND `field` = '$sourceField' AND deleted IS NULL", array('value'), 'singleValue');
			
			$work[$orcidField] = $value;
		}
	}

	return $work;
}