<?php	
/*

**** This file is responsible for preparing common queries to the metadata table.

** Parameters :
	$source : name of the source system.
	$idInSource : id of record in the source system.
	$field : standard field name in the format namespace.element.qualifier .
	$place : the order of the values.
	$value : the metadata value.
	$parentRowID : if row is the child of another row, this will be the parent row's rowID, otherwise it will be NULL.

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

	function setSourceMetadataQuery($source, $idInSource, $parentRowID, $field)
	{
		if(is_null($parentRowID))
		{
			$query = "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInSource' AND `parentRowID` IS NULL AND `field` = '$field' AND `deleted` IS NULL ORDER BY `place` ASC";
		}
		elseif($parentRowID===TRUE)
		{
			$query = "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInSource' AND `parentRowID` IS NOT NULL AND `field` = '$field' AND `deleted` IS NULL ORDER BY `place` ASC";
		}
		else
		{
			$query = "SELECT `rowID`, `value` FROM `metadata` WHERE `source` = '$source' AND `idInSource` = '$idInSource' AND `parentRowID` = '$parentRowID' AND `field` = '$field' AND `deleted` IS NULL ORDER BY `place` ASC";
		}
		
		return $query;	
	}