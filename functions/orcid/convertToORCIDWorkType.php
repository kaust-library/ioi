<?php
/*

**** This file is responsible of mapping the type in Dspace to the equivalent type in ORCID.

** Parameters :
	$type : item type ( work ).

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------


function convertToORCIDWorkType($type)
{
	global $ioi;		
	
	$type = getValues($ioi, "SELECT `orcidField` FROM `mappings` WHERE `source` = 'dspace' AND sourceField = '$type' AND `entryType` = 'workType'", array('orcidField'), 'singleValue');
	
	if(empty($type))
	{
		$type = 'other';
	}
	
	return $type;
}
