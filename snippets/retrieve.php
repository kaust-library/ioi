<?php

/*

**** This file is responsible of checking the user's token.

** Parameters :
	No parameters required

** Created by : Daryl Grenz and Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//-----------------------------------------------------------------------------------------------------------

// get the last scope for the user
$oldscopes = getValues($ioi, "SELECT `scope`, `access_token`, `created` FROM `tokens` WHERE `orcid` = '$orcid' AND `deleted` IS NULL order by `created` DESC", array('scope','access_token','created'), 'arrayOfValues');

if(!empty($oldscopes) && $oldscopes[0]['scope'] === $_SESSION['scope'])
{
	//echo ' No Change';
	
	// if the user's new scope equals the old scope no change is made
}
else
{	
	// if the user changed the scope mark the old one as deleted and tell ORCID to revoke the token, then insert the new scope to the local table
			
	foreach($oldscopes as $oldscope)
	{	
		// mark the old token as deleted 
		$ioi->query("UPDATE `tokens` SET `deleted`= '".date("Y-m-d H:i:s")."' WHERE `access_token` = '".$oldscope['access_token']."'");

		// revoke the token on the ORCID side
		revokeTokens(OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET, $oldscope['access_token']);		
	}
	
	$response = getNewAccessToken();
	
	//get the information from response
	$orcid = $response['orcid'];
	$access_token = $response['access_token'];
	$expires_in = $response['expires_in'];
	$scope = $response['scope'];
	$name = $response['name'];
	$refresh_token = $response['refresh_token'];
	
	//prepare other variables
	$today = date('Y-m-d H:i:s');
	$expiration = date('Y-m-d H:i:s', time()+$expires_in);
	
	$email = $_SESSION[LDAP_EMAIL_ATTRIBUTE];
	$personID = $_SESSION[LDAP_PERSON_ID_ATTRIBUTE];
	
	// New user ?
	$check = $ioi->query("SELECT * FROM `orcids` WHERE localPersonID = '$personID'");
			
	if($check->num_rows === 1)
	{
		//update the name and the orcid for the user
		$ioi->query("UPDATE orcids SET orcid = '$orcid', name = '$name' WHERE localPersonID = '$personID'");
	}
	else
	{
		// insert the data from the new user
		$ioi->query("INSERT INTO orcids (email, orcid, name, localPersonID) VALUES ('$email', '$orcid', '$name', '$personID')");
	}

	// save token if the scope is new or changed
	$ioi->query("INSERT INTO tokens (access_token, expiration, scope, orcid, name, created, refresh_token) VALUES ('$access_token', '$expiration', '$scope', '$orcid', '$name', '$today', '$refresh_token')");

	//Settings for repository notification email
	$to = IR_EMAIL;
	$subject = "ORCID created/updated for ".$name;
	
	//Create message to send
	$message = '
		<html>
		<body>
		<hr><p><b><u>ORCID created/updated for: '.$name.'</u></b><br>ORCID: '.$orcid.'<br> - Link: '.ORCID_LINK_BASE_URL.$orcid.'<br> - Scopes: '.$scope.'<br> - Access Token: '.$access_token.'<br> - Expiration: '.$expiration.'</p><hr> 
		</body>
		</html>
		';

	// Always set content-type when sending HTML email
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

	// More headers
	$headers .= 'From: <'.IR_EMAIL.'>' . "\r\n";
	
	//Send to repository
	mail($to,$subject,$message,$headers);
}

?>