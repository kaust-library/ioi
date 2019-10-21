<?php


/*

**** This file is responsible of displaying the header in the page.

** Parameters :
	No parameters required




** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------
	echo '<html>
			<head>
			<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
			<meta http-equiv="pragma" content="no-cache"/>
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta http-equiv="Content-Language" content="en-us">
			<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
			
			<!-- Bootstrap CSS -->
			<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
			
			<!-- Local changes and additions -->
			<link href="./css/orcid.local.css" rel="stylesheet" type="text/css" />
			<title>'.INSTITUTION_ABBREVIATION.'/ORCID Integration - '.$pageTitle.'</title>
			</head>';
?>	