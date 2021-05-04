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

		if( !empty($org[0])) {

			# get the value from each array ( row )
			$idInSource = 'org_'.$org[0];
			$field = 'local.org.id';
			$value = $org[0];
			$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
			if($result['status']!=='unchanged')
			{
				$changes[$field] = $result['status'];
			}

			# insert the Organization name
			if(!empty($org[1])){

				$field = 'local.org.name';
				$value = $org[1];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}
				
			}

			# insert the Organization type
			if(!empty($org[2])) {

				$field = 'local.org.type';
				$value = $org[2];
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}

			# insert the Organization start date
			if(!empty($org[3]))
			{

				$field = 'local.date.start';
				$value = $org[3];
				$time = strtotime($value);
				$value = date('Y-m-d',$time);
				$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
				if($result['status']!=='unchanged')
				{
					$changes[$field] = $result['status'];
				}

			}

			# insert the Organization end date
			if( !empty($org[4])) {
				
				$field = 'local.date.end';
				$value = $org[4];
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
