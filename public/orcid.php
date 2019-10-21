<?php
/*

**** This file is the main page for the tool.

** Parameters :
	No parameters required


** Created by : Daryl Grenz and Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April - 10:30 AM

*/

//--------------------------------------------------------------------------------------------
	header('Content-Type: text/html; charset=UTF-8');
	//This allows users to use the back button smoothly without receiving a warning from the browser
	header('Cache-Control: no cache');
	session_cache_limiter('private_no_expire');

	//assume that application home directory is the parent directory
	set_include_path('../');

	//include core configuration and common function files
	include_once 'include.php';

	//initialize the session.
	session_start();

	if(!isset($_SESSION["step"])){

		// add new parameter to the session so when the user refresh the page it will change
		$_SESSION["step"] = 'checkWorks';
	}

	if(isset($_GET['action']))
	{
		if($_GET['action']==='logout')
		{
			if(session_id() !== '')
			{
				$_SESSION = array();

				if (ini_get("session.use_cookies"))
				{
					$params = session_get_cookie_params();
					setcookie(session_name(), '', time() - 42000,
						$params["path"], $params["domain"],
						$params["secure"], $params["httponly"]
					);
				}

				session_destroy();
			}
		}
	}

	// check for authenticated user
	if(!isset($_SESSION['username']))
    {
		$pageTitle = 'ORCID at '.INSTITUTION_ABBREVIATION.' - Login Page';

		include_once 'snippets/html/header.php';

		include_once 'snippets/login.php';

	}
	else
	{

		$orcid = '';
		$scope = '';
		// take the local person id and the displayed name from the session
		$localPersonID =  $_SESSION[LDAP_PERSON_ID_ATTRIBUTE];
		$displayname = $_SESSION[LDAP_NAME_ATTRIBUTE];

		//Check for known ORCID by email
		$orcid = getValues($ioi, "SELECT `orcid` FROM `orcids` WHERE `email` LIKE '".$_SESSION[LDAP_EMAIL_ATTRIBUTE]."'", array('orcid'), 'singleValue');

		// get the access token if the user has one
		$access_token = getAccessTokenFromDB($orcid);

		//Check for latest token
		$check = $ioi->query("SELECT * FROM `tokens` WHERE orcid LIKE '$orcid' ORDER BY created DESC LIMIT 0,1");

		if($check->num_rows===1)
		{
			$row = $check->fetch_assoc();

			$scope = $row['scope'];
		}

		if(!empty($scope) )
		{
			if(isset($_POST['review']))
			{
				$pageTitle = ' - Review';
			}
			else
			{
				$pageTitle = ' - Manage Permissions';
			}
		}
		elseif(!empty($orcid))
		{
			$pageTitle = ' - Connect your ORCID to '.INSTITUTION_ABBREVIATION;
		}
		else
		{
			$pageTitle = ' - Create or Identify your ORCID ID';
		}



		include_once 'snippets/html/header.php';

		include 'snippets/html/startBody.php';

		// when user first arrives present a form
		if(!isset($_GET['code']) && !isset($_GET['identify']) && !isset($_POST['review']))
		{

			include 'snippets/html/permissionsForm.php';

		}

		// redirect the user to approve the application
		elseif(!isset($_GET['code'])&&isset($_GET['identify']))
		{
			include 'snippets/approve.php';
		}

		// code is returned, with invalid state
		elseif(isset($_GET['code'])&&(!isset($_GET['state'])||!isset($_COOKIE['oauth_state'])) && !isset($_POST['review']) ) {
		  echo '<p>Invalid state. Your session may have timed out. Please try again by reopening the link you received by email or visiting <a href="'. OAUTH_REDIRECT_URI . '">'.OAUTH_REDIRECT_URI.'</a>. If the problem persists email <a href="'.IR_EMAIL.'">'.IR_EMAIL.'</a> for assistance.</p>';
		}

		// if the user clicks on review button
		elseif(isset($_POST['review']) && empty($_POST['selectedworks']) && empty($_POST['selectedaffiliation']) ){

			// Add affiliation and works if permission granted
			if(strpos($scope, '/activities/update')!==FALSE || strpos($scope, '/orcid-works/create /affiliations/update /affiliations/create /orcid-works/update')!==FALSE )
			{

				// get array of work ( and if it's selected make a flag)
				$works = getAllWorks($orcid, $localPersonID, $displayname);
				$_SESSION['works'] = $works;

				$affiliations = getAllAffiliations($orcid, $localPersonID);

				// display one form for both works and affiliations
				displayForm($works, $affiliations, $access_token, 'yes');

			}
			else{

				// if the user didn't give us the right permissions yet
				$pageTitle = ' ORCID at '.INSTITUTION_ABBREVIATION.' - Review';
				echo '<div class="alert-warning p-4"><br><p><b>You have not yet granted the appropriate permissions.</b></p>
					<p>To allow and manage the transfer of publications and affiliations to your ORCID record, you must check the below options in the Manage Permissions form:
					<ul>
					<li> Read information from my ORCID record.</li>
					<li> Add information about my '.INSTITUTION_ABBREVIATION.' affiliation and publications in the '.INSTITUTION_ABBREVIATION.' Repository to my ORCID record.</li>
					</ul>
					 Please make and confirm the changes to proceed.
					</p>
					<p> Thank you!</a></p></div>';

				include 'snippets/html/permissionsForm.php';
			}
		}

		// code is returned, with valid state
		elseif(isset($_GET['code']) && ( $_GET['state'] == $_COOKIE['oauth_state'])  && empty($_POST['selectedworks']) && empty($_POST['selectedaffiliation']) )
		{

			//print_r($_SESSION);

			if( $_SESSION["step"] == 'checkWorks' ) {

				// retrieve access tokens from ORCID
				include 'snippets/retrieve.php';

			}

			$_SESSION["step"] = 'home';

			// Add affiliation and works if permission granted
			if(strpos($scope, '/activities/update') !== FALSE )
			{

				// get array of work ( and if it's selected make a flag)
				$works = getAllWorks($orcid, $localPersonID, $displayname) ;
				$_SESSION['works'] = $works;
				$affiliations = getAllAffiliations($orcid, $localPersonID);

				// display one form for both works and affiliations
				displayForm($works, $affiliations, $access_token);

			}
			else{

				// inform user of their ORCID
				echo '<br>
						<p>Thank you!</p>
						<p>Your ORCID is <a href="'.ORCID_LINK_BASE_URL . $orcid . '"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>'.ORCID_LINK_BASE_URL . $orcid . '</a>.</p>';

				//Offer suggestions for additional steps
				include 'snippets/html/suggestions.php';

				echo '
					<br>
						<form method="post" action="'.OAUTH_REDIRECT_URI.'">
						<input type="Submit" name="Submit" id="button" class="btn btn-secondary btn-lg" style="float:right;font-size:20px;font-style: normal;"" value="Main Page" />
					</form>';

				// change the session data when the user returns to the main page
				$_SESSION["step"] = 'checkWorks' ;

			}
		}

		//if the user has posted the review form
		elseif(!empty($_POST["selectedworks"]) && count($_POST["selectedworks"]) != 0 || !empty($_POST["selectedaffiliation"]) && count($_POST["selectedaffiliation"]) != 0 ){

			//if works selected
			if(!empty($_POST["selectedworks"]) && count($_POST["selectedworks"]) != 0){

				// take the selected work from the session
				$works = $_SESSION['works'];

				// the handles
				$selectedWorksHandles = $_POST['selectedworks'];

				$unselectedWorksHandles = array();

				// all the works
				$selectedWorks = array();
				$unselectedWorks = array();

				//get the unselected work to send it to the unselected work function
				foreach ($works as $work) {
					if(!in_array($work['idInSource'], $selectedWorksHandles)){

						// push to the array
						array_push($unselectedWorks, $work);
						array_push($unselectedWorksHandles, $work['idInSource']);

					}
					else{

						array_push($selectedWorks, $work);

					}
				}

				 //send the selected works to the orcid system
				if(count($works) != 0 ) {

					addWorks($orcid, $selectedWorks, $access_token);
					unselectedWork($orcid, $unselectedWorksHandles, $access_token);

				}
			}

			if(!empty($_POST["selectedaffiliation"]) && count($_POST["selectedaffiliation"]) != 0 ){

				// get all the affiliations
				$affiliations = getAllAffiliations($orcid, $localPersonID);

				// get the selected ids
				$selectedPersonOrgRelation = $_POST['selectedaffiliation'];

				//create new arrays
				$unselectedAffiliations = array();
				$selectedAffiliations = array();

				// get the unselected work to send it to the unselected work function
				foreach ($affiliations as $affiliation) {

					if(!in_array($affiliation['fields']['localSourceRecordID'], $selectedPersonOrgRelation)){

						// push to the array ( I did this because I don't want to query the result again )
						array_push($unselectedAffiliations, $affiliation);

					}
					else{

						array_push($selectedAffiliations, $affiliation);

					}
				}

				//send the selected affiliation to the orcid system
				if(count($affiliations) != 0 ) {

					addAffiliation($orcid, $selectedAffiliations, $localPersonID, $access_token);
					unselectedAffiliation($orcid, $unselectedAffiliations, $access_token, $localPersonID);
				}
			}

			$_SESSION["step"] = 'checkWorks' ;
			// add the main page button

			echo '
			<br>
			<form method="post" action="'.OAUTH_REDIRECT_URI.'">
			<input type="hidden" name="checkWorks" />
				<input type="Submit" name="Submit" id="button" class="btn btn-secondary btn-lg" style="float:right;font-size:20px;font-style: normal;"" value="Main Page" />

				</form>
				';

			echo '<p>* There will be a delay before the changes will be reflected in the repository.</p>';

		} // end of if the user has works or affiliations statement

		else{

			// change the session data when the user returns to the main page
			$_SESSION["step"] = 'checkWorks';

			echo (isset($_GET['code']) && ( $_GET['state'] == $_COOKIE['oauth_state'])  && empty($_POST['selectedworks']) && empty($_POST['selectedaffiliation']));

			// inform user of their ORCID
			echo '<br>
					<p>Thank you!</p>
					<p>Your ORCID is <a href="'.ORCID_LINK_BASE_URL . $orcid . '"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>'.ORCID_LINK_BASE_URL . $orcid . '</a>.</p>';

			//Offer suggestions for additional steps
			include 'snippets/html/suggestions.php';

			echo '
				<br>
				<form method="post" action="'.OAUTH_REDIRECT_URI.'">
					<input type="hidden" name="checkWorks" />
					<input type="Submit" name="Submit" id="button" class="btn btn-secondary btn-lg" style="float:right;font-size:20px;font-style: normal;"" value="Main Page" />
				</form>';
		}
		include 'snippets/html/footer.php';
	}

	//For development - uncomment to see the contents of the session
	//print_r($_SESSION);
?>
