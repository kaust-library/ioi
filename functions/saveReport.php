<?php


/*

**** This file is responsible of saving the reports in ioi datbase.

** Parameters :
	$taskName : a string value that describe the task that made.
	$report : a string text contain the detail of the task.
	$recordTypeCounts : number of the recodes in the task.
	$errors : if any error appear when the task run.


** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------
function saveReport($taskName, $report, $recordTypeCounts, $errors)
{
	global $ioi;
	
	$summary = $taskName.':'.PHP_EOL;
	
	foreach($recordTypeCounts as $type => $count)
	{
		$summary .= ' - '.$count.' '.$type.' items'.PHP_EOL;
	}
	
	$summary .= ' - Error count: '.count($errors).PHP_EOL;
	
	foreach($errors as $error)
	{
		$report .= ' - '.$error['type'].' error: '.$error['message'].PHP_EOL;
	}
	
	$report .= PHP_EOL.$summary;
	
	if($recordTypeCounts['all']!==0||count($errors)!==0)
	{		
		//Log task summary
		insert($ioi, 'messages', array('process', 'type', 'message'), array($taskName, 'summary', $summary));
		
		//Log full task report
		insert($ioi, 'messages', array('process', 'type', 'message'), array($taskName, 'report', $report));

		//Settings for report email
		$to = IR_EMAIL;
		$subject = "Results of $taskName for IOI";

		//Complete  message to send
		$summary = $taskName.' Report'.PHP_EOL.$summary;

		$headers = "From: " .IR_EMAIL. "\r\n";

		//Send
		mail($to,$subject,$summary,$headers);
	}
	
	return $summary;
}
