<?php	


/*


**** This file is responsible of executing specific query and return the result.

** Parameters :
	$database: database name.
	$query : the query in string format.
	$fields : the fields that the query will return.
	$request if the query will return single value put ( singleValue ) else ( arrayOfValues )



** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

	function getValues($database, $query, $fields, $request)
	{			
		$result = $database->query($query);
		
		if($request === 'singleValue')
		{
			$values = '';
			
			$row = $result->fetch_assoc();
			$values = $row[$fields[0]];
		}
		elseif($request === 'arrayOfValues')
		{
			$values = array();
			
			while($row = $result->fetch_assoc())
			{				
				if(count($fields)===1)
				{
					array_push($values, $row[$fields[0]]);
				}
				else
				{
					array_push($values, $row);
				}
			}
		}
		
		return $values;		
	}	
