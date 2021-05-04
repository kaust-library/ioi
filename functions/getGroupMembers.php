<?php	

/*


**** This file is responsible for retrieving lists of group members according to criteria.

** Parameters :
	$group : array of group attributes (titles or titleParts) used to query for members .	
	$active : active people will be those with a current org relation .
	$emailSent : whether a previous email has been sent to them .
	$orcidCreated : whether they have an ORCID iD recorded in the system.
	$permissionsGranted : whether they have granted an access token to IOI. 

** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 7 October 2019 - 8:00 AM 

*/

//-----------------------------------------------------------------------------------------------------------

	function getGroupMembers($group, $active, $emailSent, $orcidCreated, $permissionsGranted)
	{			
		global $ioi;
		
		$titles = str_replace('||',"','",$group['titles']);
		$titleParts = explode('||',$group['titleParts']);

		$titleQuery = '';
		if($group['label'] === 'Others')
		{			
			$titleQuery = " AND title.value NOT IN ('$titles')";
			
			if(!empty($titleParts))
			{
				foreach($titleParts as $titlePart)
				{
					$titleQuery .= " AND title.value NOT LIKE '%$titlePart%'";
				}
			}
		}
		elseif(!empty($titles))
		{
			$titleQuery = " AND title.value IN ('$titles')";
		}
		elseif(!empty($titleParts))
		{
			foreach($titleParts as $titlePart)
			{
				$titleQueryParts[] = " title.value LIKE '%$titlePart%' ";
			}
			$titleQuery = " AND (".implode(" OR ", $titleQueryParts).")";
		}
		
		$statusQuery = '';
		if(!is_null($active))
		{
			if($active)
			{
				$statusQuery = " AND title.parentRowID NOT IN (
					SELECT parentRowID FROM metadata 
					WHERE source='local'
					AND idInSource = email.idInSource
					AND field = 'local.date.end')";
			}
			else
			{
				$statusQuery = " AND title.parentRowID IN (
					SELECT parentRowID FROM metadata 
					WHERE source='local'
					AND idInSource = email.idInSource
					AND field = 'local.date.end')";
			}
		}
		
		$emailQuery = '';
		if(!is_null($emailSent))
		{
			if($emailSent)
			{
				$emailQuery = " AND email.value IN (
				SELECT idInSource FROM metadata
				WHERE source = 'ioi'
				AND field = 'ioi.email.sent'
				)";
			}
			else
			{
				$emailQuery = " AND email.value NOT IN (
				SELECT idInSource FROM metadata
				WHERE source = 'ioi'
				AND field = 'ioi.email.sent'
				)";
			}
		}
		
		$orcidQuery = '';
		if(!is_null($orcidCreated))
		{
			if($orcidCreated)
			{
				$orcidQuery = " AND email.value IN (
				SELECT email FROM orcids)";
			}
			else
			{
				$orcidQuery = " AND email.value NOT IN (
				SELECT email FROM orcids)";
			}
		}
		
		$tokenQuery = '';
		if(!is_null($permissionsGranted))
		{
			if($permissionsGranted)
			{
				$tokenQuery = " AND (SELECT COUNT(`access_token`) FROM `tokens` LEFT JOIN orcids USING (orcid) WHERE orcids.email = email.value AND deleted IS NULL) != 0";
			}
			else
			{
				$tokenQuery = " AND (SELECT COUNT(`access_token`) FROM `tokens` LEFT JOIN orcids USING (orcid) WHERE orcids.email = email.value AND deleted IS NULL) = 0";
			}
		}
		
		$query = "SELECT DISTINCT email.idInSource 
		FROM metadata email
		LEFT JOIN metadata title ON email.idInSource = title.idInSource
		WHERE email.source = 'local' 
		AND email.field = 'local.person.email'	
		$emailQuery
		$orcidQuery		
		AND email.deleted IS NULL
		AND title.source = 'local'
		AND title.field = 'local.person.title'
		$titleQuery 
		$statusQuery
		$tokenQuery";
		
		$result = getValues($ioi, $query, array('idInSource'), 'arrayOfValues');
	
		return array('list'=>$result,'query'=>$query);	
	}	
