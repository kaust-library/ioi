<?php	
	
/*


**** This file is responsible of getting the authority key form th DB and send it to Dspace API.

** Parameters :
	$localPersonID : unique id for each user in the institution.	
	$displayname : the displayed name for the user in Ldap system .



** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 17 September 2019 - 2:09 AM 

*/

//-----------------------------------------------------------------------------------------------------------
function getName($localPersonID, $displayname)
{			
	global $ioi;
	
	// get the user name from the database based on the local person id
	$name = getValues($ioi, "SELECT * FROM `metadata` WHERE source = 'local' AND `field` = 'local.person.name' and `idInSource` = 'person_$localPersonID'", array('value'), 'singleValue');
	
	// if there is no match for the local person ID, take the name from the session ($displayname)
	if(empty($name)) {

		$nameArray =  explode(" ", $displayname);
		if(count($nameArray) > 1)
			$name = array_pop($nameArray).', '.implode(' ', $nameArray);
		else 
			$name = $nameArray[0];
	}
	
	return $name;		
}	
