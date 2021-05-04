<?php

/*
**** This file is responsible of converting the DSpace metdata to associative array.

** Parameters :
	$dspaceMetadata : json file generated from DSpace.
	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------	


function dSpaceMetadataToArray($dspaceMetadata)
	{
		$metadata = array();
		
		foreach($dspaceMetadata as $entry)
		{
		  $metadata[$entry['key']][] = array('value'=>$entry['value'],'language'=>$entry['language']);
		}
		
		return $metadata;
	}
