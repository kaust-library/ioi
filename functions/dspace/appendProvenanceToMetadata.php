<?php

/*

**** This file is responsible of appending provenance to DSpace Metadata .

** Parameters :
	No parameters required
	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------
	function appendProvenanceToMetadata($itemID, $metadata, $process = NULL, $name)
	{
		
		$count = count($metadata['dc.description.provenance']);
		
		$metadata['dc.description.provenance'][$count+1]['value'] = 'Record metadata updated via REST API by '.$name.' using the '.IR_EMAIL.' user account on '.TODAY.' - '.$process .'.';
		
		$metadata['dc.description.provenance'][$count+1]['language'] = null;
		
		return $metadata;
	}
