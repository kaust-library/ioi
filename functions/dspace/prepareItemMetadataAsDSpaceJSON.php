<?php
/*


**** This file is responsible of preparing the item metadata, from array to JSON format .

** Parameters :
	$metadata : array of the item metadata.


** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

function prepareItemMetadataAsDSpaceJSON($metadata)
	{
		$json = array('metadata'=>array());
		
		foreach($metadata as $key => $entries)
		{		  
		  if(!empty($entries))
		  {
			if(is_array($entries))
			{
				foreach($entries as $entry)
				{
					$value = $entry['value'];
					
					$value = preg_replace('/\x{2010}/u','-', $value);
					
					$value = preg_replace('/\x{2009}/u',' ', trim($value));
				
					$json['metadata'][] = array('key'=>$key,'language'=>$entry['language'],'value'=>$value);
				}
			}
		  }		  		  
		}

		return json_encode($json['metadata'], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_HEX_QUOT);
}
