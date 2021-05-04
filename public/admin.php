<?php
/*

**** This file is the admin page for the tool.

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

		include 'snippets/html/header.php';

		include 'snippets/login.php';

	}
	else
	{
		$pageTitle = ' - Admin Page';

		include 'snippets/html/header.php';

		include 'snippets/html/startBody.php';

		$activeTab = '';
		if(isset($_GET['tab']))
		{
			$activeTab = $_GET['tab'];
		}
		else
		{
			$activeTab = 'dashboard';
		}

		$tabs = array('dashboard'=>'Dashboard','sendEmails'=>'Send Emails', 'updateNames'=>'Update Names', "UploadFiles" => "Upload Files");

		echo '<nav><div class="nav nav-tabs" id="nav-tab" role="tablist">';

		foreach($tabs as $tab => $label)
		{
			if($activeTab === $tab)
			{
				$status[$tab] = 'show active';
			}
			else
			{
				$status[$tab] = '';
			}

			echo '<a class="nav-item nav-link '.$status[$tab].'" id="nav-'.$tab.'-tab" data-toggle="tab" href="#nav-'.$tab.'" role="tab">'.$label.'</a>';
		}

		echo '</div></nav><div class="tab-content" id="nav-tabContent">';

		foreach($tabs as $tab => $label)
		{

			echo '<div class="tab-pane fade '.$status[$tab].'" id="nav-'.$tab.'" role="tabpanel" aria-labelledby="nav-'.$tab.'-tab" id="'.$tab.'">'.$tab().'</div>';
		}

		echo '</div>';

		include 'snippets/html/footer.php';
	}
