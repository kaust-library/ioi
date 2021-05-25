<?php

/*

**** This function is responsible of displaying the work and affiliation selection form.

** Parameters :
	$works : all the works that match the user name.
	$affiliations : all the affiliations that match the user name.
	$accessToken : unique token for each user from ORCID.
	$review : if the user clicks on the review button its value will be "yes".	

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 April 2019- 11:30 AM 

*/

//------------------------------------------------------------------------------------------------------------

function displaySelectionForm($works, $affiliations, $accessToken, $review = 'no')
{
	$form = '<form action="'.$_SERVER['REQUEST_URI'].'" method="post">';

	//if no matching works
	if(empty($works))
	{
		if(ORCID_MEMBER)
		{
			$form .= '<br><h2><b>Publications</b></h2>
				<li>There are no existing work records in the '.INSTITUTION_ABBREVIATION.' repository which match your name, so no work records are available for review. When new records are added to the '.INSTITUTION_ABBREVIATION.' repository with your ORCID iD or your name, they will automatically be added to your ORCID record. If changes are needed, you can manage your works list by returning to this page.</li>';
		}
		else
		{
			$form .= '<br><h2><b>Publications</b></h2>
				<li>There are no existing work records in the '.INSTITUTION_ABBREVIATION.' repository which match your name, so no work records are available for review. When new records are added to the '.INSTITUTION_ABBREVIATION.' repository with your ORCID iD or your name, you can review them by returning to this page.</li>';
		}		
	}
	else
	{
		$form .= '
		<h2><b>Publications</b>  <img src="./images/QuestionMark2.png" title="Some items may be listed that have a name match with another person whose name is similar to yours, please uncheck any items for which you are not an author." style ="height: 30px;width: 35px;" /></h2>
		<br>

		<table class="table table-bordered">
		  <tr>
			<th></th>
			<th>Title</th> 
			<th>Journal</th>
			<th>Type</th>
			<th>Publication Date</th>
			<th>URL</th>
		  </tr>';

		foreach($works as $work)
		{
			$checked = '';
			
			if($work['selected'])
			{
				$checked = 'checked';
			}

			$form .= '<tr>
			<td><input type="checkbox" name="selectedworks[]" value="'.$work['idInSource'].'" '.$checked.'></td>
			<td>'.$work['title'].'</td> 
			<td>'.$work['journal-title'].'</td>
			<td>'.$work['type'].'</td>
			<td>'.$work['publication-date'].'</td>
			<td><a href="'.$work['url'].'">'.$work['url'].'</a></td>
		  </tr>
		  ';
		}
		$form .= '</table>';
	}
	//------------------------------------------------------------------------------------------------------------------

	// If non-ORCID member the affiliations array will be empty
	if(count($affiliations) !== 0)
	{		
		$form .= '<br> 
		<h2><b>Affiliation</b>  <img src="./images/QuestionMark1.png" title="The selected information will be sent to ORCID. If there is any information that you don\'t want to send, please uncheck it." style ="height: 30px;width: 35px;" /></h2>
		<br>
		<table class="table table-bordered">
		  <tr>
			<th></th>
			<th>Role Title</th> 
			<th>Department Name</th> 
			<th>Start Date</th>
			<th>End Date</th>
		  </tr>';

		foreach($affiliations as $affiliation)
		{
			$checked = '';
			
			if($affiliation['selected'])
			{
				$checked = 'checked';
			}

			$form .= '<tr>
				<td><input type="checkbox" name="selectedaffiliations[]" value="'.$affiliation['fields']['localSourceRecordID'].'" '.$checked.'></td>
				<td>'.$affiliation['fields']['role-title'].'</td>					
				<td>'.$affiliation['fields']['department-name'].'</td>
				<td>'.$affiliation['dates']['start-date'] .'</td><td>'.$affiliation['dates']['end-date'].'</td></tr>';					
		}

		$form .= '</table>';
	}

//------------------------------------------------------------------------------------------------------------------

	if($review == 'yes'){
		$form .= '<input type="hidden" name="review" value="yes">';
	}

	// show submit button if there were entries listed to review
	if( count($affiliations) != 0 || count($works) != 0 )
	{
		// submit hidden value just if the user unseleted all the items the array will be not empty
		$form .= '<input type="hidden" name="selectedworks[]" value="">
		<input type="hidden" name="selectedaffiliations[]" value="">
		<br>
		<input type="submit" class="btn btn-secondary btn-lg btn-block" value="Submit"></form>';
	}
	
	$form .= '<hr><h5>If any information needs correction, please contact us via email at <a href="mailto:'.IR_EMAIL.'">'.IR_EMAIL.'</a> for assistance.</h5>';

//------------------------------------------------------------------------------------------------------------------

	return $form;
}
