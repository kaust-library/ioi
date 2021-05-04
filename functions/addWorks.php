<?php
/*

**** This funcation is responsible of sending works to ORCID, and updating the ignored table if the work was unselected by the user 

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$works : array contains all the work handles associated with the ORCID.
	$accessToken :  unique token for each user from ORCID.
	

** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 April 2019- 11:30 AM 

*/


//------------------------------------------------------------------------------------------------------------

function addWorks($orcid, $works, $accessToken){

	global $ioi;

	$newPutCodes = array();
	$existingPutCodes = array();	

	foreach($works as $work)
	{

		$localSourceRecordID = "repository_".$work['idInSource'];
		
		// chcek if the work already exists in the database 
		$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');
		
		if(!empty($existingPutCode))
		{
			$existingPutCodes[] = $existingPutCode;
		}
		else
		{
			// if the work was ignored, but is now selected ( make the deleted = the current date)
			$result = $ioi->query("SELECT `rowID` FROM `ignored` WHERE `orcid` = '$orcid' AND`localSourceRecordID` = '$localSourceRecordID'");
			
			if( !is_null($result)) {

				$update = $ioi->query("UPDATE `ignored` SET `deleted` = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID';");

			}

			// before send it to xml remove the selected key
			unset($work['selected']);

			$xml = prepWorkXML($work);

			$response = postToORCID($orcid, $accessToken, 'work', $xml);

			//failure returns array
			if(is_string($response))
			{
				$location = str_replace('||','',explode('/', explode('Location: ', $response)[1]));
				$putCode = trim($location[count($location)-1]);	
				$recordType = saveRecord($orcid, 'work', $putCode, $localSourceRecordID, $xml, 'XML', $response);
				
				$newPutCodes[] = $putCode;
	
			}			
		}
	}



	echo '<h4><b>Publications</b></h4>';
	
	echo '<li style="font-color:Black;">'.count($newPutCodes).' records for works in the '.INSTITUTION_ABBREVIATION.' repository were successfully added to your ORCID record.</li>';

	echo '<li style="font-color:Black;">'.count($existingPutCodes).' records for works in the '.INSTITUTION_ABBREVIATION.' repository already exist in your ORCID record and were not changed.</li>';
}