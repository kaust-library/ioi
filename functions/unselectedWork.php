<?php
/*


**** This file is responsible of insert unselected affiliation in the database.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localWorkIDs : array contains all the works associative with ORCID id.
	$accessToken :  unique token for each user from ORCID.


** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//---------------------------------------------------------------------------------------------------------
function unselectedWork($orcid, $localWorkIDs, $accessToken){

	global $ioi;

	foreach($localWorkIDs as $localWorkID)
	{
	
		// if the unselected record is in ORCID 
		$localSourceRecordID = "repository_".$localWorkID;
	
		$existingPutCode = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = 'work' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');	

		if(!empty($existingPutCode))
		{
			
			// delete the record from the ORCID 
			deleteFromORCID($orcid, $accessToken, 'work', $existingPutCode);

			// delete it from ioi database 
			$deleted = $ioi-> query("UPDATE `putCodes` SET deleted = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '".$localSourceRecordID."' ");

		}

		// check if the record already in the ignored table

		$result = $ioi->query("SELECT `rowID` FROM `ignored` WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '".$localSourceRecordID."'");

		$resultlist = mysqli_fetch_row($result);

		// if it's in the ignored table change the ignored date and make the delete null
		if( !is_null($resultlist)) {

			$update = $ioi->query("UPDATE `ignored` SET `deleted` = NULL , `ignored`.`ignored` = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '".$localSourceRecordID."'");
			
		} 	//else add the record in the ignored table 

		else{

			$update = $ioi->query("INSERT INTO `ignored`(`orcid`, `type`, `localSourceRecordID`, `ignored`) VALUES ('".$orcid."','work','".$localSourceRecordID."','".date("Y-m-d H:i:s")."')");
				
		}
			
	}

	echo '<li style="font-color:Black;">'.count($localWorkIDs).' records for works in the '.INSTITUTION_ABBREVIATION.' repository were ignored.</li>';
}