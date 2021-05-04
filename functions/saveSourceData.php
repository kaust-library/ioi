<?php	
/*

**** This file is responsible for saving a copy of the original source data that has been harvested from other systems, before the individual values are saved in the metadata table.

** Parameters :

	$source : the name of the source system.
	$idInSource : the id of this record in the source system.
	$sourceData : the full record in the source format.
	$format : the format of the record (XML, JSON, etc.).
	
** Output :	returns recordType (new, modified, or unchanged).

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------


function saveSourceData($source, $idInSource, $sourceData, $format)
{
	global $ioi, $report, $errors;
	
	//check for existing entry
	$check = select($ioi, "SELECT rowID, sourceData FROM sourceData WHERE source LIKE ? AND idInSource LIKE ? AND deleted IS NULL", array($source, $idInSource));
	
	//if not existing			
	if(mysqli_num_rows($check) === 0)
	{								
		$recordType = 'new';
		
		if(!insert($ioi, 'sourceData', array('source', 'idInSource', 'sourceData', 'format'), array($source, $idInSource, $sourceData, $format)))
		{
			$error = end($errors);
			$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
		}
	}		
	else
	{
		$row = $check->fetch_assoc();
		$existingData = $row['sourceData'];
		$existingRowID = $row['rowID'];
		
		//if scourceData has changed, mark old sourceData as replaced
		if($existingData !== $sourceData)
		{	
			$recordType = 'modified';
			
			if(!insert($ioi, 'sourceData', array('source', 'idInSource', 'sourceData', 'format'), array($source, $idInSource, $sourceData, $format)))
			{
				$error = end($errors);
				$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
			}
			$newRowID = $ioi->insert_id;
	
			if(!update($ioi, 'sourceData', array("deleted", "replacedByRowID"), array(date("Y-m-d H:i:s"), $newRowID, $existingRowID), 'rowID'))
			{
				$error = end($errors);
				$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
			}
		}
		else
		{
			$recordType = 'unchanged';
		}
	}
	return $recordType;
}	
