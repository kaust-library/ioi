<?php

/*


**** This file is responsible for checking if a number is even or odd, and return back the bins for the chart as even always.

** Parameters :
	$number: numeric number

** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 16 April 2019- 10:30 AM 

*/

//-----------------------------------------------------------------------------------------------------------

function  evenOrOdd($number){

	if($number % 2 == 0){
	 $number = $number + 10;

	}else{
	  $number = $number + 11;
	}

	return $number;

}
