<?php

//Define function to process local results
function processLocalPersonOrgRelationsRecord($person)
{
	# database connection
	global $ioi;

	#set the common values
	$source = 'local';
	
	#init array to track changes for reporting
	$changes = array();

	if(!empty($person[0])) {

		# get the local person id
		$idInSource = getValues($ioi, "SELECT `idInSource` FROM `metadata` WHERE source = 'local' AND (`field` IN('local.person.personnelNumber', 'local.person.studentNumber')) AND `value` = '".$person[0]."' AND deleted IS NULL", array('idInSource'), 'singleValue');

		if(!empty($idInSource)) {

			# insert the local.personOrgRelation.id ( This is the parent row for the details for each person org relation)
			if(!empty($person[0]) || !empty($person[1]) || !empty($person[3])){

				$field = 'local.personOrgRelation.id';
				
				$value = $person[0].'_'.$person[1].'_'.$person[3];
				
				$existing = getValues($ioi, "SELECT `rowID`, `place` FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND `field` = '$field' and `value` = '$value' AND deleted IS NULL", array('rowID', 'place'), 'arrayOfValues');

				if(empty($existing))
				{
					$existingPlace = getValues($ioi, "SELECT `place` FROM `metadata` WHERE source = 'local' AND idInSource = '$idInSource' AND `field` = '$field' AND deleted IS NULL ORDER BY `place` DESC LIMIT 1", array('place'), 'singleValue');
					
					if(!empty($existingPlace))
					{
						$place = $existingPlace+1;
					}
					else
					{
						$place = 1;
					}
					
					$result = saveValue($source, $idInSource, $field, $place, $value, NULL);
					$parentRowID = $result['rowID'];
					if($result['status']!=='unchanged')
					{
						$changes[$field] = $result['status'];
					}
				}
				else
				{
					$place = $existing[0]['place'];
					$parentRowID = $existing[0]['rowID'];
				}				
				
				//If there is a problem inserting the parent row, don't save any children
				if(!empty($parentRowID))
				{
					// all other fields will be children of the personOrgRelation ID
					$place = 1;
					# insert person org id
					if( !empty($person[1])){

						$field = 'local.org.id';
						$value = $person[1];
						$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
						if($result['status']!=='unchanged')
						{
							$changes[$field] = $result['status'];
						}
					}

					# insert person title
					if( !empty($person[2])){

						$field = 'local.person.title';
						$value = $person[2];
						$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
						if($result['status']!=='unchanged')
						{
							$changes[$field] = $result['status'];
						}
					}

					#  insert the person start date with the organization
					if( !empty($person[3])){

						$field = 'local.date.start';
						$value = $person[3];
						$time = strtotime($value);
						$value = date('Y-m-d',$time);
						$result = saveValue($source, $idInSource, $field, $place, $value, $parentRowID);
						if($result['status']!=='unchanged')
						{
							$changes[$field] = $result['status'];
						}

					}

					# insert the person end date with the organization
					if( !empty($person[4])){

						$field = 'local.date.end';
						$value = $person[4];
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
		}
	}
	return array('idInSource'=>$idInSource,'changes'=>$changes);
}