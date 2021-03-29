[![DOI](https://zenodo.org/badge/208052952.svg)](https://zenodo.org/badge/latestdoi/208052952)

# Institutional ORCID Integration (IOI)

Institutional ORCID Integration (IOI) provides a way for institutions to support the use of [ORCID](https://orcid.org/) iDs by their researchers and students.

It is designed for use in conjunction with a [DSpace](https://duraspace.org/dspace/) repository (currently supporting [DSpace](https://duraspace.org/dspace/) versions 5 and 6). It expects the [DSpace expanded-ORCID-support patch](https://atmire.github.io/expanded-ORCID-support) to already have been applied.

It interacts with ORCID using version 3.0 of the ORCID API. For full functionality ORCID membership and access to the ORCID member API are needed. It can also be used with the ORCID public API, but with limited functionality.<br/>


## Prerequisites

IOI has been tested on [Ubuntu](https://ubuntu.com/download/server) using PHP 7 and a MySQL 5.7.27 database. It is also recommended to install  [phpmyadmin](https://www.phpmyadmin.net/downloads/) to interact with the database. 

Then, it is time to create the database structure. Available in the [ioi.sql](ioi.sql) file. 

To automatically run syncronizations between IOI and either DSpace or ORCID [crontab](https://crontab.guru/) tasks need to be set up separately. When running the scripts in the shell you can also provide a date argument to only syncronize items with changes after a certain date by using [php-cgi](https://www.howtoinstall.co/en/ubuntu/xenial/php-cgi).<br/>



## Installing

After finishing the prerequisites, download the IOI code by clicking on the "Clone or download  button" or by using this command line in the terminal:

```commonlisp
cd YourFolderPath
git clone git@github.com:kaust-library/ioi.git 
```
<br/>


To set the constants, including the MySQL database, DSpace REST API, and ORCID API credentials, fill [constants.php](config/constants.php). The path of the file is: 

```commonlisp
config/constants.php
```

<br/>

The database connection is set in [database.php](config/database.php), using the credentials previously set in constants.php, the path of the file is: 

```commonlisp
config/database.php
```

<br/>


## Settings Demo

Watch [the settings demo video](http://hdl.handle.net/10754/659221) to see a demonstration of the setup of the application.<br/><br/>

## Interfaces

The public directory contains three interface endpoints. **Your web server should be set up to point and provide access only to the public directory**.

### User Interface

```commonlisp
public/orcid.php
```

[orcid.php](public/orcid.php) is the primary user interface. It allows users to connect to ORCID, grant permissions to the IOI application and review the information to be transferred.

### Admin Interface

```commonlisp
public/admin.php
```

[admin.php](public/admin.php) is accessible to users listed as admin in the users table. It provides a dashboard of usage statistics for the application, as well as forms for sending emails to designated groups, updating the name attached to an ORCID iD in DSpace, and uploading organizational and persons information to the database.

### Query Interface

```commonlisp
public/query.php
```

[query.php](public/query.php) provides a basic endpoint for another university system to retrieve information as JSON or CSV about the ORCID iDs and works recorded in the application. **You should define your own method of restricting access to this endpoint**

<br/>

## Deployment

Production usage of the tool requires setting tasks to run periodically that synchronize information between the application, ORCID and DSpace. The task files are located in the [tasks](tasks) folder. To set the [crontab](https://crontab.guru/) tasks, open the terminal and sign in to your server. You can open the list of crontasks for your user by entering this command:

```commonlisp
crontab -e
```

A file will open.

Each [crontab](https://crontab.guru/) task starts with five fields that define the frequency that the task will be run:

<img src="https://i2.wp.com/www.adminschoice.com/wp-content/uploads/2009/12/crontab-layout.png?w=775&ssl=1" alt="q" style="zoom:60%;" />

Below are sample crontab entries for the IOI tasks:

1. **Harvest new and modified repository records from [DSpace](https://duraspace.org/dspace/) to the metadata table**. This task is responsible for adding new or modified metadata records to the metadata table in the database. This is how you keep track of new works added to your institutional repository.

    To run it every 10 minutes, add this line to the crontab:

    ```commonlisp
    */10 * * * * /usr/bin/php YourFolderPath/ioi/tasks/harvestRepository.php
    ```

    If you want to do an initial harvest or test harvest only for items added or modified after a certain date, and have installed php-cgi, you could also do the below:

    ```commonlisp
    */10 * * * * /usr/bin/php-cgi YourFolderPath/ioi/tasks/harvestRepository.php fromDate=YYYY-MM-DD
    ```
<br/><br/>
  

2. **Synchronizing works from IOI to [ORCID](https://orcid.org/)**. This task is responsible for updating the user records in ORCID with new works harvested to IOI from the repository, based on the permissions the users have already given the system.

   To run it every day at 3 AM, add this line to the crontab:

   ```commonlisp
   0 3 * * * /usr/bin/php YourFolderPath/ioi/tasks/syncWorksToORCID.php
   ```

   <br/><br/>

   
3. **Send updated ORCID metadata into [DSpace](https://duraspace.org/dspace/)**. This task will check that ORCID iDs are added to the DSpace record for each work that the user has selected as their own:

    To run it every 30 minutes, add this line to the crontab:

   ```commonlisp
   */30 * * * * /usr/bin/php YourFolderPath/ioi/tasks/updateORCIDiDsInDSpace.php
   ```

<br/><br/>

4. **Synchronizing affiliations from IOI to [ORCID](https://orcid.org/)** ( *optional  task* ). This task is responsible for updating affiliations in user records in ORCID based on the permission they gave to the system before. It only needs to be run if you are uploading new job titles, end dates, etc. for your users to IOI.

   ```commonlisp
   0 3 * * * /usr/bin/php YourFolderPath/ioi/tasks/syncAffiliationsToORCID.php
   ```

<br/><br/>

## Institutional Branding

For the logo, please insert your institutional logo as an image (PNG, GIF, JPEG, etc. should all work ) inside the [images](images/) folder :

```commonlisp
../ioi/public/images
```

Use the name "logo" for your logo image.<br/><br/>


For the icon, please replace `favicon.ico` with your institution's icon in the [public](public/) folder:

```commonlisp
../ioi/public
```
Use the name "favicon" for the icon and the extension must be `.ico` .<br/><br/>

## Built With

* PHP 7 - Primary coding language
* [phpmyadmin](https://www.phpmyadmin.net/downloads/) - Database Management Application
* [Bootstrap](https://getbootstrap.com/) - CSS Framework
* [Jquery](https://jquery.com/) - JavaScript Library 
* [Zing Chart](https://www.zingchart.com/) - JavaScript Chart Framework <br/><br/>





## Authors

* **Daryl Grenz** - Digital Repository Lead
* **Yasmeen Alsaedi** - Repository Coordinator/Architect <br/><br/>





## Acknowledgments

* We would like to thank Atmire for developing the [DSpace expanded-ORCID-support patch](https://atmire.github.io/expanded-ORCID-support), and the ORCID team for their feedback.

  
