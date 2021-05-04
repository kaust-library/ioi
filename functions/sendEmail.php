<?php


/*

**** This function sends an email to an individual based on the requested template

** Parameters :
	$personID : id of the intended recipient.
	$templateType : name of the email template to be used.

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------
function sendEmail($personID, $template, $action)
{
	global $ioi, $report;
	
	//For testing
	if($action === 'test')
	{
		$to = IR_EMAIL;
	}
	else
	{
		$to = getValues($ioi, "SELECT value FROM `metadata` WHERE source='local' AND idInSource='$personID' AND field='local.person.email' AND deleted IS NULL", array('value'), 'singleValue');
	}

	$subject = 'ORCID at '.INSTITUTION_ABBREVIATION;
	if($template === 'followup')
	{
		$subject = 'Reminder: '.$subject;
	}
	
	$email = getValues($ioi, "SELECT template FROM `emailTemplates` WHERE label='$template'", array('template'), 'singleValue');
	
	$name = getValues($ioi, "SELECT value FROM `metadata` WHERE source='local' AND idInSource='$personID' AND field='local.person.name' AND deleted IS NULL", array('value'), 'singleValue');
	
	$givenName = explode(' ', explode(', ', $name)[1])[0];
	
	$senderGivenName = explode(' ', $_SESSION[LDAP_NAME_ATTRIBUTE])[0];
	
	$placeHolders = array(
		'givenName'=>$givenName,
		'sender'=>$senderGivenName,
		'INSTITUTION_ABBREVIATION'=>INSTITUTION_ABBREVIATION,
		'ORCID_LINK_BASE_URL'=>ORCID_LINK_BASE_URL,
		'LOCAL_TRAINING_URL'=>LOCAL_TRAINING_URL,
		'LOCAL_LIBGUIDE_URL'=>LOCAL_LIBGUIDE_URL,
		'OAUTH_REDIRECT_URI'=>OAUTH_REDIRECT_URI
	);

	foreach($placeHolders as $placeHolder => $value)
	{
		$email = str_replace('{{'.$placeHolder.'}}', $value, $email);
	}
	
	//Headers
	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";	
	$headers .= 'From: '.INSTITUTION_ABBREVIATION.' Repository<'.IR_EMAIL.'>' . "\r\n";
	$headers .= 'Cc: <'.IR_EMAIL.'>' . "\r\n";

	if(mail($to,$subject,$email,$headers))
	{
		$report .= $template.' ORCID connection email successfully sent to '.$to;
		
		$field = 'ioi.email.sent';

		$rowID = saveValue('ioi', $to, $field, 1, TODAY, NULL);
		
		return 'sent';
	}
	else
	{
		$report .= 'Error! - '.$template.' ORCID connection email failed to send to '.$to;
		
		return 'failed';
	}
}
