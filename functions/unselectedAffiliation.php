<?php

/*


**** This file is responsible of insert unselected affiliation in the database.

** Parameters :
	$orcid : unique identifier for each user in ORCID.
	$localAffiliations : array contains all the affiliations associative with ORCID id.
	$accessToken :  unique token for each user from ORCID.
	$localPersonID : unique identifier for each person in the institution.


** Created by : Yasmeen Alsaedy
** institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019 - 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function unselectedAffiliation($orcid, $localAffiliations, $accessToken,  $localPersonID){

	global $ioi;

	foreach($localAffiliations as $localAffiliation)
	{

		// if the unselected record is in ORCID 
		$localSourceRecordID = $localAffiliation['fields']['localSourceRecordID'];

		$existingPutCode =  getValues($ioi,"SELECT `putCode` FROM `putCodes` WHERE `orcid` = '$orcid'  AND `type` = '". $localAffiliation['type']."' AND `localSourceRecordID` = '$localSourceRecordID' AND deleted IS NULL", array('putCode'), 'singleValue');

		if(!empty($existingPutCode))
		{
	
			// delete the record from the ORCID 
			deleteFromORCID($orcid, $accessToken, $localAffiliation['type'], $existingPutCode);

			// delete it from ioi database 
			$query = "UPDATE `putCodes` SET deleted = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '$orcid' AND `localSourceRecordID` = '$localSourceRecordID' ";
			$deleted = $ioi-> query($query);

		}

		// check if the record already in the ignored table

		$result = $ioi->query("SELECT `ignored`, `deleted` FROM `ignored` WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '".$localSourceRecordID."'");
		
		$resultlist = mysqli_fetch_row($result);

		// if it's in the ignored table change the ignored date and make the delete null
		if( !is_null($resultlist)) {
			
			$update = $ioi->query("UPDATE `ignored` SET `deleted` = NULL , `ignored`.`ignored` = '".date("Y-m-d H:i:s")."' WHERE `orcid` = '".$orcid."' AND `localSourceRecordID` = '".$localSourceRecordID."';");
				
		} 	//else add the record in the ignored table 
		else{

			$query = "INSERT INTO `ignored`(`orcid`, `type`, `localSourceRecordID`, `ignored`) VALUES ('$orcid','".$localAffiliation['type']."','$localSourceRecordID','".date("Y-m-d H:i:s")."')";
			$update = $ioi->query($query);
		
		}
	}

	// to be more organized
	echo '<li style="font-color:Black;" id="unselectedAffiliationstyle">'.count($localAffiliations).' '.INSTITUTION_ABBREVIATION.' affiliation(s) were ignored.</li>';

}