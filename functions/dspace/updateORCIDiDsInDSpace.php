<?php
	/*

	**** This file is responsible of updating Dspace records ( add or remove ORCID ID)

	** Parameters :
		$works : New works added to database.
		$message : (Orcid id added to or Orcid id removed by)		

	** Created by : Yasmeen Alsaedy
	** institute : King Abdullah University of Science and Technology | KAUST
	** Date : 16 May 2019 - 10:30 AM 

	*/
	//-----------------------------------------------------------------------------------------------------------

	function updateORCIDiDsInDSpace($works, $message){

		global $ioi, $report, $recordTypeCounts, $errors;
		
		$changedCount = 0;

		// get the access token 
		$token = loginToDSpaceRESTAPI();

		if(is_string($token))
		{
			// for each work 
			foreach($works as $work)
			{
				//initiate flag as false - changing to true means that the author value has been changed
				$flag = false;
				
				$recordTypeCounts['all']++;

				// assign the orcid 
				$orcid =  $work['orcid'];
				
				$report .= $orcid.PHP_EOL;

				// assign the handle
				$handle = str_replace('repository_', '', $work['localSourceRecordID']);
				
				$report .= $handle.PHP_EOL;

				// get the name of the user 
				$email = getValues($ioi, "SELECT `email`  FROM `orcids` where `orcid` = '$orcid'", array('email'), 'singleValue');

				// get name from the email
				$name = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `field`= 'local.person.name' and `idInSource` = ( SELECT  `idInSource` FROM `metadata` WHERE source = 'local' AND `field`  = 'local.person.email' AND value = '$email' AND deleted IS NULL) AND deleted IS NULL",  array('value'), 'singleValue');

				//get the itemcode for the handle - selected work 
				$itemid =  getValues($ioi, "SELECT `value`  FROM `metadata` WHERE `field` = 'dspace.internal.itemID' AND `idInSource` = '$handle'", array('value'), 'singleValue');

				$report .= $itemid.PHP_EOL;
				
				//get the item metadata
				$json = getItemMetadataFromDSpaceRESTAPI($itemid, $token);
				
				if(is_string($json))
				{
					//get the metadata
					$metadata = dSpaceMetadataToArray(json_decode($json, TRUE));
					
					$dc_contributor_authors = $metadata['dc.contributor.author'];

					// search in the array for the author name 
					foreach($dc_contributor_authors as $count => $dc_contributor_author){

						if($message === 'ORCID iD added to ') {

							// change the value for each item to (Auther name :: $orcid) for selected work 

							// if the orcid id in the value skip
							if(strpos($dc_contributor_author['value'], $orcid)) {

								$flag = false;
								continue;
							}
						
							//get the author name 
							$authorname = explode('::', $dc_contributor_author['value'])[0];

							if($authorname === $name ){

								// get the dspace id
								$dspaceid = explode('::', $dc_contributor_author['value'])[1];

								// replace the dspace id with the orcid id  
								$value = str_replace($dspaceid , $orcid , $dc_contributor_author['value']);
								$dc_contributor_authors[$count]['value'] = $value;

								// if you put the ORCID in the value change the flag to update the item
								$flag = true;
								break;
							
							} // end of the $name == $authorname if statement

						} //end of the added message
						elseif($message === 'ORCID iD removed by '){

							// if the orcid id is not in the value skip		
							if(strpos($dc_contributor_author['value'], $orcid) === false) {

								$flag = false;
								continue;						
							}

							// change the value for each item to (Auther name only) if the work is unselected

							//get the author name 
							$authorname = explode('::', $dc_contributor_author['value'])[0];

							if($authorname === $name ){

								// put name only
								$dc_contributor_authors[$count]['value'] = $authorname;	
							
								// if you removed the ORCID from the value change the flag to update the item
								$flag = true;
								break;
							
							}
						} // end of else ( remove message)
					} // end of the contributor author loop

					if( $flag ) {

						// return the dc_contributor_authors after the edit to the metadata
						$metadata['dc.contributor.author'] = $dc_contributor_authors;

						$message = $message.''.$name;

						// add a provenance entry by adding the name to the provided message ( 'ORCID iD added to ' or 'ORCID iD removed by ')
						$metadata = appendProvenanceToMetadata($itemid, $metadata, $message, $name);

						// convert the array to json file
						$json = prepareItemMetadataAsDSpaceJSON($metadata);

						// send the array to Dspace
						$response = putItemMetadataToDSpaceRESTAPI($itemid, $json, $token);
						
						if(!is_array($response))
						{
							$changedCount++;
							
							$report .= '-- '.$message.PHP_EOL;
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
				sleep(2);
			} // end of the selected work loop 
		}
			
		return $changedCount;

	} //end of the function 
