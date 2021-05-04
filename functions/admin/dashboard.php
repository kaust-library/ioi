<?php
/*

**** This file is responsible for displaying a usage dashboard for the application.

** Parameters :
	No parameters required


** Created by : Yasmeen Alsaedy
** Institute : King Abdullah University of Science and Technology | KAUST
** Date : 28 August - 1:24 PM

*/

//--------------------------------------------------------------------------------------------------------------------------------------------------
	//Define function to present a dashboard
	function dashboard()
	{
		global $ioi;

		# All Faculty ORCID iD
		$faculty = array();

		# array of the KAUST guide color
		$colors = array('80615D', 'F0B500', 'CDCE00', 'F18F00', '00A6AA');

		# counter to display the permission label with different colors
		$colorsCounter = 0;
		$counterforpermissionskeys = 0;

		# ----------------------------------- Chart #1 How many Faculty, Staff and Student in our db have ORCID iD ---------------------------------

		# get all the emails in ioi database
		$orcidusers = getValues($ioi, "SELECT `email`, `orcid` FROM `orcids`",   array('email', 'orcid'), 'arrayOfValues');

		# get the groups
		$groups = getValues($ioi, "SELECT * FROM `groups` WHERE 1", array('label','titles','titleParts'), 'arrayOfValues');

		# add their labels as keys for counting members
		foreach($groups as $group)
		{
			$roles[$group['label']] = 0;
		}
		$roles['Others'] = 0;

		# for each item in $orcids
		foreach($orcidusers as $user){

			# from the email get the person title
			$title = getValues($ioi, "SELECT `value` FROM `metadata` WHERE `field`= 'local.person.title' AND `idInSource` = (SELECT `idInSource` FROM `metadata` WHERE `value` LIKE '".$user['email']."' AND `source` = 'local' AND deleted IS NULL LIMIT 1 ) AND deleted IS NULL ORDER BY `added` DESC LIMIT 1", array('value'), 'singleValue');

			# if the title not empty
			if(!empty($title)) {

				$match = FALSE;
				foreach($groups as $group)
				{
					if(!empty($group['titles']))
					{
						$titles = explode('||',$group['titles']);

						if(in_array($title, $titles))
						{
							$roles[$group['label']]++;
							$match = TRUE;
							break;
						}
					}
					elseif(!empty($group['titleParts']))
					{
						$titleParts = explode('||',$group['titleParts']);

						foreach($titleParts as $titlePart)
						{
							if(stripos($title, $titlePart) !== false)
							{
								$roles[$group['label']]++;

								if($group['label']==='Faculty')
								{
									# save faculty ORCID iD
									array_push($faculty, $user['orcid']);
								}

								$match = TRUE;
								break;
							}
						}
					}
				}

				if(!$match)
				{
					$roles['Others']++;
				}
			} // not empty title
		} // end of the foreach loop

		# ----------------------------------- Chart #2 How many member registered by category ------------------------------------------------------

		# get first registration date
		$startyear = getValues($ioi, "SELECT min(`added`) as startyear FROM `orcids`", array('startyear'), 'singleValue');

		# convert it to date
		$startyear = date("Y", strtotime($startyear));

		# get the range from the start year till to day year
		$currentyear = date("Y");
		$yearrange = range($startyear, $currentyear);

		# yearly counter array
		# add group labels as keys for arrays of members counted each year
		foreach($groups as $group)
		{
			$yearlyCounts[$group['label']] = array();
		}
		$yearlyCounts['Others'] = array();

		# max counter to use it in the chart
		$maxcounter = 0;

		# for each year count all the registrations in this year
		foreach ($yearrange as $year) {

			$emails = getValues($ioi, "SELECT `email`  FROM `orcids` where `added` like '$year%'", array('email'), 'arrayOfValues');

			# reset the counter for each year
			foreach($groups as $group)
			{
				$groupCounts[$group['label']] = 0;
			}
			$groupCounts['Others'] = 0;

			foreach($emails as $email) {

				# from the email get the most recent person title
				$title = getValues($ioi, "select `value` from `metadata` where `field`= 'local.person.title' and `idInSource` = (SELECT `idInSource` FROM `metadata` WHERE `value` LIKE '$email' and `source` = 'local' limit 1 ) ORDER BY `metadata`.`added` DESC limit 1", array('value'), 'singleValue');

				$match = FALSE;
				foreach($groups as $group)
				{
					if(!empty($group['titles']))
					{
						$titles = explode('||',$group['titles']);

						if(in_array($title, $titles))
						{
							$groupCounts[$group['label']]++;
							$match = TRUE;
							break;
						}
					}
					elseif(!empty($group['titleParts']))
					{
						$titleParts = explode('||',$group['titleParts']);

						foreach($titleParts as $titlePart)
						{
							if(stripos($title, $titlePart) !== false)
							{
								$groupCounts[$group['label']]++;
								$match = TRUE;
								break;
							}
						}
					}
				}

				if(!$match)
				{
					$groupCounts['Others']++;
				}
			}

			foreach($groupCounts as $group => $count)
			{
				$yearlyCounts[$group][] = $count;
			}

			# get the max
			$maxcounter = max( $groupCounts + array($maxcounter));
		}

		# ----------------------------------- Chart #3 How many permissions and many member give us this permission ------------------------------------

		# get all the permissions ( unique )
		$permissions = getValues($ioi, "SELECT DISTINCT(`scope`) as permissions FROM `tokens`", array('permissions'), 'arrayOfValues');

		# add no-permission to the permissions array
		$permissions  = array_merge($permissions, array('no-permission'));

		# fill all the permissions with zeros
		$counterforpermissions = array_fill_keys($permissions, 0);

		# for each permission count all the user that gave us this permission
		foreach ( $permissions  as $permission ) {

			# skip no-permission
			if($permission != 'no-permission') {
				$totalregist = getValues($ioi, "SELECT count(*) as permissions FROM `tokens` where `scope` = '".$permission."' and `deleted` IS NULL", array('permissions'), 'singleValue');

				if(!empty($totalregist ))
					$counterforpermissions[$permission] = $totalregist;
			}

		}

		# get all the values of the array
		$permissionsvalue = array_values($counterforpermissions);

		# total of all pemission - total all = no-permission orcid
		$permissions['no-permission'] = array_sum(array_values($counterforpermissions)) - count($orcidusers);

		# ----------------------------------- Chart #4 For Faculty only, how many  and which type of permissions ----------------------

		# for each permission count all the faculty that gave us this permission
		$counterforpermissionsfaculty = array_fill_keys($permissions, 0);

		foreach ( $faculty  as $orcid ) {

			$facultypermission = getValues($ioi, "SELECT scope FROM `tokens` where  `deleted` IS NULL and orcid = '".$orcid."' ", array('scope'), 'singleValue');

			if(!empty($facultypermission ))
				$counterforpermissionsfaculty[$facultypermission]++;
			else
				$counterforpermissionsfaculty['no-permission']++;

		}

		# get all the values of the array
		$permissionsfacultyvalue = array_values($counterforpermissionsfaculty);


		# ------------------------------ 	Chart #5 For basic statistic about work and affiliation data ----------------------------------------------------

		# how many works in the database
		$worksCounter = getValues($ioi, "SELECT DISTINCT count(*) as works FROM `putCodes` where `deleted` IS NULL and `type` = 'work'", array('works'), 'singleValue');

		# how many affiliations in the database
		$affiliationsCounter = getValues($ioi, "SELECT DISTINCT count(*) as affiliations FROM `putCodes` where `deleted` IS NULL and (`type` = 'employment' or `type` = 'education')", array('affiliations'), 'singleValue');

		# how many ignored works in database
		$worksIgnoredCounter = getValues($ioi, "SELECT DISTINCT count(*) as ignoredWorks FROM `ignored` where `deleted` IS NULL and `type` = 'work'", array('ignoredWorks'), 'singleValue');

		# how many ignored works in database
		$affiliationsIgnoredCounter = getValues($ioi, "SELECT DISTINCT count(*) as ignoredaffiliations FROM `ignored` where `deleted` IS NULL and (`type` = 'employment' or `type` = 'education')", array('ignoredaffiliations'), 'singleValue');

		# get the max value
		$maxValue = max(array($worksCounter, $affiliationsCounter, $worksIgnoredCounter, $affiliationsIgnoredCounter));


		# -------------------------------------- HTML and JS for to display the charts -----------------------------------------------------------------------

		$dashboard = "

		<!doctype html>

		<html lang='en'>
		<style>

		html, body {
		  margin: 0;
		  padding: 0;
		  height: 100%;
		  width: 100%;
		}

		.zc-ref {
		  display: none;
		}

		</style>
		<head>
		  <meta charset='utf-8'>

		  <title>ORCID Dashboard</title>

		  <!-- -------------------------------------- Upload the js library ------------------------------------------------------------------------------------------ -->

			<script src='https://cdn.zingchart.com/zingchart.min.js'></script>
		</head>

		<body>

		<!-- ------------- ORCID User chart and Faculty Permissions chart--------- -->
		<div id='myChart' class='chart--container' style='height: 35%;'></div>

		<!-- ------------- Member Registered chart and Permissions chart--------- -->
		<div id='myChart2' class='chart--container2'  ></div>

		<!-- ------------- Works and Affiliations chart --------- -->
		<div id='myChart3' class='chart--container3'  style='height:430px' ></div>

		<script>
		let chartConfig = {

		  globals: {
			fontFamily: 'Arial',
			fontWeight: 'normal'
		  },
		 backgroundColor: '#e9ecef',
			 y: '0%',
			height: '100%',
		  width: '100%',
		  graphset: [
			{
			  type: 'null',
			  backgroundColor: 'none',
			  x: '2.25%',
			  y: '1%',

			},
			{

			// -------------------------------------------- ORCID Users pie chart -------------------------------------------------------------------------------------

			  type: 'pie',
			  backgroundColor: 'white',
			  borderRadius: '4px',
			  borderWidth: '1px',
			  borderColor: '#dae5ec',
			  width: '30%',
			  height: '60%',
			  x: '3%',
			  y: '10%',
			   title: {
				text: 'ORCID USERS',
				margin: '15px 20px 0px 0px',
				padding: '2px 0px 0px 24px',
				backgroundColor: 'none',
				fontColor: '#707d94',
				fontFamily: 'Arial',
				fontSize: '15px',
				fontWeight: 'bold',
				shadow: false,
				textAlign: 'left'
			  },
			  plot: {
				valueBox: [
				{ text: '%t',
				fontSize: '16px',
				placement: 'out'},

				{ text: '%npv%',
				fontColor: '#1A1B26',
				fontSize: '16px',
				placement: 'in',
					rules: [
					{
					  rule: '%v <= 0',
					  visible: false
					}
				  ]
			}
			  ],
				animation: {
				  delay: 0,
				  effect: 'ANIMATION_EXPAND_VERTICAL',
				  method: 'ANIMATION_LINEAR',
				  sequence: 'ANIMATION_BY_PLOT',
				  speed: '300'
				}
			  },
			  plotarea: {
				margin: '20px 10px 0px 0px'
			  },
			  series: [";

			$colorsCounter = 0;
			$series = array();
			foreach($roles as $group => $count)
			{
				$series[] = "{
				  text: '$group',
				  values: [$count],
				  tooltip: {
						padding: '5px 10px',
						backgroundColor: '#".$colors[$colorsCounter]."',
						borderRadius: '6px',
						fontColor: '#ffffff',
						shadow: false
				  },
					backgroundColor: '#".$colors[$colorsCounter]."',
					borderColor: '#454754',
					borderWidth: '1px',
				  shadow: false
				}";
				$colorsCounter++;
				if($colorsCounter == (sizeof($colors))){
					$colorsCounter= 0;
				}
			}

			$dashboard .= implode(',', $series);

			$dashboard .= "]
			},


			// ----------------------------------------- End of ORCID Users pie chart -----------------------------------------------------------------------------------


			 // ------------------------------- Faculty Permissions bar chart ------------------------------------------------------------------------------------------

			{
			  type: 'bar',
			  backgroundColor: '#ffffff',
			  borderRadius: '4px',
			  width: '63.5%',
			  height: '60%',
					borderWidth: '1px',
			  borderColor: '#dae5ec',
			 x: '33.5%',
			  y: '10%',
		  title: {
			   text: 'FACULTY PERMISSIONS',
				margin: '15px 20px 0px 0px',
				padding: '4px 0px 0px 24px',
				backgroundColor: 'none',
				fontColor: '#707d94',
				fontFamily: 'Arial',
				fontSize: '15px',
				fontWeight: 'bold',
				shadow: false,
				textAlign: 'left'
			  },

			  plot: {
				animation: {
				  delay: 500,
				  effect: 'ANIMATION_SLIDE_BOTTOM'
				},
				 rules: [
					{
					  rule: '%v <= 0',
					  visible: false
					}],
				backgroundColor: '#000000'
			  },
			  plotarea: {
				margin: '50px 25px 70px 46px',
				  y: '20%'
			  },
			  scaleX: {
				values: [' '],
				guide: {
				  visible: false
				},
				item: {
				  fontSize: '10px',
				  offsetY: '-2%'
				},
				lineColor: '#d2dae2',
				lineWidth: '2px',
				tick: {
				  lineColor: '#d2dae2',
				  lineWidth: '1px'
				}
			  },

			  scaleY: {
				values: '0:".(evenOrOdd(max($permissionsfacultyvalue)))."',
					guide: {
				  alpha: 0.5,
				  lineColor: '#d2dae2',
				  lineStyle: 'solid',
				  lineWidth: '1px'
				},
				item: {
				  fontSize: '10px',
				  offsetX: '2%'
				},
				lineColor: 'none',
				tick: {
				  visible: false
				}
			  },
			  tooltip: {
				text: '%t<br><strong>%v</strong>',
				padding: '5px 10px',
				borderRadius: '4px',
				callout: true,
				fontSize: '12px',

			  },
			  series:[";

				# print the permissions in the js chart
				foreach($counterforpermissionsfaculty as $permissionfaculty){


					# ignore unselected permission
					if($permissionfaculty > 0 ){
						$dashboard  .= "
						{
						  text: '".$permissions[$counterforpermissionskeys]."',
						  values: [".$permissionfaculty."],
						  backgroundColor: '#".$colors[$colorsCounter]."',
						  description: '".$permissions[$counterforpermissionskeys]."',
						  hoverState: {
							backgroundColor: '#".$colors[$colorsCounter]."'
						  }
						}

					  ,";
					 }

				$colorsCounter++;
				$counterforpermissionskeys++;
				if($colorsCounter == (sizeof($colors))){
					$colorsCounter= 0;
				}
				}

		  $dashboard  .= "

			  ]
			}


		  ]
		};

		zingchart.render({
		  id: 'myChart',
		  data: chartConfig
		});


		 // -------------------------------------------- End of Faculty Permissions bar chart -----------------------------------------------------------------------


		<!------------------------------- ORCID iDs Registered in each year bar chart -------------------------------------------------------------------------------------


		let chartConfig2 = {

		  globals: {
			fontFamily: 'Arial',
			fontWeight: 'normal'
		  },
		  backgroundColor: '#e9ecef',
			  height: '100%',
		  width: '100%',

		  graphset: [
		   {
			   type: 'line',
			  borderRadius: '4px',
			  borderColor: '#dae5ec',
			  borderWidth: '1px',
			 width: '94%',
			  height: '52%',
			  x: '3%',
			   y: '-2%',
			  title: {
			   text: 'ORCID iDs ADDED PER YEAR',
				margin: '15px 20px 0px 0px',
				padding: '8px 0px 0px 24px',
				backgroundColor: 'none',
				fontColor: '#707d94',
				fontFamily: 'Arial',
				fontSize: '15px',
				fontWeight: 'bold',
				shadow: false,
				textAlign: 'left'
			  },
			  plot: {
				animation: {
				  delay: 500,
				  effect: 'ANIMATION_SLIDE_LEFT'
				}

			  },
			  plotarea: {
				margin: '60px 25px 80px 46px'
			  },
			  scaleY: {
				values: '0:".evenOrOdd($maxcounter)."',
				guide: {
				  alpha: 0.5,
				  lineColor: '#d2dae2',
				  lineStyle: 'solid',
				  lineWidth: '1px'
				},
				item: {
				  paddingRight: '5px',
				  fontColor: '#8391a5',
				  fontFamily: 'Arial',
				  fontSize: '10px'
				},
				lineColor: 'none',
				tick: {
				  visible: false
				}
			  },";

			  $dashboard  .= '
			  scaleX: {
				values: ["'.implode('","', $yearrange).'"],
				';

		 $dashboard  .= "
				guide: {
				  visible: false
				},
				item: {
				  paddingTop: '5px',
				  fontColor: '#8391a5',
				  fontFamily: 'Arial',
				  fontSize: '10px'
				},
				lineColor: '#d2dae2',
				lineWidth: '2px',
				tick: {
				  lineColor: '#d2dae2',
				  lineWidth: '1px'
				}
			  },
			  legend: {
				margin: 'auto auto 15 auto',
				backgroundColor: 'none',
				borderWidth: '0px',
				item: {
				  margin: '0px',
				  padding: '0px',
				  fontColor: '#707d94',
				  fontFamily: 'Arial',
				  fontSize: '9px'
				},
				layout: 'x4',
				marker: {
				  type: 'match',
				  padding: '3px',
				  fontFamily: 'Arial',
				  fontSize: '10px',
				  lineWidth: '2px',
				  showLine: 'true',
				  size: 4
				},
				shadow: false

			  },
			  scaleLabel: {
				padding: '2px 4px',
				backgroundColor: '#707d94',
				borderRadius: '5px',
				fontColor: '#ffffff',
				fontFamily: 'Arial',
				fontSize: '10px'
			  },
			  crosshairX: {
				lineColor: '#707d94',
				lineWidth: '1px',
				plotLabel: {
				  padding: '2px 6px',
				  alpha: 1,
				  borderRadius: '5px',
				  fontColor: '#000',
				  fontFamily: 'Arial',
				  fontSize: '10px',
				  shadow: false
				}
			  },
			  tooltip: {
				visible: false,
				borderRadius: '4px'
			  },
			  series: [ ";

				# set the counter to zero
				$colorsCounter = 0;

				# print the permissions in the js chart
				foreach($yearlyCounts as $text => $yearcounts){

					  $dashboard  .= '
						{
					  text: "'.$text.'",
					  values: ['.implode(",", $yearcounts).'],
					  lineColor: "#'.$colors[$colorsCounter].'",
					  description: "'.$text.'",
					  lineWidth: "2px",
					  hoverState: {
					  backgroundColor: "#FFC942"
					   },

						marker: {
						backgroundColor: "#fff",
						borderColor: "#'.$colors[$colorsCounter].'",
						borderWidth: "1px",
						shadow: false,
						size: 5
					  },
					  palette: 0,
					  shadow: false
						}

					 ,';

					$colorsCounter++;
					if($colorsCounter == (sizeof($colors))){
						$colorsCounter= 0;
					}
				}

		 $dashboard  .= "
			  ]
			},

		<!------------------------------- End of ORCID iDs Registered in each year bar chart -------------------------------------------------------------------------------

		<!------------------------------- Permissions bar chart -------------------------------------------------------------------------------------



		{
			  type: 'bar',
			  backgroundColor: '#ffffff',
					borderWidth: '1px',
			  borderColor: '#dae5ec',
			  borderRadius: '4px',
			 width: '94%',
			  height: '230px',
			  x: '3%',
			   y: '55%',
		  title: {
			   text: 'PERMISSIONS',
				margin: '15px 20px 0px 0px',
				padding: '0px 0px 0px 32px',
				backgroundColor: 'none',
				fontColor: '#707d94',
				fontFamily: 'Arial',
				fontSize: '15px',
				fontWeight: 'bold',
				shadow: false,
				textAlign: 'left'
			},
			  plot: {
				animation: {
				  delay: 500,
				  effect: 'ANIMATION_SLIDE_BOTTOM'
				},
				 rules: [
					{
					  rule: '%v <= 0',
					  visible: false
					}],
				backgroundColor: '#000000'
			  },
			  plotarea: {
				margin: '50px 25px 50px 46px',
				  y: '20%'
			  },
			  scaleX: {
				values: [' '],
				guide: {
				  visible: false
				},
				item: {
				  fontSize: '10px',
				  offsetY: '-2%'
				},
				lineColor: '#d2dae2',
				lineWidth: '2px',
				tick: {
				  lineColor: '#d2dae2',
				  lineWidth: '1px'
				}
			  },

			  scaleY: {
				values: '0:".(evenOrOdd(max($permissionsvalue)))."',
					guide: {
				  alpha: 0.5,
				  lineColor: '#d2dae2',
				  lineStyle: 'solid',
				  lineWidth: '1px'
				},
				item: {
				  fontSize: '10px',
				  offsetX: '2%'
				},
				lineColor: 'none',
				tick: {
				  visible: false
				}
			  },
			  tooltip: {
		   text: '%t<br><strong>%v</strong>',
				padding: '5px 10px',
				borderRadius: '4px',
				callout: true,
				fontSize: '12px',

			  },
			  series:[";


				# reset the counters
				$counterforpermissionskeys = 0;
				$colorsCounter = 0;

				# print the permissions in the js chart
				foreach($counterforpermissions as $permission){


					# ignore unselected permission
					if($permission > 0 ){
						  $dashboard  .= "
						{
						  text: '".$permissions[$counterforpermissionskeys]."',
						  values: [".$permission."],
						  backgroundColor: '#".$colors[$colorsCounter]."',
						  description: '".$permissions[$counterforpermissionskeys]."',
						  hoverState: {
							backgroundColor: '#".$colors[$colorsCounter]."'
						  }
						}

					  ,";
					 }

				$colorsCounter++;
				$counterforpermissionskeys++;
				if($colorsCounter == (sizeof($colors))){
					$colorsCounter= 0;
				}
				}


		 $dashboard  .= "

		  ]

		},

// ------------------------------ End of bar chart -------------------------------------------------------------------------------------


		]
		};


		zingchart.render({
		  id: 'myChart2',
		  data: chartConfig2
		});

	// -------------------------------- Works and affiliations counts bar chart ------------------------------------------------------------


		let chartConfig3 = {

		  globals: {
			fontFamily: 'Arial',
			fontWeight: 'normal'
		  },
		 backgroundColor: '#e9ecef',
			 y: '0%',
			height: '100%',
		  width: '98%',
		  graphset: [
			{
			  type: 'null',
			  backgroundColor: 'none',
			  x: '2.25%',
			  y: '5%',


			},

		{
      type: 'bar',
      backgroundColor: '#fff',
      borderWidth: '1px',
      borderColor: '#dae5ec',
      width: '96%',
      height: '50%',
      x: '3%',
      y: '6%',
       margin: '0 0',

      title: {
	   text: 'Works & Affiliations',
		margin: '15px 20px 0px 0px',
		padding: '0px 0px 0px 32px',
		backgroundColor: 'none',
		fontColor: '#707d94',
		fontFamily: 'Arial',
		fontSize: '15px',
		fontWeight: 'bold',
		shadow: false,
		textAlign: 'left'
      },
      plot: {
        tooltip: {
          padding: '5px 10px',
          backgroundColor: '#707e94',
          borderRadius: '6px',
          fontColor: '#fff',
          fontFamily: 'Arial',
          fontSize: '11px',
          shadow: false
        },
        animation: {
          delay: 500,
          effect: 'ANIMATION_SLIDE_BOTTOM'
        },
        barWidth: '33px',
        hoverState: {
          visible: false
        }
      },
      plotarea: {
        margin: '45px 20px 38px 45px'
      },
      scaleX: {
        values: ['Selected Works', 'Ignored Works', 'Selected Affiliations', 'Ignored Affiliations'],
        guide: {
          visible: false
        },
        item: {
          paddingTop: '2px',
          fontColor: '#8391a5',
          fontFamily: 'Arial',
          fontSize: '11px'
        },
        itemsOverlap: true,
        lineColor: '#d2dae2',
        maxItems: 9999,
        offsetY: '1px',
        tick: {
          lineColor: '#d2dae2',
          visible: false
        }
      },
      scaleY: {
        values: '0:".(evenOrOdd($maxValue))."',
        guide: {
          rules: [
            {
              lineWidth: '0px',
              rule: '%i == 0'
            },
            {
              alpha: 0.4,
              lineColor: '#d2dae2',
              lineStyle: 'solid',
              lineWidth: '1px',
              rule: '%i > 0'
            }
          ]
        },
        item: {
          paddingRight: '5px',
          fontColor: '#8391a5',
          fontFamily: 'Arial',
          fontSize: '10px'
        },
        lineColor: 'none',
        maxItems: 4,
        maxTicks: 4,
        tick: {
          visible: false
        }
      },
      series: [
        {
          values: [".$worksCounter.", ".$worksIgnoredCounter.", ".$affiliationsCounter.", ".$affiliationsIgnoredCounter."],
          styles: [
            {
              backgroundColor: '#".$colors[4]."'
            },
            {
              backgroundColor: '#".$colors[1]."'
            },
            {
              backgroundColor: '#".$colors[2]."'
            },
            {
              backgroundColor: '#".$colors[3]."'
            }
          ]
        }
      ]
    }

] };

		zingchart.render({
		  id: 'myChart3',
		  data: chartConfig3,
		    height: '430px',


		});
<!------------------------------- End of bar chart -------------------------------------------------------------------------------------

		</script>

		</body>
		</html> ";

		return $dashboard;
	}
