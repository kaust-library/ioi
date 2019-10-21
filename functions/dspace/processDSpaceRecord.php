<?php	
	//Define function to process DSpace JSON metadata for a single repository item
	function processDSpaceRecord($source, $idInSource, $dspaceObject)
	{
		global $ioi, $report;
	
		$recordType = saveSourceData($source, $idInSource, $dspaceObject, 'JSON');

		$dspaceObject = json_decode($dspaceObject, TRUE);
		
		$itemID = (string)$dspaceObject['id'];
		
		$result = saveValue($source, $idInSource, 'dspace.internal.itemID', 1, $itemID, NULL);
		$rowID = $result['rowID'];

		$metadata = dSpaceMetadataToArray($dspaceObject['metadata']);
		
		//List of metadata fields in the current record
		$currentFields = array_keys($metadata);
		$currentFields[] = 'dspace.internal.itemID';
		$currentFields[] = 'dspace.date.modified';
		
		foreach($metadata as $field => $values)
		{
			$place = 0;
			foreach($values as $value)
			{
				$place++;
				
				if(in_array($field, ORCID_ENABLED_FIELDS))
				{
					$entryParts = explode('::', $value['value']);
						
					$value['value'] = $entryParts[0];
					
					$value['dspace.authority.key'] = $entryParts[1];
					
					if(isset($entryParts[2]))
					{
						$value['dc.identifier.orcid'] = $entryParts[2];
					}
				}
				
				$result = saveValue($source, $idInSource, $field, $place, $value['value'], NULL);
				$parentRowID = $result['rowID'];
				
				$childPlace = 1;				
				if($value['language'] !== NULL)
				{
					$childField = "dspace.metadata.language";
					$result = saveValue($source, $idInSource, $childField, $childPlace, $value['language'], $parentRowID);
					$rowID = $result['rowID'];
				}
				else
				{
					$childField = "dspace.metadata.language";
					markExtraMetadataAsDeleted($source, $idInSource, $parentRowID, $childField, 0, '');
				}
				
				if(isset($value['dspace.authority.key']))
				{
					$childField = "dspace.authority.key";
					$result = saveValue($source, $idInSource, $childField, $childPlace, $value['dspace.authority.key'], $parentRowID);
					$authorityRowID = $result['rowID'];
					
					if(isset($value['dc.identifier.orcid']))
					{
						$childField = "dc.identifier.orcid";
						$result = saveValue($source, $idInSource, $childField, $childPlace, $value['dc.identifier.orcid'], $authorityRowID);
						$rowID = $result['rowID'];
					}
				}
			}
			markExtraMetadataAsDeleted($source, $idInSource, NULL, $field, $place, '');
		}
		markExtraMetadataAsDeleted($source, $idInSource, NULL, '', '', $currentFields);
		return $recordType;
	}		
