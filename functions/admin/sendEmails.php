<?php

/*

**** This file provides a form to send emails to groups.

** Parameters :
	No parameters required
	

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 1 October 2019- 8:00 AM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

	function sendEmails()
	{
		global $ioi;
	
		$form = '';
		
		$groups = getValues($ioi, "SELECT * FROM `groups` WHERE 1", array('label','titles','titleParts'), 'arrayOfValues');
		
		$seenTitles = array();
		$seenTitleParts = array();
		
		foreach($groups as $group)
		{
			if(!empty($group['titles']))
			{
				$seenTitles[] = $group['titles'];
			}
			
			if(!empty($group['titleParts']))
			{
				$seenTitleParts[] = $group['titleParts'];
			}
		}
		$seenTitles = implode('||', $seenTitles);
		$seenTitleParts = implode('||', $seenTitleParts);
		
		$groups[] = array('label'=>'Others','titles'=>$seenTitles,'titleParts'=>$seenTitleParts);
		
		$templateTypes = array(
			'initial'=>array(
				'description'=>'Send only to people to whom no email has been previously sent',
				'emailSent'=>FALSE,
				'orcidCreated'=>FALSE,
				'permissionsGranted'=>NULL
			),
			'followup'=>array(
				'description'=>'Send only to people to whom an initial email was sent, but for whom no ORCID iD has yet been recorded',
				'emailSent'=>TRUE,
				'orcidCreated'=>FALSE,
				'permissionsGranted'=>NULL
			),
			'noPermissions'=>array(
				'description'=>'Send only to people who have an ORCID iD recorded locally, but no related access token',
				'emailSent'=>NULL,
				'orcidCreated'=>TRUE,
				'permissionsGranted'=>FALSE
			)
		);
				
		if(isset($_POST['group']) && isset($_POST['templateType']) && isset($_POST['action']))
		{
			$action = $_POST['action'];
			
			$groupKey = array_search($_POST['group'], array_column($groups, 'label'));
			$group = $groups[$groupKey];

			$template = $_POST['templateType'];
			
			if(in_array($template, array_keys($templateTypes)) && !empty($group))
			{
				$taskName = 'sendEmails_'.$group['label'].'_'.$template.'_'.$action;
				
				// init variables for reporting
				$recordTypeCounts = array('all'=>0,'sent'=>0,'failed'=>0);
				$report = '';
				$errors = array();
				
				$templateDetails = $templateTypes[$template];
				$active = TRUE;
					
				$members = getGroupMembers($group, $active, $templateDetails['emailSent'], $templateDetails['orcidCreated'], $templateDetails['permissionsGranted']);

				# send the emails
				foreach($members['list'] as $personID)
				{
					$recordTypeCounts['all']++;
					
					$result = sendEmail($personID, $template, $action);
					$recordTypeCounts[$result]++;
					
					if($action === 'test')
					{
						break;
					}
					
					sleep(20);
				}
				
				$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);
			
				# success message
				$form .= '
				<div class="alert alert-success">
					<p><b>Success</b></p>
					<p>SUMMARY: '.$summary.'</p>
				</div>
				';		
			}
			else 
			{
				# Warning message
				$form .= '
				<div class="alert alert-danger">
					<p><b>Warning</b></p>
					<p>Unknown template selected.<p>
				</div>
				';
			}
		}
		
		$form .= '
			<b>Email Invitations</b>
			<br>
			This form allows batch generation of emails to different user groups.
			<br>

			<table class="table table-bordered">
			  <tr>
				<th>Group</th> 
				<th>Invitation Type</th>
				<th>Group Size</th>
				<th>Last Sent</th>
				<th>Send Batch</th>
				<th>Test Only</th>
			  </tr>';
		
		foreach($groups as $group)
		{
			$label = $group['label'];
			
			foreach($templateTypes as $templateType => $templateDetails)
			{
				$active = TRUE;
				
				$members = getGroupMembers($group, $active, $templateDetails['emailSent'], $templateDetails['orcidCreated'], $templateDetails['permissionsGranted']);
				
				$lastSent = getValues($ioi, "SELECT timestamp FROM messages WHERE `process` = 'sendEmails_".$label."_".$templateType."_batch' ORDER BY `timestamp` DESC LIMIT 1", array('timestamp'), 'singleValue');
				
				$form .= '<tr>
					<td>'.$label.'</td>
					<td><details>
						<summary>'.$templateType.'</summary>
						<p>Description: '.$templateDetails['description'].'</p>
						</details></td>
					<td>
						<details>
						<summary>'.count($members['list']).'</summary>
						<p>Database Query Used: '.$members['query'].'</p>
						</details>
					</td>
					<td>'.$lastSent.'</td>
					<td>
						<form action="admin.php?tab=sendEmails" method="POST">
						<input type="hidden" name="group" value="'.$label.'">
						<input type="hidden" name="templateType" value="'.$templateType.'">
						<button onclick="showTheprogress()" class="btn btn-lg btn-danger" type="submit" name="action" value="batch" title="This will send emails to '.count($members['list']).' '.$label.'">Send Batch</button>
						</form>
					</td>
					<td>
						<form action="admin.php?tab=sendEmails" method="POST">
						<input type="hidden" name="group" value="'.$label.'">
						<input type="hidden" name="templateType" value="'.$templateType.'">
						<button onclick="showTheprogress()" class="btn btn-lg btn-info" type="submit" name="action" value="test" title="This will send a test email to '.IR_EMAIL.'">Test Only</button>
						</form>
					</td>
				  </tr>
				  ';
			}
		}
		$form .= '</table>'; 
		
		$form .=  '<script>
		
		// function to hide the messages
		function HideTheMessage()
		{	
			var message = document.getElementById("message");
			if(message){
				document.getElementById("message").style.display = "none";
			}
			document.getElementById("myProgress").style.display = "none";
			document.getElementById("loading").style.display = "none";
		}

		// function to show the progress bar
		function showTheprogress()
		{
			document.getElementById("myProgress").style.display = "block";
			var myInput = document.getElementById("file");
			var myInputlist  = document.getElementById("list");
			if ( ( myInput && myInput.value ) && (myInputlist && myInputlist.value) ) {
				document.getElementById("loading").style.display = "block";
			  document.getElementById("myProgress").innerHTML="<p >Sending emails, please wait ..</p>";
			}
		}

			</script>';

		return $form;
	}	
