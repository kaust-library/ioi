<?php

/*

**** This file is responsible of displaying the body in the page.

** Parameters :
	No parameters required




** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

	echo '<body>
			<div class="container">
				<header>
					<div class="masthead">
						<a href="'.OAUTH_REDIRECT_URI.'" title="ORCID at '.INSTITUTION_ABBREVIATION.''.$pageTitle.'">
							<img style="margin:20px" alt="'.INSTITUTION_ABBREVIATION.'" src="./images/logo.png">
						</a>';
					
	if(isset($_SESSION['admin']))
	{	
		if($_SESSION['admin'])
			echo '<a style="float:right" href="admin.php" type="button" class="btn btn-primary rounded">Admin</a>';
	
						
	echo '			</div>
					<span style="float:right; font-size:20px;">Logged in as '.$_SESSION[LDAP_NAME_ATTRIBUTE].' (<a href="orcid.php?action=logout">logout</a>)</span><br><br>
				</header>
				<!-- Main Content for Page -->
				<div class="jumbotron">
				<h2>ORCID at '.INSTITUTION_ABBREVIATION.''.$pageTitle.'</h2>';
	}else{

			echo '			</div>
					
				</header>
				<!-- Main Content for Page -->
				<div class="jumbotron">
				<h2>ORCID at '.INSTITUTION_ABBREVIATION.''.$pageTitle.'</h2>';
	}
?>	