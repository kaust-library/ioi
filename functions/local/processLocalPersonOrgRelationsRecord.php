<?php

//Define function to process local results
function processLocalPersonOrgRelationsRecord($person)
{
	# database connection
	global $ioi;

	#set the common values
	$source = 'local';
	$result = array();

	#init array to track changes for reporting
	$changes = array();
	
	if(!empty($person['person_id']))
	{
		# get the local person id
		$idInSource = getValues($ioi, "SELECT `idInSource` FROM `metadata` WHERE source = 'local' AND (`field` IN('local.person.personnelNumber', 'local.person.studentNumber')) AND `value` = '".$person['person_id']."' AND deleted IS NULL", array('idInSource'), 'singleValue');

		if(!empty($idInSource))
		{
			# insert the local.personOrgRelation.id ( This is the parent row for the details for each person org relation)
			if(!empty($person['person_id']) || !empty($person['org_id']) || !empty($person['period_start_date']))
			{
				$startDate = date('Y-m-d', strtotime($person['period_start_date']));
				
				$field = 'local.personOrgRelation.id';
				$value = $person['person_id'].'_'.$person['org_id'].'_'.$startDate;

				//We assume that each person will only have one position with a given start date, the exception is faculty who will not have a job title for their extra affiliations
				$existingRelationRowID = getValues($ioi, "SELECT rowID FROM metadata 
					WHERE source = '$source' 
					AND idInSource = '$idInSource'
					AND field LIKE 'local.personOrgRelation.id'
					AND deleted IS NULL
					AND rowID IN (
						SELECT parentRowID FROM metadata 
						WHERE source = '$source' 
						AND field LIKE 'local.date.start'
						AND value LIKE '$startDate'
						AND deleted IS NULL
						)
					AND rowID IN (
						SELECT parentRowID FROM metadata 
						WHERE source = '$source' 
						AND field LIKE 'local.person.title'
						AND deleted IS NULL
						)", array('rowID'), 'singleValue');

				if(empty($existingRelationRowID))
				{
					$existingPlace = getValues($ioi, "SELECT `place` FROM `metadata` WHERE source = '$source' AND idInSource = '$idInSource' AND `field` = '$field' AND deleted IS NULL ORDER BY `place` DESC LIMIT 1", array('place'), 'singleValue');

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
					$parentRowID = $existingRelationRowID;
				}

				//If there is a problem inserting the parent row, don't save any children
				if(!empty($parentRowID))
				{
					// all other fields will be children of the personOrgRelation ID
					$childFields = array('org_id'=>'local.org.id','job_title'=>'local.person.title','local.date.start','local.date.end');
		
					foreach($person as $childKey => $childValue)
					{
						$childField = $childFields[$childKey];
						
						// Currently we are only updating org ids and end dates for existing personOrgRelations. We are not updating job titles because the older titles may be more correct for faculty and the start date is the basis for the match so it will always be unchanged
						if(!empty($existingRelationRowID) && in_array($childField, array('local.person.title','local.date.start')))
						{
							continue;
						}
						
						if(!empty($childValue))
						{
							if(in_array($childField, array('local.date.start','local.date.end')))
							{
								$childValue = date('Y-m-d',strtotime($childValue));
							}
							
							$result = saveValue($source, $idInSource, $childField, 1, $childValue, $parentRowID);
							
							if($result['status']!=='unchanged')
							{
								$changes[$childField] = $result['status'];
							}
						}
					}
				}
			}
		}
	}
	return array('idInSource'=>$idInSource,'changes'=>$changes);
}
