<?php	


/*

**** This file is responsible of uploading the local person data to IOI database.

** Parameters :
	$person : person data from CSV file ( one row as array).

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 10:30 AM 

*/


//-----------------------------------------------------------------------------------------------------------


function processLocalPersonsDataRecord($person)
{
	# database connection
	global $ioi;
	
	#set the common values
	$source = 'local';
	$place = 1;
	$parentRowID = NULL;
	$changes = array();
	$idInSource = '';

	if( !empty($person) ) {
		
		if(!empty($person[4])) {

			# create the person id 
			$idInSource = 'person_'.$person[4];

			// employee id 6 digits
			if(!empty($person[0]) && strlen($person[0]) === 6){

				$field = 'local.person.personnelNumber';
				$value = $person[0];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
			}
			// student id is more than 6 digits
			elseif(!empty($person[0]) && strlen($person[0]) > 6){

				$field = 'local.person.studentNumber';
				$value = $person[0];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}

			# insert the person name
			if(!empty($person[1])){

				$field = 'local.person.name';
				
				//Do not override locally updated names when new data is uploaded
				if(empty(getValues($ioi, "SELECT `value` FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND `field` = '$field' AND place = '$place' AND deleted IS NULL", array('value'), 'singleValue')))
				{
					$value = trim($person[1].', '.$person[2].' '.$person[3]);
					$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
					if($result['status']!=='unchanged')
					{
						$changes[$field] = $result['status'];
					}
				}

			}

			# insert the person institutional id
			if(!empty($person[4])) {

				$field = 'local.person.id';
				$value = $person[4];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
			}

			# insert the person email
			if(!empty($person[5]))
			{

				$field = 'local.person.email';
				$value = $person[5];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}		
		}
	}
	return array('idInSource'=>$idInSource,'changes'=>$changes);
}
	