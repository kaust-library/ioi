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
	$result = $ioi->query("SELECT `scope`, `access_token`, `created` FROM `tokens` WHERE `orcid` = '$orcid' AND `deleted` IS NULL order by `created` DESC ");
	$oldscopes = mysqli_fetch_all($result);
	
	//print_r($oldscopes);
		
	//print_r($_SESSION);

	if(!empty($oldscopes) && $oldscopes[0][0] === $_SESSION['scope']){
		
		//echo ' No Change';
		
		// if the user's new scope is equals the old scope no change  made
		
	}
	else{
		
		// if the user's changed the scope mark the old one as deleted and tell orcid to revoke the token, then insert the new scope to the local table 
				
		foreach($oldscopes as $oldscope) {
    	
			// mark the old token as deleted 
			$ioi->query("UPDATE `tokens` SET `deleted`= '".date("Y-m-d H:i:s")."' WHERE `access_token` = '".$oldscope[1]."'");

			// revoke the token on the ORCID side
			revokeTokens(OAUTH_CLIENT_ID, OAUTH_CLIENT_SECRET, $oldscope[1]);
			
		}
		
		$datafromORCID = getNewAccessToken();
		$orcid = $datafromORCID[0];
		$access_token = $datafromORCID[1];
		$expires_in = $datafromORCID[2];
		$scope = $datafromORCID[3];
		$name = $datafromORCID[4];
		$refresh_token = $datafromORCID[5];
		$today = $datafromORCID[6];
		$expiration = $datafromORCID[7];

		// insert if the scope is new or changed
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