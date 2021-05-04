<?php


/*

**** This file is responsible of preparing the affiliation data to xml format.

** Parameters :
	$localAffiliation :affiliation data ( employment or education ).
	$orcid : unique identifier for each user in ORCID (by default null).
	$putCode: unique number for each (work, affiliation) in ORCID (by default null).



** Created by : Daryl Grenz
** Institute : King Abdullah University of Science and Technology | KAUST
** Date :  1 April 2019 - 8:00 AM 


*/

//-----------------------------------------------------------------------------------------------------------


function prepAffiliationXML($localAffiliation, $orcid=NULL, $putcode=NULL)
{
	$type = $localAffiliation['type'];
	$fields = $localAffiliation['fields'];

	$namespaces = array($type => "http://www.orcid.org/ns/$type", 'common' => 'http://www.orcid.org/ns/common');


	if(is_null($putcode) & is_null($orcid) )
	{
		$xml = new SimpleXMLElement('<'.$type.':'.$type.' xmlns:'.$type.'="'.$namespaces[$type].'" xmlns:common="http://www.orcid.org/ns/common" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="'.$namespaces[$type].' ../'.$type.'-2.0.xsd "/>');

	}
	else
	{
		$xml = new SimpleXMLElement('<'.$type.':'.$type.' put-code="'.$putcode.'" xmlns:'.$type.'="'.$namespaces[$type].'" xmlns:common="http://www.orcid.org/ns/common" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="'.$namespaces[$type].' ../'.$type.'-2.0.xsd "/>');
	}


	foreach($fields as $field => $value)
	{	


		$xml->addChild($field, str_replace('&', '&amp;', $value), $namespaces['common']);

	}

	if(isset($localAffiliation['dates']))
	{
		foreach($localAffiliation['dates'] as $dateType => $date)
		{
			if(!empty($date))
			{
				$dateParts = explode('-', $date);
				$element = $xml->addChild($dateType, null, $namespaces['common']);
				$element->addChild('year', $dateParts[0], $namespaces['common']);
				$element->addChild('month', $dateParts[1], $namespaces['common']);
				$element->addChild('day', $dateParts[2], $namespaces['common']);
			}
		}
	}


	$organization = $xml->addChild('organization', null, $namespaces['common']);
	$organization->addChild('name', INSTITUTION_NAME, $namespaces['common']);
	$address = $organization->addChild('address', null, $namespaces['common']);
	$address->addChild('city', INSTITUTION_CITY, $namespaces['common']);
	$address->addChild('country', INSTITUTION_COUNTRY, $namespaces['common']);

	$disambiguated = $organization->addChild('disambiguated-organization', null, $namespaces['common']);
	$disambiguated->addChild('disambiguated-organization-identifier', INSTITUTION_RINGGOLD_ID, $namespaces['common']);
	$disambiguated->addChild('disambiguation-source', 'RINGGOLD', $namespaces['common']);


	$xml = $xml->asXML();

	

	return $xml;




}
