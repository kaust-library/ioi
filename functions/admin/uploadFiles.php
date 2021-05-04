<?php

/*

**** This file is responsible of adding local person and organizational data to the database via file upload.

** Parameters :
	No parameters required	

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 1:24 PM 

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------

function uploadFiles()
{
	# set a variable
	$form = '';
	
	# labels and fields for each accepted file
	$acceptedUploads = array(
		array('file'=>"OrgHierarchy",'label'=>"Organisation Hierarchy",'fields'=>array('Parent_Organisation_ID', 'Child_Organisation_ID')),
		array('file'=>"OrgData",'label'=>"Organisation Data",'fields'=>array('Organisation_ID', 'Name', 'Type', 'Start_Date', 'End_Date')),
		array('file'=>"PersonsData",'label'=>"Persons Data",'fields'=>array('PERSON_ID', 'LAST_NAME', 'FIRST_NAME', 'MIDDLE_NAME', 'LOCAL_PERSON_ID', 'EMAIL')),
		array('file'=>"PersonOrgRelations",'label'=>"Person Organisation Relations",'fields'=>array('PERSON_ID', 'ORG_ID', 'JOB_TITLE', 'START_DATE', 'END_DATE'))
	);

	# html to upload the files
	$form .= '
	<style>

	.form-control{

		width: 70%;
		color:#000000;
		font-size:15px;
	}

	.form-group{
		width:30%;
		padding-top:10px;
	}

	.submit{
		
		margin:10px 0px 0px 0px;
		width:8%;
		height:4%;
		font-size:80%;
	}

	.btn{
		left:30px;
	}
	</style>

	<h1 class="h3 mb-3 font-weight-normal" style="padding-top:10px"></h1>

	<div class="container">
		<form action="admin.php?tab=UploadFiles" method="post" name="CsvImport" id="CsvImport" enctype="multipart/form-data" required>
		  <div class="row">

			<div class="col">
				 <input onclick="HideTheMessage()" type="file" name="csvfile"
				 id="file" accept=".csv" required>

			</div>

			<div class="col">
				<select name="UploadFile" class="form-control form-control-sm" id="list" required>
				   <option value="" hidden disabled selected value>What type of file are you uploading?</option>';
				   
	foreach($acceptedUploads as $option)
	{
		$form .= '<option value="'.$option['file'].'" >'.$option['label'].'</option>';
	}
	
	$form .= 	'</select>
			</div>

			<div class="w-100"></div>
				
			<div class="col">
			</div>

			<div class="col">
			 <p>* Upload <u>CSV files ONLY</u></p>
			 <p>* Persons Data must be uploaded <u>before</u> Person Org Relations</p>
			   
			</div>
			<div class="col" style="padding-top:25px;left:10%" id="buttonContainer">
			 <button onclick="showTheprogress()" type="submit" id="submit" name="import"
				class="btn btn-info"  >Import</button>
			</div>

			</div>
		</form>

		<div id="myProgress" style="display:block"> </div > <div id="loading" style="display:none" class="spinner-border text-secondary" role="status">
		<span class="sr-only">Loading...</span></div>

	</div>
	';

	# Check the uploaded file
	if( isset($_POST['UploadFile'])) {

		# Get the files 
		if( isset($_POST['import'])){

			# read the file and save it
			if(is_uploaded_file($_FILES['csvfile']['tmp_name']))
			{
				# Get the user selection
				$selectedOption = $_POST['UploadFile'];
				
				// Check if selected option is in the list of what is accepted
				$accepted = array_search($selectedOption, array_column($acceptedUploads, 'file'));

				if($accepted !== FALSE) {
					
					# open the csv file
					$file = fopen($_FILES['csvfile']['tmp_name'], "r");

					# get the first row labels and convert it to lower case
					$firstRow = array_map('trim',array_map('strtolower', fgetcsv($file)));

					# the file should have the accepted number of columns and column names
					if( (count($firstRow) === count($acceptedUploads[$accepted]['fields']) ) && array_map('trim',array_map('strtolower', $acceptedUploads[$accepted]['fields'])) === $firstRow ) {
						
						$taskName = 'uploadFile_'.$selectedOption;
						
						// init variables for reporting
						$recordTypeCounts = array('all'=>0,'new'=>0,'updated'=>0,'deleted'=>0,'unchanged'=>0);
						$report = '';
						$errors = array();

						while (($row = fgetcsv($file)) !== FALSE) {

							$recordTypeCounts['all']++;
							
							# send the data to processing function
							$result = call_user_func('processLocal'.(ucfirst($selectedOption)).'Record', $row);
							
							if(!empty($result['changes']))
							{
								$report .= $result['idInSource'].PHP_EOL;					
								
								foreach($result['changes'] as $field => $change)
								{
									$recordTypeCounts[$change]++;
									$report .= ' -- '.$field.' '.$change.PHP_EOL;	
								}
							}
							else
							{
								$recordTypeCounts['unchanged']++;
							}
						}
						
						$summary = saveReport($taskName, $report, $recordTypeCounts, $errors);
						
						$form .=  '<script language="javascript">
							document.getElementById("loading").style.display = "none";
						</script>
						
						<div class="alert alert-success" id="message">
							<p><b>Success</b></p>
							<p>Done processing '.$acceptedUploads[$accepted]['label'].' data </p>
							<p>--SUMMARY: '.$summary.'</p>
						</div>
						';

					} else {

						// if the file does not match the criteria 
						$form .=  '<div class="alert alert-warning" id="message" role="alert">
						<p><b>Warning</b></p>
						<p> '.$acceptedUploads[$accepted]['label'].' file must contain <u>'.count($acceptedUploads[$accepted]['fields']).' columns</u>: '.implode(', ', $acceptedUploads[$accepted]['fields']).' ( in this order ),<p>
						</div>';

					}
				} // End of accepted file processing
				else
				{
					$form .=  '<div class="alert alert-warning" id="message" role="alert">
					<p><b>Warning</b></p>
					<p>'.$selectedOption.' not in list of accepted uploads.<p>
					</div>';
				}
			} // Check if file was uploaded
		} // End of checking the file is exists or not 
	} // End of the checking the selected value statement 

	$form .=  '    <script>
		
		// function to hide the messages
		function HideTheMessage()
		{	
			var message = document.getElementById("message");
			if(message){
				document.getElementById("message").style.display = "none";
			}
			document.getElementById("myProgress").style.display = "none";
			document.getElementById("loading").style.display = "none";
		}

		// function to show the progress bar
		function showTheprogress()
		{
			document.getElementById("myProgress").style.display = "block";
			var myInput = document.getElementById("file");
			var myInputlist  = document.getElementById("list");
			if ( ( myInput && myInput.value ) && (myInputlist && myInputlist.value) ) {
				document.getElementById("loading").style.display = "block";
			  document.getElementById("myProgress").innerHTML="<p >Processing upload, please wait ..</p>";
			}
		}

			</script>';

	return $form;
}