<?php

/*

**** This file is responsible of update the user names in DSpace using ORCID iD.

** Parameters :
	No parameters required
	

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 28 August 2019 - 1:24 PM 

*/

//-------------------------------------------------------------------------------------------------------------------------------------------------



function updateNames()
{
	# set a variable
	$form = '';
	$message = '';

	if(isset($_POST['ORCID_ID']) && isset($_POST['fullname']))
	{
		$orcid = $_POST['ORCID_ID'];
		$name = $_POST['fullname'];

		# get the authority key related to this ORCID in DSpace and use it in the updateNameInDspace function
		$message = getAuthorityKey($orcid, $name);

		if($message === 'success')
		{
			# success message
			$form .= '
			<div  class="alert alert-success" role="alert id="message"  >

			<p><b>Success</b></p>
			<p>	The name has been updated. To check the update, please <a href= "https://'.REPOSITORY_BASE_URL.'/discover?filtertype_1=orcidid&filter_relational_operator_1=equals&filter_1='.$orcid.'&submit_apply_filter=&query=&scope=%2F" target="_blank">click here to see the name now shown in the repository for this ORCID iD.</a></p>

			</div>

			';
		}
		elseif($message === 'cannotUpdate') 
		{
			# Warning message
			$form .= '
			<div id="message" class="alert alert-danger" role="alert" >
			<p><b>Warning</b></p>
			<p>Can\'t update the name.<p>

			</div>

			';

		} 
		elseif ($message === 'noAuthorityKey') 
		{
			# Warning message
			$form .= '
			<div class="alert alert-warning" id="message" role="alert">
			<p><b>Warning</b></p>
			<p> This ORCID has no authority key.<p>
			</div>
			';
		}
	}

	# print the form
	$form .= '

	<br/>
	<form action="admin.php?tab=updateNames" method="POST">

	<div>
	  <p style="width:30%">
		<input class="form-control" name="ORCID_ID" placeholder="ORCID ID" onclick="HideTheMessage()" autocomplete="off" required />

	  </p>
	</div>
	<div>
	  <p style="width:30%">
		<input class="form-control" placeholder="Surname, Given name" name="fullname" onclick="HideTheMessage()" autocomplete="off" required />
	  </p>
	</div>
	<div>
	  <p>
		<button class="btn btn-info" type="submit"><b>Update</b></button>
	  </p>
	</div>

	</form>
	<p>* This tool will update the names in <u>DSpace ONLY</u></p>

	<script>

	function HideTheMessage()
	{
		document.getElementById("message").style.display = "none";
	}

	</script>';

	return $form;
}
