#!/usr/bin/php-cgi

<?php

	/*

	**** This file is responsible of updating ORCID information on DSpace records.

	** Parameters :
		No parameters required


	** Created by : Yasmeen Alsaedy
	** Institute : King Abdullah University of Science and Technology | KAUST
	** Date : 1 May 2019 - 8:30 AM

	*/

	//-----------------------------------------------------------------------------------------------------------

	//assume that application home directory is the parent directory
	set_include_path(dirname(__DIR__) . '/');

	//include core configuration and common function files
	include_once 'include.php';

	$taskName = 'updateORCIDiDsInDSpace';

	// counts for reporting
	$recordTypeCounts = array('all'=>0,'marked as new'=>0,'ORCID iD added'=>0,'marked as ignored'=>0,'ORCID iD removed'=>0,'unchanged'=>0,'skipped'=>0);

	// init report variable
	$report = '';
	$errors = array();

	// init works arrays
	$selectedWorks = array();
	$unselectedWorks = array();

	if(!isset($_GET['fromDate']))
	{
		// select timestamp of the last task message from the messages table
		$fromDate = getValues($ioi, "SELECT timestamp FROM messages WHERE `process` = '$taskName' ORDER BY `timestamp` DESC", array('timestamp'), 'singleValue');
	}
	else
	{
		// set fromDate as GET variable to run initial update
		$fromDate = $_GET['fromDate'];
	}
	$report .= 'From: '.$fromDate.PHP_EOL;

	// check if there are any new selected rows in the userSelections table ( selected works )
	$selectedWorks = getValues($ioi, "SELECT `orcid`, `localSourceRecordID` FROM `userSelections` WHERE `type` = 'work' AND `deleted` IS NULL AND `selected` > '$fromDate'", array('orcid','localSourceRecordID'), 'arrayOfValues');

	if(!empty($selectedWorks))
	{
		$message = 'ORCID iD added to ';

		//how many were marked to have ORCID iDs added
		$recordTypeCounts['marked as new'] = count($selectedWorks);
		
		//how many were actually added
		$recordTypeCounts['ORCID iD added'] = updateORCIDiDsInDSpace($selectedWorks, $message);
	}

	//check if there are any new ignored rows in the userSelections table ( unselected works )
	$unselectedWorks = getValues($ioi, "SELECT `orcid`, `localSourceRecordID` FROM `userSelections` WHERE `type` = 'work' AND `deleted` IS NULL AND `ignored` > '$fromDate'", array('orcid','localSourceRecordID'), 'arrayOfValues');

	if(!empty($unselectedWorks)) 
	{
		$message = 'ORCID iD removed by ';

		// how many were marked for removal?
		$recordTypeCounts['marked as ignored'] = count($unselectedWorks);

		// how many were successfully removed?
		$recordTypeCounts['ORCID iD removed'] = updateORCIDiDsInDSpace($unselectedWorks, $message);
	}	
	
	$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);

	echo $summary;
?>
