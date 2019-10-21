<?php
	// change it to 0 when moving to production
	ini_set('display_errors', 1);

	//subdirectories must be added explicitly so that directories holding test files are not included unintentionally
	$directoriesToInclude = array("config" , "functions/admin", "functions/dspace", "functions/local", "functions/orcid", "functions");

	foreach($directoriesToInclude as $directory)
	{
		//load files
		foreach(array_diff(scandir(__DIR__.'/'.$directory), array('..', '.')) as $file)
		{
			if(is_file(__DIR__.'/'.$directory.'/'.$file))
			{
				include_once $directory.'/'.$file;
			}
		}
	}
