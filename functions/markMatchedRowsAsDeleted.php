<?php
	/*

	**** This file defines a function to mark matched rows as deleted .

	** Parameters :
		$check : query result containing list of rowIDs to mark as deleted in the metadata table
		$source : name of the source system.
		$idInSource : id of this record in the source system.

	** Created by : Daryl Grenz
	** Institute : King Abdullah University of Science and Technology | KAUST
	** Date :  1 April 2019 - 8:00 AM 

	*/
	//--------------------------------------------------------------------------------------------------------------------------------------------------
	
	function markMatchedRowsAsDeleted($check, $source, $idInSource)		
	{
		global $ioi;
		
		//if matched
		if(mysqli_num_rows($check) !== 0)
		{
			while($row = $check->fetch_assoc())
			{
				$rowID = $row['rowID'];
				update($ioi, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $rowID), 'rowID');
				
				//Recursively mark any children of this row as deleted as well
				markExtraMetadataAsDeleted($source, $idInSource, $rowID, '', '', '');
			}
		}
	}