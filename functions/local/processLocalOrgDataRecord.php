<?php	

/*

**** This file is responsible of uploading the local organization data to IOI database.

** Parameters :
	$org : organization data from CSV file ( One row as array).

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 10:30 AM 

*/
//-----------------------------------------------------------------------------------------------------------

function processLocalOrgDataRecord($org)
{

	#set the common values
	$source = 'local';
	$place = 1;
	$parentRowID = NULL;
	
	#init array to track changes for reporting
	$changes = array();
	
	if( !empty($org) ) { 

		if( !empty($org['organisation_id'])) {

			# get the value from each array ( row )
			$idInSource = 'org_'.$org['organisation_id'];
			$field = 'local.org.id';
			$value = $org['organisation_id'];
			$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
			if($result['status']!=='unchanged')
			{
				$changes[$field] = $result['status'];
			}

			# insert the Organization name
			if(!empty($org['name'])){

				$field = 'local.org.name';
				$value = $org['name'];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
				
			}

			# insert the Organization type
			if(!empty($org['type'])) {

				$field = 'local.org.type';
				$value = $org['type'];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}

			# insert the Organization start date
			if(!empty($org['start_date']))
			{

				$field = 'local.date.start';
				$value = $org['start_date'];
				$time = strtotime($value);
				$value = date('Y-m-d',$time);
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}

			# insert the Organization end date
			if( !empty($org['end_date'])) {
				
				$field = 'local.date.end';
				$value = $org['end_date'];
				$time = strtotime($value);
				$value = date('Y-m-d',$time);
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
