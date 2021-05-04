<?php
/*

**** This file is responsible for uploading the local organization iDs to IOI database.

** Parameters :
	$orgHierarchyData : organization iDs ( Parent and child ) in CSV file ( One row as array).

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 10:30 AM 

*/
//-----------------------------------------------------------------------------------------------------------

function processLocalOrgHierarchyRecord($orgHierarchyData)
{
	#init array to track changes for reporting
	$changes = array();
	
	if(!empty($orgHierarchyData)) {
		$idInSource = 'org_'.$orgHierarchyData[1];

		# insert the data into metadata table
		if(!empty($orgHierarchyData[1]) && !empty($orgHierarchyData[0]))
		{			
			$field = 'local.org.parent';			
			$result = saveValue('local', $idInSource, $field, 1, $orgHierarchyData[0], NULL);
			if($result['status']!=='unchanged')
			{
				$changes[$field] = $result['status'];
			}
		}
	}
	
	return array('idInSource'=>$idInSource,'changes'=>$changes);
}	
