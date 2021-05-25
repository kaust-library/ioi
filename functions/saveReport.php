<?php

/*

**** This file is responsible for saving reports in the ioi database.

** Parameters :
	$taskName : a string value that describe the task that made the report.
	$report : a string text containing the detailed log of the task.
	$recordTypeCounts : count of the results for each record processed in the task.
	$errors : array of errors.

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
		//Log full task report
		insert($ioi, 'messages', array('process', 'type', 'message'), array($taskName, 'report', $report));

		if($taskName !== 'query')
		{
			$sendEmail = FALSE;
			
			//Log task summary
			insert($ioi, 'messages', array('process', 'type', 'message'), array($taskName, 'summary', $summary));

			if(EMAIL_REPORT_LEVEL === 'allChanges')
			{
				$sendEmail = TRUE;
			}
			elseif(count($errors)!==0 && EMAIL_REPORT_LEVEL === 'errorsOnly')
			{
				$sendEmail = TRUE;
			}
			
			if($sendEmail)
			{
				//Settings for report email
				$to = IR_EMAIL;
				$subject = "Results of $taskName for IOI";

				//Complete  message to send
				$summary = $taskName.' Report'.PHP_EOL.$summary;

				$headers = "From: " .IR_EMAIL. "\r\n";

				//Send
				mail($to,$subject,$summary,$headers);
			}
		}
	}

	return $summary;
}
