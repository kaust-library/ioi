-- phpMyAdmin SQL
-- version 4.5.4.1, ubuntu2.1
-- http://www.phpmyadmin.net


-- ---------- This file contains the ioi database structure and some default or sample data for certain tables.
-- --------------------- Created by : Daryl Grenz and Yasmeen Alsaedy
-- ----------- Institute : King Abdullah University of Science and Technology | KAUST
-- ----------------------------- Date : 16 April - 10:30 AM
--



--
-- Database: ioi
--

-- --------------------------------------------------------

--
-- - Table structure for table emailTemplates
-- - Description : This table stores templates used for sending batch emails.
--

CREATE TABLE `emailTemplates` (
  `templateID` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `template` longtext NOT NULL,
  `lastUpdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- sample data for table emailTemplates
--

INSERT INTO `emailTemplates` (`templateID`, `label`, `template`, `lastUpdated`) VALUES
(1, 'membership', '<html>\n<body>\n\n<p>Dear {{givenName}},</p>\n\n<p>We are pleased to announce that {{INSTITUTION_ABBREVIATION}} has joined ORCID (Open Researcher and Contributor Identifiers) as an institutional member. <a href="{{ORCID_LINK_BASE_URL}}">ORCID</a> is an independent, non-profit effort to provide an open registry of unique researcher identifiers and open services to link research activities and organizations to these identifiers. Having an ORCID identifier will help you gather, manage and promote all of your research activities, while distinguishing your research from others who might have a similar name. Our first goal is to support all researchers in creating an ORCID identifier. Please click the link below to create an ORCID ID or identify your existing one.</p>\n\n<p>\n<a href="{{OAUTH_REDIRECT_URI}}">ORCID at {{INSTITUTION_ABBREVIATION}}</a>\n</p>\n\n<p>Please contact us with any questions.</p>\n\n<p>Sincerely,</p>\n<p>{{sender}}</p>\n<p>on behalf of the University Library</p>\n</body>\n</html>', '2019-10-19 12:04:24'),
(2, 'initial', '<html>\n<body>\n\n<p>Dear {{givenName}},</p>\n\n<p><a href="{{ORCID_LINK_BASE_URL}}">ORCID</a> (Open Researcher and Contributor Identifiers) is an independent, non-profit effort to provide an open registry of unique researcher identifiers and open services to link research activities and organizations to these identifiers. As an institutional member of ORCID, {{INSTITUTION_ABBREVIATION}} provides the ORCID at {{INSTITUTION_ABBREVIATION}} tool for you to connect your ORCID iD to {{INSTITUTION_ABBREVIATION}}. This allows your ORCID record to be automatically updated with information about your affiliation to {{INSTITUTION_ABBREVIATION}} and the research you do here (including the completion of your thesis or dissertation). Please click the link below to create an ORCID iD (if you have an existing iD please also use this tool to identify it for us).</p>\n\n<p>\n<a href="{{OAUTH_REDIRECT_URI}}">ORCID at {{INSTITUTION_ABBREVIATION}}</a>\n</p>\n\n<p>If you want more information or have questions, you can attend our ORCID training (next training is  from , sign up <a href="{{LOCAL_TRAINING_URL}}">here</a>.), check out our <a href="{{LOCAL_LIBGUIDE_URL}}">ORCID libguide</a>, or email us.</p>\n\n<p>Sincerely,</p>\n<p>{{sender}}</p>\n<p>on behalf of the University Library</p>\n</body>\n</html>', '2019-10-19 12:04:24'),
(3, 'followup', '<html>\n<body>\n\n<p>Dear {{givenName}},</p>\n\n<p><a href="{{ORCID_LINK_BASE_URL}}">ORCID</a> (Open Researcher and Contributor Identifiers) is an independent, non-profit effort to provide an open registry of unique researcher identifiers and open services to link research activities and organizations to these identifiers. As an institutional member of ORCID, {{INSTITUTION_ABBREVIATION}} provides the ORCID at {{INSTITUTION_ABBREVIATION}} tool for you to connect your ORCID iD to {{INSTITUTION_ABBREVIATION}}. This allows your ORCID record to be automatically updated with information about your affiliation to {{INSTITUTION_ABBREVIATION}} and the research you do here (including the completion of your thesis or dissertation). \n</p>\n\n<p>\nOur records indicate that you have not yet connected an ORCID iD to {{INSTITUTION_ABBREVIATION}}. Please click the link below to create an ORCID iD (if you have an existing ORCID iD please also use this tool to identify it for us).\n</p>\n\n<p>\n<a href="{{OAUTH_REDIRECT_URI}}">ORCID at {{INSTITUTION_ABBREVIATION}}</a>\n</p>\n\n<p>If you want more information or have questions, you can attend our ORCID training (next training is  from , sign up <a href="{{LOCAL_TRAINING_URL}}">here</a>.), check out our <a href="{{LOCAL_LIBGUIDE_URL}}">ORCID libguide</a>, or email us.</p>\n\n<p>Sincerely,</p>\n<p>{{sender}}</p>\n<p>on behalf of the University Library</p>\n</body>\n</html>', '2019-10-19 12:04:24'),
(4, 'noPermissions', '<html>\n<body>\n\n<p>Dear {{givenName}},</p>\n\n<p><a href="{{ORCID_LINK_BASE_URL}}">ORCID</a> (Open Researcher and Contributor Identifiers) is an independent, non-profit effort to provide an open registry of unique researcher identifiers and open services to link research activities and organizations to these identifiers. As an institutional member of ORCID, {{INSTITUTION_ABBREVIATION}} provides the ORCID at {{INSTITUTION_ABBREVIATION}} tool for you to connect your ORCID iD to {{INSTITUTION_ABBREVIATION}}. This allows your ORCID record to be automatically updated with information about your affiliation to {{INSTITUTION_ABBREVIATION}} and the research you do here (including the completion of your thesis or dissertation).\n</p>\n\n<p>\nOur records indicate that you have the ORCID iD <a href="{{ORCID_LINK_BASE_URL}}{{ORCID}}">{{ORCID}}</a>, but have not yet granted permissions for {{INSTITUTION_ABBREVIATION}} to update your ORCID record with works and publication information. Please click the link below to connect your ORCID iD to {{INSTITUTION_ABBREVIATION}}.\n</p>\n\n<p>\n<a href="{{OAUTH_REDIRECT_URI}}">ORCID at {{INSTITUTION_ABBREVIATION}}</a>\n</p>\n\n<p>If you want more information or have questions, you can attend our ORCID training (next training is  from , sign up <a href="{{LOCAL_TRAINING_URL}}">here</a>.), check out our <a href="{{LOCAL_LIBGUIDE_URL}}">ORCID libguide</a>, or email us.</p>\n\n<p>Sincerely,</p>\n<p>{{sender}}</p>\n<p>on behalf of the University Library</p>\n</body>\n</html>', '2019-10-19 12:04:24');

--
-- - Edit the existing templates before sending emails. But if adding new place holders or an entirely new template, you will have to update the sendEmails function.
--

-- --------------------------------------------------------

--
-- - Table structure for table groups
-- - Description : This table is a lookup table, it is used to define groups or categories of people inside the institution based on all or part of their job title.
--

CREATE TABLE `groups` (
  `groupID` int(11) NOT NULL,
  `group` varchar(50) NOT NULL,
  `titles` text NOT NULL,
  `titleParts` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- default data for table groups
--

INSERT INTO `groups` (`groupID`, `group`, `titles`, `titleParts`) VALUES
(1, 'Students', 'MS Student||PhD Student', ''),
(2, 'Faculty', '', 'prof.||professor'),
(3, 'Postdocs', 'Post-Doctoral Fellow||Postdoctoral Fellow', '');

--
-- - To create a new group, just add their definition to this table
--

-- --------------------------------------------------------

--
-- - Table structure for table ignored
-- - Description: This table is managed by the application to store the works and affiliations that users want ignored.

-- -  Columns :
  -- --- rowID : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- orcid : unique iD for each user (ORCID iD).
  -- --- type : work, employment or educations.
  -- --- localSourceRecordID : Unique id for each work and affiliation.
  -- --- ignored : time when the works or affiliations were ignored by the user.
  -- --- deleted : time when previously ignored works or affiliations were reselected by the user (no longer ignored).
--


CREATE TABLE `ignored` (
  `rowID` int(11) NOT NULL ,
  `orcid` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `localSourceRecordID` varchar(255) NOT NULL,
  `ignored` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- - This table contains no default data, it will be filled automatically by the tool.
--

-- --------------------------------------------------------

--
-- - Table structure for table mappings
-- - Description: This table provides a mapping from standard dspace metadata fields or values to orcid fields or values.

-- - Columns :
  -- --- mappingID : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- source : the sourceField column source.
  -- --- sourceField : dspace fields.
  -- --- entryType : work or workType
  -- --- place : order in which the work fields need to appear according to the orcid metadata schema
  -- --- orcidField :
--

CREATE TABLE `mappings` (
  `mappingID` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `sourceField` varchar(50) NOT NULL,
  `entryType` varchar(50) NOT NULL,
  `place` int(11) DEFAULT NULL,
  `orcidField` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table mappings
--

INSERT INTO `mappings` (`mappingID`, `source`, `sourceField`, `entryType`, `place`, `orcidField`) VALUES
(1, 'dspace', 'dc.title', 'work', 1, 'title'),
(2, 'dspace', 'dc.identifier.journal', 'work', 2, 'journal-title'),
(3, 'dspace', 'dc.type', 'work', 3, 'type'),
(4, 'dspace', 'dc.date.issued', 'work', 4, 'publication-date'),
(5, 'dspace', 'dc.identifier.doi', 'work', 5, 'external-ids.doi'),
(6, 'dspace', 'dc.identifier.uri', 'work', 6, 'external-ids.handle'),
(7, 'dspace', 'dc.identifier.uri', 'work', 8, 'url'),
(9, 'dspace', 'dc.identifier.pmid', 'work', 7, 'external-ids.pmid'),
(10, 'dspace', 'Article', 'workType', NULL, 'journal-article'),
(11, 'dspace', 'Book', 'workType', NULL, 'book'),
(12, 'dspace', 'Book Chapter', 'workType', NULL, 'book-chapter'),
(13, 'dspace', 'Conference Paper', 'workType', NULL, 'conference-paper'),
(14, 'dspace', 'Dataset', 'workType', NULL, 'dataset'),
(15, 'dspace', 'Technical Report', 'workType', NULL, 'report'),
(16, 'dspace', 'Poster', 'workType', NULL, 'conference-poster'),
(17, 'dspace', 'Dissertation', 'workType', NULL, 'dissertation-thesis'),
(18, 'dspace', 'Thesis', 'workType', NULL, 'supervised-student-publication'),
(19, 'dspace', 'Patent', 'workType', NULL, 'patent'),
(20, 'dspace', 'Presentation', 'workType', NULL, 'lecture-speech'),
(21, 'dspace', 'Preprint', 'workType', NULL, 'working-paper'),
(22, 'dspace', 'Software', 'workType', NULL, 'software');



-- --------------------------------------------------------



--
-- - Table structure for table messages
-- - Description: This table stores reports and summaries from the crontab tasks and admin form actions.

-- - Columns :
  -- --- messageID : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- process : the name of the process that ran.
  -- --- type : the type of the message ( report or summary).
  -- --- message : text of the message.
  -- --- timestamp : the data and time that the message was inserted in the table.
--

CREATE TABLE `messages` (
  `messageID` int(11) NOT NULL,
  `process` varchar(50) NOT NULL,
  `type` varchar(20) NOT NULL,
  `message` longtext NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- - This table contains no default data, it will be filled automatically by the tool.
--


-- --------------------------------------------------------


--
-- - Table structure for table metadata
-- - Description: This table contains all the harvested metadata from dspace, as well as local person and org data, and some system entries with ioi as the source.

-- - Columns :
  -- --- rowID : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- source : the source of the data.
  -- --- idInSource : id of related record in the source.
  -- --- parentRowID : children refer to the rowID of their parents to allow nested or hierarchical entries
  -- --- field : field name in form namespace.element.qualifier
  -- --- place : place of multiple values in order from the source
  -- --- value : metadata value.
  -- --- added : the date and time that the row was inserted.
  -- --- deleted : the date and time when the row was marked as deleted or replaced with another row.
  -- --- replacedByRowID : the replacement row, if any.
--

CREATE TABLE `metadata` (
  `rowID` int(11) NOT NULL,
  `source` varchar(50) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `parentRowID` int(11) DEFAULT NULL,
  `field` varchar(200) NOT NULL,
  `place` int(11) NOT NULL,
  `value` longtext NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- - This table contain no default data, it will be filled by the tool.
--


-- --------------------------------------------------------

--
-- - Table structure for table orcids
-- - Description: This table contains the list of known email addresses and ORCID iDs.


-- - Columns :
  -- --- email : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- orcid : unique iD for each user (ORCID iD).
  -- --- name : user full name.
  -- --- added : the date and time that the row was inserted in the table.
--

CREATE TABLE `orcids` (
  `email` varchar(100) NOT NULL,
  `orcid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- - This table contains no default data, it will be filled automatically by the tool.
--


-- --------------------------------------------------------

--
-- - Table structure for table putCodes
-- - Description: This table saves the putCodes that come from ORCID after works and affiliations are posted.


-- - Columns :
  -- --- rowID : AUTO INCREMENT column it will increase automatically when inserting new row.
  -- --- type : the type of entry ( work, affiliation or education).
  -- --- putCode : unique number for each item on a given ORCID record.
  -- --- localSourceRecordID : unique id in the source.
  -- --- submittedData : copy of the full data transmitted through the API.
  -- --- format : format of the submitted data.
  -- --- apiResponse : the API response.

--

CREATE TABLE `putCodes` (
  `rowID` int(11) NOT NULL,
  `orcid` varchar(30) NOT NULL,
  `type` varchar(30) NOT NULL,
  `putCode` varchar(30) NOT NULL,
  `localSourceRecordID` varchar(100) NOT NULL,
  `submittedData` longtext NOT NULL,
  `format` varchar(10) NOT NULL,
  `apiResponse` text NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- - This table contains no default data, it will be filled automatically by the tool.
--



-- --------------------------------------------------------

--
-- - Table structure for table sourceData
-- - Description: This table contains original metadata as retrieved from a source


-- - Columns :
  -- --- idInSource : id of the record in the source system.
  -- --- source : name of the source system.
  -- --- sourceData : full record.
  -- --- format : format of record (JSON or XML).


--

CREATE TABLE `sourceData` (
  `rowID` int(11) NOT NULL,
  `source` varchar(30) NOT NULL,
  `idInSource` varchar(100) NOT NULL,
  `sourceData` longtext NOT NULL,
  `format` varchar(10) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted` timestamp NULL DEFAULT NULL,
  `replacedByRowID` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- - This table contains no default data, it will be filled automatically by the tool.
--


-- --------------------------------------------------------

--
-- - Table structure for table tokens
-- - Description: This table contains all the user tokens from ORCID.


-- - Columns :
  -- --- access_token : unique string for permissions granted related to a given ORCID iD.
  -- --- expiration : the expiration date and time for the access token .
  -- --- scope : scope of interactions the user has granted to the tool.
  -- --- name : the user name.
  -- --- created : the date and time when the token was created.
  -- --- refresh_token : unique string for each user, used to generate additional access tokens.
  -- --- deleted: the date and time when the access token was deleted or replaced.


--

CREATE TABLE `tokens` (
  `access_token` varchar(50) NOT NULL,
  `expiration` datetime NOT NULL,
  `scope` varchar(200) NOT NULL,
  `orcid` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created` datetime NOT NULL,
  `refresh_token` varchar(200) NOT NULL,
  `deleted` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



--
-- - This table contains no default data, it will be filled automatically by the tool.
--


-- --------------------------------------------------------

--
-- - Table structure for table users
-- - Description: This table lists tool users and indicates if they should have access to admin.php (the admin dashboard and forms page).


CREATE TABLE `users` (
  `email` varchar(100) NOT NULL,
  `admin` int(11) NOT NULL,
  `added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- - This table should be filled manually.
--

-- ------------------- Table restrictions ----------------------------

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`groupID`),
  ADD UNIQUE KEY `group` (`group`);

--
-- Indexes for table ignored
--
ALTER TABLE `ignored`
  ADD PRIMARY KEY (`rowID`);

--
-- Indexes for table mappings
--
ALTER TABLE `mappings`
  ADD PRIMARY KEY (`mappingID`);

--
-- Indexes for table messages
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`messageID`);

--
-- Indexes for table metadata
--
ALTER TABLE `metadata`
  ADD PRIMARY KEY (`rowID`),
  ADD UNIQUE KEY `checkAll` (`source`,`idInSource`,`parentRowID`,`field`,`place`,`deleted`) USING BTREE,
  ADD KEY `value` (`value`(200)) KEY_BLOCK_SIZE=200,
  ADD KEY `field` (`field`),
  ADD KEY `added` (`added`),
  ADD KEY `parentRowID` (`parentRowID`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `sourceFieldValueDeleted` (`source`,`field`,`value`(50),`deleted`),
  ADD KEY `sourceFieldDeleted` (`source`,`field`,`deleted`),
  ADD KEY `sourceIdInSourceDeleted` (`source`,`idInSource`,`deleted`) USING BTREE;

--
-- Indexes for table orcids
--
ALTER TABLE `orcids`
  ADD UNIQUE KEY `orcid` (`orcid`),
  ADD KEY `email` (`email`);

--
-- Indexes for table putCodes
--
ALTER TABLE `putCodes`
  ADD PRIMARY KEY (`rowID`),
  ADD KEY `added` (`added`),
  ADD KEY `replacedBy` (`replacedByRowID`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `putCode` (`putCode`),
  ADD KEY `localSourceRecordID` (`localSourceRecordID`),
  ADD KEY `path` (`orcid`,`type`,`putCode`) USING BTREE,
  ADD KEY `type` (`type`) USING BTREE,
  ADD KEY `format` (`format`);


--
-- Indexes for table sourceData
--
ALTER TABLE `sourceData`
  ADD PRIMARY KEY (`rowID`),
  ADD KEY `added` (`added`),
  ADD KEY `replacedBy` (`replacedByRowID`),
  ADD KEY `deleted` (`deleted`),
  ADD KEY `idInSource` (`idInSource`),
  ADD KEY `source` (`source`,`idInSource`) USING BTREE;

--
-- Indexes for table tokens
--
ALTER TABLE `tokens`
  ADD UNIQUE KEY `access_token` (`access_token`),
  ADD KEY `orcid` (`orcid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table groups
--
ALTER TABLE `groups`
  MODIFY `groupID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table ignored
--
ALTER TABLE `ignored`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table mappings
--
ALTER TABLE `mappings`
  MODIFY `mappingID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;
--
-- AUTO_INCREMENT for table messages
--
ALTER TABLE `messages`
  MODIFY `messageID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table metadata
--
ALTER TABLE `metadata`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
--
-- AUTO_INCREMENT for table putCodes
--
ALTER TABLE `putCodes`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;

--
-- AUTO_INCREMENT for table sourceData
--
ALTER TABLE `sourceData`
  MODIFY `rowID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=0;
