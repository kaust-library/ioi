<?php
	//General constants
	define('TODAY', date("Y-m-d"));

	//Local institution details
	define('INSTITUTION_ABBREVIATION', '');

	define('INSTITUTION_NAME', '');

	define('INSTITUTION_CITY', '');

	define('INSTITUTION_COUNTRY', '');

	define('INSTITUTION_RINGGOLD_ID', '');

	define('IR_EMAIL', '');

	define('LOCAL_LIBGUIDE_URL', '');
	
	define('LOCAL_TRAINING_URL', '');

	//LDAP constants
	define('LDAP_ACCOUNT_SUFFIX', ''); //binding parameters
	
	define('LDAP_HOSTNAME_SSL', ''); // space-separated list of valid hostnames for failover
	
	define('LDAP_BASE_DN', '');
	
	define('LDAP_STUDENT_DN_STRING', '');
	
	define('LDAP_PERSON_ID_ATTRIBUTE', '');
	
	define('LDAP_EMAIL_ATTRIBUTE', '');
	
	define('LDAP_NAME_ATTRIBUTE', '');
	
	define('LDAP_TITLE_ATTRIBUTE', '');
	
	define('LDAP_DEPARTMENT_ATTRIBUTE', '');
	
	define('LDAP_START_DATE_ATTRIBUTE', '');
	
	//MySQL Credentials
	define('MYSQL_USER', 'ioi');
	define('MYSQL_PW', '');

	// DSpace repository details
	define('DSPACE_VERSION', '5');
	//define('DSPACE_VERSION', '6');
	
	define('REPOSITORY_USER', '');
	define('REPOSITORY_PW', '');

	define('REPOSITORY_BASE_URL', '');

	define('REPOSITORY_URL', 'https://'.REPOSITORY_BASE_URL);

	define('REPOSITORY_OAI_URL', REPOSITORY_URL.'/oai/request?');

	define('REPOSITORY_OAI_ID_PREFIX', 'oai:'.REPOSITORY_BASE_URL.':');

	define('REPOSITORY_API_URL', REPOSITORY_URL.'/rest/');

	define('ORCID_ENABLED_FIELDS', array('dc.contributor.author','dc.contributor.advisor','dc.contributor.committeemember'));

	//ORCID connection details
	define('ORCID_MEMBER', true); // change to false if ORCID client credentials are for the public API only

	define('ORCID_PRODUCTION', false); // change to true when ready to leave the sandbox

	if(ORCID_PRODUCTION)
	{
		//production credentials
		define('OAUTH_CLIENT_ID', '');
		define('OAUTH_CLIENT_SECRET', '');

		//production endpoints
		define('OAUTH_REDIRECT_URI', ''); // URL of the main user script, public/orcid.php
		define('OAUTH_AUTHORIZATION_URL', 'https://orcid.org/oauth/authorize');
		define('OAUTH_TOKEN_URL', 'https://orcid.org/oauth/token');
		define('ORCID_API_URL', 'https://api.orcid.org/v3.0/');
		define('PUBLIC_API_URL', 'https://pub.orcid.org/v3.0/');
		define('ORCID_LINK_BASE_URL', 'https://orcid.org/');
	}
	else
	{
		//sandbox credentials
		define('OAUTH_CLIENT_ID', '');
		define('OAUTH_CLIENT_SECRET', '');

		//sandbox endpoints
		define('OAUTH_REDIRECT_URI', ''); // URL of the main user script, public/orcid.php
		define('OAUTH_AUTHORIZATION_URL', 'https://sandbox.orcid.org/oauth/authorize');
		define('OAUTH_TOKEN_URL', 'https://sandbox.orcid.org/oauth/token');
		define('ORCID_API_URL', 'https://api.sandbox.orcid.org/v3.0/');
		define('PUBLIC_API_URL', 'https://pub.sandbox.orcid.org/v3.0/');
		define('ORCID_LINK_BASE_URL', 'https://sandbox.orcid.org/');
	}	
?>
