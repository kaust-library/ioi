<?php
	/*

	**** This file provides a query endpoint for the tool for use by other systems.

	** Parameters :
		$localIDVariableName : prepend lowercase institution abbreviation to "id"
		$_GET[$localIDVariableName] : the local person id to be queried
		$_GET['year'] : the publication year to be queried
		$_GET['request'] : whether 'orcid' or 'works' should be returned
		$_GET['response'] : format of response (csv or json)

	** Created by : Daryl Grenz
	** Institute : King Abdullah University of Science and Technology | KAUST
	** Date : 16 April - 10:30 AM

	*/
	//--------------------------------------------------------------------------------------------

	//assume that application home directory is the parent directory
	set_include_path('../');

	//include core configuration and common function files
	include_once 'include.php';

	$localIDVariableName = strtolower(INSTITUTION_ABBREVIATION.'id');

	$localIDLabelName = INSTITUTION_ABBREVIATION.' ID';

	if(isset($_GET[$localIDVariableName]))
	{
		$localPersonID = $_GET[$localIDVariableName];
	}

	if(isset($_GET['year']))
	{
		$year = $_GET["year"];
	}

	if(isset($_GET['request']))
	{
		$request = $_GET["request"];
	}
	$acceptedrequests = Array('orcid', 'works');

	if(isset($_GET['response']))
	{
		$response = $_GET["response"];
	}
	$acceptedresponses = Array('csv', 'json');

	if(in_array($response, $acceptedresponses))
	{
		if($response=='csv')
		{
			header('Content-Type: text/csv; charset=utf-8');
			header('Content-Disposition: inline; filename=result.csv');
		}
		elseif($response=='json')
		{
			header('Content-Type: application/json; charset=utf-8');
			header('Content-Disposition: inline; filename=result.json');
		}

		if(in_array($request, $acceptedrequests))
		{
			if(strlen($localPersonID)===6&&is_numeric($localPersonID))
			{
				$source = 'local';
				$idInSource = 'person_'.$localPersonID;

				$fields = array($localIDLabelName=>'local.person.id','Full Name'=>'local.person.name');

				foreach($fields as $label=>$field)
				{
					$person[$label] = getValues($ioi, setSourceMetadataQuery($source, $idInSource, NULL, $field), array('value'), 'singleValue');
				}

				$person['ORCID'] = getValues($ioi, "SELECT orcid FROM orcids o LEFT JOIN metadata m ON o.email = m.value WHERE m.source = 'local' AND idInSource = '$idInSource' AND m.field = 'local.person.email'", array('orcid'), 'singleValue');

				if($person[$localIDLabelName]===$localPersonID)
				{
					if($request==='orcid')
					{
						// create a file pointer connected to the output stream
						$output = fopen('php://output', 'w');

						if($response=='csv')
						{
							// output the column headings
							fputcsv($output, array_keys($fields));

							fputcsv($output, array_values($person));
						}
						elseif($response=='json')
						{
							fwrite($output, json_encode($person));
						}
						fclose($output);
					}
					elseif($request==='works')
					{
						$source = 'dspace';

						if($year==='all'||(strlen($year)===4&&is_numeric($year)))
						{
							$orcid = $person['ORCID'];
							$controlName = $person['Full Name'];

							$fields = array('Type'=>'dc.type', 'Title'=>'dc.title', 'Authors'=>'dc.contributor.author', 'Journal'=>'dc.identifier.journal', 'DOI'=>'dc.identifier.doi', 'Handle'=>'dc.identifier.uri', 'Publication Date'=>'dc.date.issued', 'Bibtex'=>'dc.identifier.bibtex');

							$query = "SELECT DISTINCT `idInSource`
								FROM `metadata`
								WHERE `source` LIKE '$source'
								AND `deleted` IS NULL ";

							if($year!=='all')
							{
								$query .= "AND `idInSource` IN(
								SELECT DISTINCT `idInSource`
								FROM `metadata`
								WHERE `source` LIKE '$source'
								AND `field` LIKE 'dc.date.issued'
								AND `value` LIKE '$year%'
								AND `deleted` IS NULL)
								";
							}

							if(empty($orcid))
							{
								$query .= "AND `idInSource` IN(
								SELECT DISTINCT `idInSource`
								FROM `metadata`
								WHERE `source` LIKE '$source'
								AND `field` LIKE 'dc.contributor.author'
								AND `value` LIKE '$controlName'
								AND `deleted` IS NULL)
								";
							}
							else
							{
								$query .= "AND `idInSource` IN(
								SELECT DISTINCT orcid.idInSource
								FROM metadata author
								LEFT JOIN metadata `key` ON author.rowID=key.parentRowID
								LEFT JOIN metadata orcid ON key.rowID=orcid.parentRowID
								WHERE author.`source` LIKE '$source'
								AND author.field LIKE 'dc.contributor.author'
								AND orcid.field LIKE 'dc.identifier.orcid'
								AND orcid.value LIKE '$orcid'
								AND orcid.deleted IS NULL)
								";
							}

							$works = array();
							$workIDs = getValues($ioi, $query, array('idInSource'), 'arrayOfValues');

							foreach($workIDs as $idInSource)
							{
								foreach($fields as $label=>$field)
								{
									$works[$idInSource][$label] = implode('||',getValues($ioi, setSourceMetadataQuery($source, $idInSource, NULL, $field), array('value'), 'arrayOfValues'));
								}
							}

							// create a file pointer connected to the output stream
							$output = fopen('php://output', 'w');

							if($response=='csv')
							{
								// output the column headings
								fputcsv($output, array_keys($fields));

								foreach($works as $work)
								{
									fputcsv($output, $work);
								}
							}
							elseif($response=='json')
							{
								fwrite($output, json_encode(array_values($works)));
							}
							fclose($output);
						}
						else
						{
							echo 'Invalid year entered: submitted year must be a 4 digit number or the value "all"';
						}
					}
				}
				else
				{
					echo 'Submitted '.INSTITUTION_ABBREVIATION.' ID has no match in the database';
				}
			}
			else
			{
				echo 'Invalid '.INSTITUTION_ABBREVIATION.' ID: submitted id must be a 6 digit number or enter "all" for a bulk response';
			}
		}
		else
		{
			echo 'Invalid request type: valid types are "orcid" and "works"';
		}
	}
	else
	{
		echo 'Invalid response type: valid types are "csv" and "json"';
	}
