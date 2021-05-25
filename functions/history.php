<?php 

/*

**** This file is responsible of diplaying the history data for each user.

** Parameters :
	$localPersonID : unique identifier for each person in the institution.

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function history($localPersonID)
{
	global $ioi;

	$name = getName($localPersonID, $_SESSION[LDAP_NAME_ATTRIBUTE]);
	
	//History card to return for display in the main page permissionsForm
	$card = '

	<div class="card">
	<div class="card-header" id="headingTwo">
	  <h5 class="mb-0">
		<button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" style="font-size: 20px;">
		 History 
		</button>
	  </h5>
	</div>
	<div id="collapseTwo" class="collapse" aria-labelledby="headingTwo" data-parent="#accordion">
		<div class="card-body">';

	// get works in repository with author name match
	$localWorkIDs = getValues($ioi, "SELECT DISTINCT idInSource,`added` FROM `metadata` m WHERE `source` LIKE 'dspace' AND `field` LIKE 'dc.contributor.author' AND `value` LIKE '$name' AND deleted IS NULL ORDER BY `added` DESC", array('idInSource'), 'arrayOfValues');

	// get the ORCID iD using the localPersonID
	$orcid = getValues($ioi, "SELECT `orcid` FROM `orcids` WHERE `localPersonID` LIKE '$localPersonID'", array('orcid'), 'singleValue');
	
	// get the works that were ignored
	$ignoredWorks = getValues($ioi, "SELECT `ignored` FROM `userSelections`  WHERE `type` = 'work' AND `orcid` = '$orcid' AND `ignored` IS NOT NULL AND deleted IS NULL", array('ignored'), 'arrayOfValues');

	//Display the history of works and affiliation entries transferred to ORCID
	if(ORCID_MEMBER)
	{
		// get the works that were sent to ORCID
		$existingPutCodes = getValues($ioi, "SELECT `localSourceRecordID` FROM `putCodes` WHERE `type` = 'work' AND `orcid` = '$orcid' AND deleted IS NULL ", array('localSourceRecordID'), 'arrayOfValues');	

		$card .= '<br><h5><b>Publications</b></h5>
		
			<li style="font-color:Black;">'.count($localWorkIDs).' total work(s) in the '.INSTITUTION_ABBREVIATION.' repository match your name as an author. <a href="'.REPOSITORY_URL.'/discover?filtertype_1=author&filter_relational_operator_1=equals&filter_1='.$name.'&sort_by=dc.date.issued_dt&order=desc">See the full search results »</a></li>
			
			<li style="font-color:Black;">'.count($existingPutCodes).' work(s) in the '.INSTITUTION_ABBREVIATION.' repository were successfully added to your ORCID record.</li>
			
			<li style="font-color:Black;">'.count($ignoredWorks).' work(s) in the '.INSTITUTION_ABBREVIATION.' repository were ignored (you chose not to add them to your ORCID record).</li>';
		//------------------------------------------------------------------------------------------------------------------

		// get all the affiliations for the user for which a job title is listed
		$localPersonOrgRelationRowIDs = getValues($ioi, "SELECT `rowID` 
			FROM `metadata` m 
			WHERE `source` = 'local' 
			AND `idInSource` = 'person_$localPersonID' 
			AND `field` = 'local.personOrgRelation.id' 
			AND deleted IS NULL 
			AND EXISTS (
				SELECT * FROM `metadata` 
				WHERE `parentRowID` = m.`rowID` 
				AND `field` = 'local.person.title' 
				AND deleted IS NULL)", array('rowID'), 'arrayOfValues');

		// get all the affiliation(s) that were sent to ORCID
		$existingPutCodes = getValues($ioi, "SELECT `putCode` FROM `putCodes` WHERE  `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'local_person_$localPersonID%' AND deleted IS NULL", array('putCode'), 'arrayOfValues');

		// get all the affiliation(s) that were ignored
		$ignoredAffiliations = getValues($ioi, "SELECT `ignored`  FROM `userSelections`  WHERE `type` IN ('employment','education') AND `localSourceRecordID` LIKE 'local_person_$localPersonID%' AND `ignored` IS NOT NULL AND deleted IS  NULL", array('ignored'), 'arrayOfValues');

		$card .= '<hr><h5><b>Affiliation</b></h5>
		
		<li style="font-color:Black;">'.count($localPersonOrgRelationRowIDs).' affiliation(s) in '.INSTITUTION_ABBREVIATION.'.</li>
		
		<li style="font-color:Black;">'.count($existingPutCodes).' affiliation(s) in '.INSTITUTION_ABBREVIATION.' were successfully added to your ORCID record.</li>
		
		<li style="font-color:Black;">'.count($ignoredAffiliations).' affiliation(s) in '.INSTITUTION_ABBREVIATION.' were ignored.</li><br>
		
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
					<input type="hidden" name="review" value="yes">
					<button type="submit" value="submit" id="connect-orcid-button"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>Review and edit publications and affiliations transferred to your ORCID record</button>
				</form> 

				</div>
			</div>
		</div>';
	}
	//Display summary of works with ORCID or name match in DSpace
	else
	{
		// get works in repository with author ORCID match
		$localWorkIDsWithORCID = getValues($ioi, "SELECT DISTINCT idInSource, `added`
						FROM `metadata` 
						WHERE `source` LIKE 'dspace'
						AND `field` LIKE 'dc.identifier.orcid'
						AND value LIKE '$orcid'
						AND deleted IS NULL
						ORDER BY `added` DESC", array('idInSource'), 'arrayOfValues');	

		$card .= '<br><h5><b>Publications</b></h5>
		
			<li style="font-color:Black;">'.count($localWorkIDs).' total work(s) in the '.INSTITUTION_ABBREVIATION.' repository matching your name as an author. <a href="'.REPOSITORY_URL.'/discover?filtertype_1=author&filter_relational_operator_1=equals&filter_1='.$name.'&sort_by=dc.date.issued_dt&order=desc">See the full search results »</a></li>
			
			<li style="font-color:Black;">'.count($localWorkIDsWithORCID).' work(s) in the '.INSTITUTION_ABBREVIATION.' repository linked to your ORCID iD. <a href="'.REPOSITORY_URL.'/discover?filtertype_1=orcidid&filter_relational_operator_1=equals&filter_1='.$orcid.'&sort_by=dc.date.issued_dt&order=desc">See the full search results »</a></li>
			
			<li style="font-color:Black;">'.count($ignoredWorks).' work(s) in the '.INSTITUTION_ABBREVIATION.' repository matching your name as an author were ignored (you chose not to link them to your ORCID iD).</li>';

		$card .= '<form method="post" action="'.$_SERVER['REQUEST_URI'].'">
					<input type="hidden" name="review" value="yes">
					<button type="submit" value="submit" id="connect-orcid-button"><img id="orcid-id-icon" src="https://orcid.org/sites/default/files/images/orcid_24x24.png" width="24" height="24" alt="ORCID iD icon"/>Review and edit publications linked to your ORCID iD in the '.INSTITUTION_ABBREVIATION.' repository</button>
				</form> 

				</div>
			</div>
		</div>';
	}
	
	return $card;
}