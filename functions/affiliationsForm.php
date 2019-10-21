<?php
/*

**** This funcation is responsible of displaying the the affiliation form. 

** Parameters :
	$affiliations : array of the user affiliations.
	$accessToken :  unique token for each user from ORCID.
	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 April 2019- 11:30 AM 

*/


//------------------------------------------------------------------------------------------------------------
	function affiliationsForm($affiliations, $accessToken)
	{
	
		$checked = '';
		$form = '



			<!DOCTYPE html>
			<html>
			<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<style>
			#more {display: none;}
			</style>
			</head>
<body>
		<div class="jumbotron" >
	<h2>Affiliation</h2>
	<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
	<table class="table table-bordered">
	  <tr>
		<th></th>
		<th>Role Title</th> 
		<th>department-name</th> 
		<th>Start Date</th>
		<th>End Date</th>
	  </tr>';




	foreach ($affiliations[0] as $affiliation) {

		
		
		if($affiliation['seleted'])
			$checked = 'checked';
		else
			$checked = '';


			$form .= '<tr>
			<td><input type="checkbox" name="selectedaffiliation[]" value="'.$affiliation['fields']['localSourceRecordID'].'" '.$checked.'></td>
			<td>'.$affiliation['fields']['role-title'].'</td> ';


		// because from ldap usully they didn't have the department name dn date or personOrgRelation

		if(isset($affiliation['fields']['department-name']))
			$form .= '<td>'.$affiliation['fields']['department-name'].'</td> ';
		else
			$form .= '<td>  </td> ';


		if(isset($affiliation['dates']))
			$form .= '<td>'.$affiliation['dates']['start-date'] .'</td><td>'.$affiliation['dates']['end-date'].'</td>
	  		';
	  	else
	  			$form .= '<td>  </td><td>  </td>';

	}


	// submit hidden value just if the user unseleted all the items the array will be not empty
	$form .= '</tr><input type="hidden" name="selectedaffiliation[]" value=""></table></div> <input type="submit" value="Next"></form>



</body>
</html>
';




	echo $form;



}
