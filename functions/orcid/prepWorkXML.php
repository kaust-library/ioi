<?php
/*

**** This file contains a function used to prepare the work as an ORCID-formatted XML file.

** Parameters :
	$work : an array of fields and values for the item metadata
	

** Created by : Daryl Grenz 
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 10 June 2019- 1:30 PM 

*/

//------------------------------------------------------------------------------------------------------------

	//Prepare ORCID-formatted work XML, $orcid and $putcode are needed if updating an existing entry
	function prepWorkXML($work, $orcid=NULL, $putcode=NULL)
	{
		unset($work['idInSource']);
		
		$namespaces = array('work' => "http://www.orcid.org/ns/work", 'common' => 'http://www.orcid.org/ns/common');

		if(is_null($putcode) & is_null($orcid) )
		{
			$xml = new SimpleXMLElement('<work:work xmlns:work="'.$namespaces['work'].'" xmlns:common="'.$namespaces['common'].'" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" />');
		}
		else
		{
			$xml = new SimpleXMLElement('<work:work put-code="'.$putcode.'" path="/'.$orcid.'/work/'.$putcode.'" xmlns:work="'.$namespaces['work'].'" xmlns:common="'.$namespaces['common'].'" />');
		}

		foreach($work as $field => $value)
		{
			if(!empty($value))
			{
				if(($field === 'title'))
				{
					$element = $xml->addChild($field, null, $namespaces['work']);
					$element->addChild($field, str_replace('&', '&amp;', $value), $namespaces['common']);
				}
				elseif($field == 'url')
				{
					$xml->addChild($field, str_replace('&', '&amp;', $value), $namespaces['common']);
				}
				elseif($field === 'publication-date')
				{
					$dateParts = explode('-', $value);
					
					$element = $xml->addChild($field, null, $namespaces['common']);
					if(isset($dateParts[0]))
					{
						$element->addChild('year', $dateParts[0], $namespaces['common']);
					}
					elseif(isset($dateParts[1]))
					{
						$element->addChild('month', $dateParts[1], $namespaces['common']);
					}
					elseif(isset($dateParts[2]))
					{
						$element->addChild('day', $dateParts[2], $namespaces['common']);
					}
				}
				elseif($field === 'external-ids')
				{
					$element = $xml->addChild('external-ids', null, $namespaces['common']);
					foreach($value as $idType => $ids)
					{
						foreach($ids as $id)
						{
							if($idType === 'handle')
							{
								$id = str_replace('http://hdl.handle.net/', '', $id);
							}
							
							$subelement = $element->addChild('external-id', null, $namespaces['common']);
							$subelement->addChild('external-id-type', $idType, $namespaces['common']);
							$subelement->addChild('external-id-value', $id, $namespaces['common']);
							$subelement->addChild('external-id-relationship', 'self', $namespaces['common']);
						}
					}
				}
				else
				{
					if(($field === 'type'))
					{
						$value = convertToORCIDWorkType($value);
					}
					
					$xml->addChild($field, str_replace('&', '&amp;', $value), $namespaces['work']);				
				}
			}
		}

		$xml = $xml->asXML();

		return $xml;
	}