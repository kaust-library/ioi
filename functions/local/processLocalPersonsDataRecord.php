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

	if(!empty($person)) {

		if(!empty($person['local_person_id'])) {

			# create the person id
			$idInSource = 'person_'.$person['local_person_id'];

			// employee id 6 digits
			if(!empty($person['person_id']) && strlen($person['person_id'] === 6)){

				$field = 'local.person.personnelNumber';
				$value = $person['person_id'];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
			}
			// student id is more than 6 digits
			elseif(!empty($person['person_id']) && strlen($person['person_id']) > 6)
			{
				$field = 'local.person.studentNumber';
				$value = $person['person_id'];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
			}

			# insert the person name
			if(!empty($person['last_name']))
			{
				$field = 'local.person.name';

				//Do not override locally updated names when new data is uploaded
				if(empty(getValues($ioi, "SELECT `value` FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND `field` = '$field' AND place = '$place' AND deleted IS NULL", array('value'), 'singleValue')))
				{
					$value = trim($person['last_name'].', '.$person['first_name'].' '.$person['middle_name']);
					$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
					if($result['status']!=='unchanged')
					{
						$changes[$field] = $result['status'];
					}
				}
			}

			# insert the person institutional id
			if(!empty($person['local_person_id']))
			{
				$field = 'local.person.id';
				$value = $person['local_person_id'];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
			}

			# insert the person email
			if(!empty($person['email']))
			{
				$field = 'local.person.email';
				$value = strtolower($person['email']);
				
				// check if the email is not contain the institutional email
				if(strpos(value, LDAP_ACCOUNT_SUFFIX ) !== FALSE ) {
					$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
					
					if($result['status']!=='unchanged')
					{
						$changes[$field] = $result['status'];
					}
				}
				 else {
					 
					 $changes[$field] = 'skipped';
				 }
			}
		}
	}
	return array('idInSource'=>$idInSource,'changes'=>$changes);
}
