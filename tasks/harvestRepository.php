#!/usr/bin/php-cgi

<?php

/*

**** This task checks an OAI-PMH endpoint for a list of recent items and then retrieves the full metadata for each item from the DSpace REST API and saves it to the database.

** Parameters :
	No parameters required.

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM

*/

//------------------------------------------------------------------------------------------------------------

	//assume that application home directory is the parent directory
	set_include_path(dirname(__DIR__).'/');

	//include core configuration and common function files
	include_once 'include.php';

	$taskName = 'harvestRepository';

	//Create variables for reporting process results
	$report = '';
	$errors = array();
	$changed = 0;

	// counts for reporting
	$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'skipped'=>0,'ignored'=>0,'unchanged'=>0);

	$source = 'dspace';
	$records = array();

	$dSpaceAuthHeader = loginToDSpaceRESTAPI();

	if(is_string($dSpaceAuthHeader))
	{
		if(isset($_GET['handle']))
		{
			if(isset($_GET['handle']))
			{
				$handles = array($_GET['handle']);
			}

			//print_r($handles);

			foreach($handles as $handle)
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=GetRecord&metadataPrefix=xoai&identifier='.REPOSITORY_OAI_ID_PREFIX.$handle);

				if(!empty($oai))
				{
					//print_r($oai);

					if(isset($oai->GetRecord))
					{
						foreach($oai->GetRecord->record->header as $item)
						{
							//print_r($item);

							$idInSource = str_replace(REPOSITORY_OAI_ID_PREFIX, '', (string)$item->identifier);

							$records[$idInSource] = $item;
						}
					}
				}
			}
		}
		else
		{
			$fromDate = '';

			//Set fromDate as GET variable to perform reharvest of older records
			if(!isset($_GET['fromDate']))
			{
				$fromDate = getValues($ioi, "SELECT value FROM metadata WHERE source LIKE '$source' AND field LIKE 'dspace.date.modified' ORDER BY value DESC LIMIT 1", array('value'), 'singleValue');

				//increment fromDate by 1 second so that last modified item is not reharvested
				$fromDate = substr_replace($fromDate, substr($fromDate, -3, 2)+1, -3, 2);
			}
			else
			{
				//Manually set fromDate as GET variable
				$fromDate = $_GET['fromDate'];
			}
			$report .= 'From Date: '.$fromDate.PHP_EOL;

			if(empty($fromDate))
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai');
			}
			else
			{
				$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai&from='.$fromDate);
			}
			
			//print_r($oai).PHP_EOL;

			if(isset($oai->ListIdentifiers->resumptionToken))
			{
				$total = $oai->ListIdentifiers->resumptionToken['completeListSize'];
			}
			else
			{
				$total = count($oai->ListIdentifiers->header);
			}
			unset($oai);

			while($recordTypeCounts['all']<$total)
			{
				if(!empty($resumptionToken))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&resumptionToken='.$resumptionToken.'');
				}
				elseif(empty($fromDate))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai');
				}
				elseif(!empty($fromDate))
				{
					$oai = simplexml_load_file(REPOSITORY_OAI_URL.'verb=ListIdentifiers&metadataPrefix=xoai&from='.$fromDate);
				}
				
				//print_r($oai).PHP_EOL;

				if(!empty($oai))
				{
					$report .= 'Total: '.$total.PHP_EOL;
					if(isset($oai->ListIdentifiers))
					{
						foreach($oai->ListIdentifiers->header as $item)
						{
							$recordTypeCounts['all']++;
							if($recordTypeCounts['all']===$total+1)
							{
								break 2;
							}

							$idInSource = str_replace(REPOSITORY_OAI_ID_PREFIX, '', (string)$item->identifier);

							echo $idInSource.PHP_EOL;

							//echo (string)$item['status'].PHP_EOL;
							
							//print_r($item).PHP_EOL;
							
							if((string)$item['status']==='deleted')
							{
								$report .= '- DELETED'.PHP_EOL;
								if(update($ioi, 'sourceData', array("deleted"), array(date("Y-m-d H:i:s"), $idInSource), 'idInSource'))
								{
									$recordType = 'deleted';
									//also mark all related information in metadata table with deleted timestamp
									update($ioi, 'metadata', array("deleted"), array(date("Y-m-d H:i:s"), $idInSource), 'idInSource');
								}
								else
								{
									$error = end($errors);
									$sourceReport .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
								}
							}
							else
							{
								$records[$idInSource] = $item;
							}
						}
					}
				}
				$resumptionToken = $oai->ListIdentifiers->resumptionToken;
			}
		}

		foreach($records as $idInSource => $item)
		{
			//print_r($idInSource).PHP_EOL;

			//print_r($item);

			$report .= $idInSource.PHP_EOL;			

			$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($idInSource, $dSpaceAuthHeader, 'metadata');

			if(is_string($dspaceObject))
			{
				//process item
				$recordType = processDSpaceRecord($source, $idInSource, $dspaceObject);
			}
			else
			{
				$recordType = 'skipped';

				$errors[] = array('type'=>'getDSpaceObject','message'=>$idInSource.' - error response from DSpace REST API: '.print_r($dspaceObject, TRUE));
			}

			$report .= ' - '.$recordType.PHP_EOL;

			$recordTypeCounts[$recordType]++;

			if(count($recordTypeCounts['skipped'])===5)
			{
				$report .= ' -- Exiting harvest: Repeated REST API retrieval errors.'.PHP_EOL;
				break 1;
			}

			//In some cases the DSpace REST API will begin giving internal server errors if queried continuously, so we pause between items
			sleep(10);
			flush();
			set_time_limit(0);
		}
	}
	else
	{
		$errors[] = array('type'=>'loginToDSpaceRESTAPI','message'=>' - error response from DSpace REST API: '.print_r($dSpaceAuthHeader, TRUE));
	}

	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

	echo $summary;
?>
