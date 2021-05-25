<?php
	/*

	**** This file is responsible for updating DSpace records ( adding or removing an ORCID ID)

	** Parameters :
		$works : New works added to database.
		$message : (ORCID iD added to or ORCID iD removed by)		

	** Created by : Yasmeen Alsaedy
	** institute : King Abdullah University of Science and Technology | KAUST
	** Date : 16 May 2019 - 10:30 AM 

	*/
	//-----------------------------------------------------------------------------------------------------------

	function updateORCIDiDsInDSpace($works, $initMessage)
	{
		global $ioi, $report, $recordTypeCounts, $errors;
		
		$changedCount = 0;

		// get the access token 
		$dSpaceAuthHeader = loginToDSpaceRESTAPI();

		if(is_string($dSpaceAuthHeader))
		{
			// for each work 
			foreach($works as $work)
			{
				//initiate flag as false - changing to true means that the author value has been changed
				$flag = false;
				
				$recordTypeCounts['all']++;

				// set orcid variable 
				$orcid =  $work['orcid'];
				
				$report .= $orcid.PHP_EOL;

				// set handle variable
				$handle = str_replace('repository_', '', $work['localSourceRecordID']);
				
				$report .= $handle.PHP_EOL;

				// get the localPersonID linked to the ORCID
				$localPersonID = getValues($ioi, "SELECT `localPersonID` FROM `orcids` where `orcid` = '$orcid'", array('localPersonID'), 'singleValue');

				// get name based on localPersonID
				$name = getValues($ioi, "SELECT `value` FROM `metadata` WHERE source = 'local' AND `idInSource` = 'person_$localPersonID' AND `field` = 'local.person.name' AND deleted IS NULL",  array('value'), 'singleValue');

				//get the itemID for the handle - selected work 
				$itemID = getValues($ioi, "SELECT `value` FROM `metadata` WHERE source = 'dspace' AND `idInSource` = '$handle' AND `field` = 'dspace.internal.itemID' AND deleted IS NULL", array('value'), 'singleValue');

				$report .= $itemID.PHP_EOL;
				
				//get the item metadata
				$json = getItemMetadataFromDSpaceRESTAPI($itemID, $dSpaceAuthHeader);
				
				sleep(5);
				
				if(is_string($json))
				{
					//get the metadata
					$metadata = dSpaceMetadataToArray(json_decode($json, TRUE));
					
					$dc_contributor_authors = $metadata['dc.contributor.author'];

					// search in the array for the author name 
					foreach($dc_contributor_authors as $count => $dc_contributor_author)
					{
						if($initMessage === 'ORCID iD added to ')
						{
							// if the ORCID iD is already in the author value skip
							if(strpos($dc_contributor_author['value'], $orcid) !== false)
							{								
								continue;
							}
						
							// get the author name 
							$authorName = explode('::', $dc_contributor_author['value'])[0];
							
							if($authorName === $name)
							{
								// change the author value to name::orcid format
								$dc_contributor_authors[$count]['value'] = $authorName.'::'.$orcid;
					
								// if you put the ORCID in the value change the flag to update the item
								$flag = true;
								break;							
							} // end of the $name == $authorName if statement
						} //end of the added message
						elseif($initMessage === 'ORCID iD removed by ')
						{
							// if the ORCID iD is NOT in the author value skip		
							if(strpos($dc_contributor_author['value'], $orcid) === false)
							{
								$flag = false;
								continue;						
							}

							// change the value for each item to (Auther name only) if the work is unselected

							//get the author name 
							$authorName = explode('::', $dc_contributor_author['value'])[0];

							if($authorName === $name )
							{
								// put name only
								$dc_contributor_authors[$count]['value'] = $authorName;	
							
								// if you removed the ORCID from the value change the flag to update the item
								$flag = true;
								break;
							}
						} // end of else ( remove message)
					} // end of the contributor author loop
										
					if( $flag )
					{
						// return the dc_contributor_authors after the edit to the metadata
						$metadata['dc.contributor.author'] = $dc_contributor_authors;

						$message = $initMessage.''.$name;

						// add a provenance entry by adding the name to the provided message ( 'ORCID iD added to ' or 'ORCID iD removed by ')
						$metadata = appendProvenanceToMetadata($itemID, $metadata, $message, $name);

						// convert the array to json file
						$json = prepareItemMetadataAsDSpaceJSON($metadata);

						// send the array to DSpace
						$response = putItemMetadataToDSpaceRESTAPI($itemID, $json, $dSpaceAuthHeader);
						
						sleep(5);
						
						if(!is_array($response))
						{
							$changedCount++;
							
							$report .= '-- '.$message.PHP_EOL;
							
							sleep(5);
							
							$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($handle, $dSpaceAuthHeader, 'metadata');
							
							if(is_string($dspaceObject))
							{
								//process item
								$recordType = processDSpaceRecord('dspace', $handle, $dspaceObject);
							}
							else
							{
								$recordType = 'skipped';

								$errors[] = array('type'=>'getDSpaceObject','message'=>$handle.' - error response from DSpace REST API: '.print_r($dspaceObject, TRUE));
							}
						}
						else
						{
							$recordTypeCounts['skipped']++;
							
							$errors[] = array('type'=>'putItemMetadata','message'=>' - error response from DSpace REST API: '.print_r($response, TRUE));
							
							$report .= print_r($response, TRUE);
						}
					} // end of the flag if statement
					else
					{
						$recordTypeCounts['unchanged']++;
						$report .= '-- unchanged'.PHP_EOL;
					}
				}
				else
				{
					$recordTypeCounts['skipped']++;
					
					$errors[] = array('type'=>'getItemMetadata','message'=>' - error response from DSpace REST API: '.print_r($json, TRUE));
					
					$report .= print_r($json, TRUE);
				}
				
				ob_flush();
				flush();
				set_time_limit(0);
		
			} // end of the selected work loop 
		}
			
		return $changedCount;

	} //end of the function 
