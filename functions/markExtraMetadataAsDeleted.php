<?php
	/*

	**** This file defines a function to mark existing entries with place greater than current count as deleted .

	** Parameters :
		$source : name of the source system.
		$idInSource : id of this record in the source system.
		$field : standard field name in the format namespace.element.qualifier .
		$place : current count of values for this field on this item.
		$parentRowID : if row is the child of another row, this will be the parent row's rowID, otherwise it will be NULL.
		$currentFields : array of field names used on the current record

	** Created by : Daryl Grenz
	** Institute : King Abdullah University of Science and Technology | KAUST
	** Date :  1 April 2019 - 8:00 AM 

	*/
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	
	function markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, $field, $place, $currentFields)
	{			
		global $ioi;
		
		if(!empty($parentRowID)&&empty($field)&&empty($place)&&empty($currentFields))
		{
			//Mark all children of a deleted row as deleted
			$check = $ioi->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL");

			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($field)&&is_int($place))
		{
			//mark existing entries with place greater than current count as deleted
			if($parentRowID === NULL)
			{
				$check = $ioi->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}
			else
			{
				$check = $ioi->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$field' AND place > '$place' AND deleted IS NULL");
			}
			
			markMatchedRowsAsDeleted($check, $source, $idInSource);
		}
		elseif(!empty($currentFields))
		{
			//Mark metadata fields previously but no longer used on the item as deleted
			if(is_null($parentRowID))
			{
				$previousFields = getValues($ioi, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND deleted IS NULL", array('field'), 'arrayOfValues');
			}
			else
			{
				$previousFields = getValues($ioi, "SELECT DISTINCT field FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND deleted IS NULL", array('field'), 'arrayOfValues');
			}
			
			foreach($previousFields as $previousField)
			{
				if(!in_array($previousField, $currentFields))
				{					
					if(is_null($parentRowID))
					{
						$check = $ioi->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID IS NULL AND field LIKE '$previousField' AND deleted IS NULL");	
					}
					else
					{
						$check = $ioi->query("SELECT rowID FROM metadata WHERE source LIKE '$source' AND idInSource LIKE '$idInSource' AND parentRowID LIKE '$parentRowID' AND field LIKE '$previousField' AND deleted IS NULL");	
					}
					
					markMatchedRowsAsDeleted($check, $source, $idInSource);
				}
			}		
		}
	}