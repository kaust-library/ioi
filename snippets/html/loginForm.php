<?php

/*

**** This file is responsible of displaying the login form in the page.

** Parameters :
	No parameters required




** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------


if(empty($pageTitle)){
	$pageTitle = '';
}
	$loginForm =
		'<html lang="en">
		<head>

		<meta http-equiv="X-UA-Compatible" content="IE=9; IE=EDGE" />
		<meta charset="UTF-8">
		<title>'.$pageTitle.'</title>
		<link rel="stylesheet" href="./css/loginForm.css">

		</head>

		<body>
		<div class="logo">
		<img src="./images/logo.png" alt="'.INSTITUTION_ABBREVIATION.' logo" title="'.INSTITUTION_ABBREVIATION.' logo" />
		</div>

		<div class="title">
		<span>'.$pageTitle.'</span>
		</div>

		<div class="login">
		<form action="" method="post">
		<input type="text" placeholder="'.INSTITUTION_ABBREVIATION.' username" name="username" id="username" value="'.$username.'">
		<div class="help-tip">
				<p>Use your '.INSTITUTION_ABBREVIATION.' username</p>
		</div>
		<input type="password" placeholder="password" name="password" id="password">';
		
	if ($error_msg != '')
	{
		$loginForm .= '<div class="alert-box error"><span>error: </span>'.$error_msg.'</div>';
	}
			  
	$loginForm .= '<input type="submit" value="Sign In">
		</form>
		</div>
		<div class="shadow"></div>	

		<div class="text">
			For training and questions about ORCID, please visit <a href="'.LOCAL_LIBGUIDE_URL.'" target="_blank">the ORCID libguide</a> or contact <a href="mailto:'.IR_EMAIL.'">'.IR_EMAIL.'</a>.
		  </div>		

		<script>
		document.getElementById( "username" ).focus();
		</script>

		</body>
		</html>';
		
	echo $loginForm;	
?>	