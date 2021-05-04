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
	set_include_path('../');

	//include core configuration and common function files
	include_once 'include.php';

	$taskName = 'harvestRepository';

	//Create variables for reporting process results
	$report = '';
	$errors = array();
	$changed = 0;

	// counts for reporting
	$recordTypeCounts = array('all'=>0,'new'=>0,'modified'=>0,'deleted'=>0,'skipped'=>0,'unchanged'=>0);

	$source = 'dspace';

	$DSpaceRESTAPIToken = loginToDSpaceRESTAPI();

	if(is_string($DSpaceRESTAPIToken))
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

						$datestamp = (string)$item->datestamp;

						$idInSource = str_replace(REPOSITORY_OAI_ID_PREFIX, '', (string)$item->identifier);

						$report .= 'Number:'.$recordTypeCounts['all'].PHP_EOL;
						$report .= 'idInSource:'.$idInSource.PHP_EOL;

						$dspaceObject = getObjectByHandleFromDSpaceRESTAPI($idInSource, $DSpaceRESTAPIToken, 'metadata');

						if(is_string($dspaceObject))
						{
							//save modified date only if object retrieved
							$rowID = saveValue($source, $idInSource, 'dspace.date.modified', 1, $datestamp, NULL);

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
							break 2;
						}

						//In some cases the DSpace REST API will begin giving internal server errors if queried continuously, so we pause between items
						sleep(10);
						flush();
						set_time_limit(0);
					}
				}
			}
			$resumptionToken = $oai->ListIdentifiers->resumptionToken;
		}
	}
	else
	{
		$errors[] = array('type'=>'loginToDSpaceRESTAPI','message'=>' - error response from DSpace REST API: '.print_r($DSpaceRESTAPIToken, TRUE));
	}

	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

	echo $summary;
?>
