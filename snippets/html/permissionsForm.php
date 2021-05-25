<?php 
/*

**** This file is responsible for displaying the permissions form.

** Parameters :
  No parameters required

** Created by : Daryl Grenz
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019 - 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

echo '
 <div id="accordion">
 ';

if(!empty($orcid))
{
	echo '<br>
	<p style="font-size:20px;">Your <u>ORCID ID</u> has been identified as:<br><a href="'.ORCID_LINK_BASE_URL.$orcid.'" target="_blank"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon" />'.ORCID_LINK_BASE_URL.$orcid.'</a><p>';
}

echo'
  <div class="card bg-light">
    <div class="card-header" id="headingOne">
      <h5 class="mb-0">
        <button class="btn btn-link" data-toggle="collapse" data-target="#collapseOne" aria-expanded="tue" aria-controls="collapseOne" style="font-size: 20px;">
          Manage Permissions
        </button>
      </h5>
    </div>

    <div id="collapseOne" class="collapse show" aria-labelledby="headingOne" data-parent="#accordion">
      <div class="card-body">';      

if(!empty($orcid))
{
	if(!empty($scope))
	{
		echo 'You have already established the connection between the '.INSTITUTION_ABBREVIATION.' Repository and your ORCID record.<br><br>The permissions you have granted to the '.INSTITUTION_ABBREVIATION.' Repository are checked below.';
		
		if(ORCID_MEMBER)
		{
			echo '<u>If you make changes to these permissions, please select "Confirm permissions change with ORCID" to complete the update.</u>';
		}
	}
	else
	{   
		echo '<br><b>You have not yet granted permissions to the '.INSTITUTION_ABBREVIATION.' Repository to connect to your ORCID record.</b><br><br>';
	}
}

if(empty($scope))
{
	echo 'Please select the permissions you would like to grant to the '.INSTITUTION_ABBREVIATION.' Repository from the list below before connecting to the ORCID system.';
}
echo '<br><br>
<form>';

$nameParts = explode(' ', $displayName);

//set variables for hidden input fields
echo '<input type="hidden" name="family_names" value="'.array_pop($nameParts).'">';   
echo '<input type="hidden" name="given_names" value="'.implode(' ', $nameParts).'">';

if(!empty($orcid))
{
	echo '<input type="hidden" name="orcid" value="'.$orcid.'">';
}

echo '<input type="hidden" name="email" value="'.$_SESSION[LDAP_EMAIL_ATTRIBUTE].'">
<input type="hidden" name="identify" value="yes">';

echo '<div class="form-check">
  <input class="form-check-input" type="checkbox" name="identify" value="yes" checked="checked" disabled>
  <label class="form-check-label">Create a new ORCID ID or identify an existing one (required).
  </label></div>';

if(ORCID_MEMBER)
{
	if(!empty($scope))
	{
		$buttonLabel = 'Confirm permissions change with ORCID';

		echo '<div class="form-check">
			<input class="form-check-input" type="checkbox" name="read" value="yes"'; 
			  if(strpos($scope, '/read-limited')!==FALSE)
			  {
			  echo 'checked="checked"';
			  }
			  echo '><label class="form-check-label"> Read information from my ORCID record.</label>
			</div>
		  
			<div class="form-check">
			  <input class="form-check-input" type="checkbox" name="addActivities" value="yes" ';
			  
			  if(strpos($scope, '/activities/update')!==FALSE || strpos($scope, '/orcid-works/create /affiliations/update /affiliations/create /orcid-works/update')!==FALSE)
			  {
			  echo 'checked="checked"';
			  }
			  
			  echo '><label class="form-check-label"> Add information about my '.INSTITUTION_ABBREVIATION.' affiliation and publications in the '.INSTITUTION_ABBREVIATION.' Repository to my ORCID record.</label>
		  </div>';
	}
	else
	{
		echo '<div class="form-check">
			<input class="form-check-input" type="checkbox" name="read" value="yes" checked="checked"><label class="form-check-label"> Read information from my ORCID record.</label>
			</div>
		  
			<div class="form-check">
			  <input class="form-check-input" type="checkbox" name="addActivities" value="yes" checked="checked"><label class="form-check-label"> Add information about my '.INSTITUTION_ABBREVIATION.' affiliation and publications in the '.INSTITUTION_ABBREVIATION.' Repository to my ORCID record.</label>
		  </div>';
	}
}

//Button label changes depending on if permissions are being changed or being granted for the first time
if(!isset($buttonLabel))
{
	$buttonLabel = 'Register or connect your ORCID iD';
}

echo '<br><p>You can change these permissions at any time in the Accounts Settings section of your ORCID record or by returning to this page.</p>
<button type="submit" id="connect-orcid-button"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>'.$buttonLabel.'</button></form></div>
</div>
</div>';

if(!empty($orcid))
{
	//display the history card
	echo history($localPersonID);
}
echo '</div>';
?>