-- content accessors
-- countyCodeHash: 
-- 		@cc = 2-letter-country-code; 
-- 		@value = ASCII(LEFT(@cc, 1))*0x100+ASCII(RIGHT(@cc, 1));
CREATE TABLE IF NOT EXISTS 
AccessLog(
    contentREL 
        INTEGER 
        NOT NULL,
    accessTime
    	TIMESTAMP
    	NOT NULL
    	DEFAULT CURRENT_TIMESTAMP,
	countyCodeHash
		INTEGER
		NOT NULL
		DEFAULT 0,
	ipAddressHash
		INTEGER
		NOT NULL,
    INDEX(contentREL),
    INDEX(accessTime),
    INDEX(countyCodeHash),
    UNIQUE(contentREL, accessTime, countyCodeHash, ipAddressHash)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- content accessors
CREATE TABLE IF NOT EXISTS 
Aliases(
    aliasID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    alias 
        VARCHAR(128) 
        UNIQUE 
        NOT NULL,
    contentREL 
        INTEGER 
        NOT NULL,
    INDEX(contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- AtomImports
CREATE TABLE IF NOT EXISTS 
AtomImports(
	atomSourceREL
        INTEGER 
        NOT NULL,
	guid
		VARCHAR(128)
		NOT NULL,
	lastUpdate
		DATETIME
		NOT NULL,
	contentREL
		INTEGER
		NOT NULL,
	UNIQUE (guid),
	INDEX (atomSourceREL),
	INDEX (contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- Atom Sources
CREATE TABLE IF NOT EXISTS 
AtomSources(
	atomSourceID
        INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
        NOT NULL,
	name
		VARCHAR(32)
		NOT NULL,
	url
		VARCHAR(255)
		NOT NULL,
	lastFetched
		TIMESTAMP
		NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- auth log
CREATE TABLE IF NOT EXISTS 
AuthorisationLog(
	LoginTime 
		TIMESTAMP 
		DEFAULT CURRENT_TIMESTAMP 
		NOT NULL,
	IPAdr
		INTEGER 
		NOT NULL,
	UserName
		VARCHAR(32)
		NOT NULL,
	Status
		ENUM('FAIL', 'SUCCESS')
		NOT NULL,
	INDEX(`IPAdr`, `UserName`, `Status`),
	INDEX (`LoginTime`)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;



-- change log
CREATE TABLE IF NOT EXISTS 
Changes(
    contentREL 
        INTEGER 
        NOT NULL,
    title 
        VARCHAR(255) 
        NOT NULL,
    size 
        INTEGER
        NOT NULL,
    changeDate 
        TIMESTAMP 
        NOT NULL 
        DEFAULT CURRENT_TIMESTAMP,
    userREL
        INTEGER 
        NULL,
    INDEX changes_date (changeDate),
    INDEX (contentREL),
    INDEX (userREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- Every class will be listed here 
-- GUID is the global unique id for classes implementing IGlobalUniqueID
CREATE TABLE IF NOT EXISTS  
Classes(
    classID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    class 
        VARCHAR(48) 
        UNIQUE 
        NOT NULL,
    guid 
        VARCHAR(128) 
        UNIQUE
        NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- basic universal content metadata
CREATE TABLE IF NOT EXISTS 
Contents(
    contentID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    primaryAlias 
        INTEGER 
        UNIQUE
        NULL,
    GUID 
        INTEGER 
        UNIQUE
        NULL,
    type 
        INTEGER 
        NOT NULL,
	mimetypeREL
		INTEGER
		NOT NULL
		DEFAULT 1,
    title 
        VARCHAR(255) 
        NOT NULL,
    subtitle
    	VARCHAR(1000)
    	NOT NULL
    	DEFAULT '',
	size
		INT
		NOT NULL
		DEFAULT 0,
    pubDate 
        DATETIME 
        NOT NULL 
        DEFAULT '0000-00-00 00:00:00',
    description 
        TEXT
        NOT NULL 
        DEFAULT '',
    allowSearchIndexing
        ENUM('Y', 'N') 
        DEFAULT 'Y'
        NOT NULL,
    INDEX contents_title_desc (title, description(32)),
    INDEX (type),
    INDEX (mimetypeREL),
    INDEX (pubDate)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- feed type
CREATE TABLE IF NOT EXISTS 
Feeds(
    contentREL 
        INTEGER 
		UNIQUE
        NOT NULL,
    filterType 
        ENUM('All', 'MatchSome', 'MatchAll', 'MatchNone') 
        NOT NULL,
	lastUpdate
		TIMESTAMP
		NOT NULL 
        DEFAULT CURRENT_TIMESTAMP,
	associatedItems
		INTEGER
		NOT NULL
		DEFAULT 0
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- Meta data for CFile
CREATE TABLE IF NOT EXISTS 
FileAttributes(
	contentREL
		INTEGER 
		NOT NULL,
	folderREL
		INTEGER
		NULL,
	originalFileName
		VARCHAR(255)
		NOT NULL,
	suffix
		VARCHAR(12)
		NOT NULL,
	md5sum
		CHAR(32)
		NOT NULL
		DEFAULT '',
	UNIQUE(contentREL),
	INDEX(folderREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- folders for cfiles
CREATE TABLE IF NOT EXISTS 
Folders(
	folderID
        INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
        NOT NULL,
	parentFolderREL
		INTEGER
		NULL,
	name
		VARCHAR(128)
		NOT NULL,
	UNIQUE(parentFolderREL, name)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- groups
CREATE TABLE IF NOT EXISTS 
Groups(
    groupID 
        INTEGER 
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    groupName
        VARCHAR(32)
        UNIQUE 
        NOT NULL,
    description
        VARCHAR(255)
        NOT NULL
        DEFAULT ''
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- Jobs
CREATE TABLE IF NOT EXISTS 
Jobs(
    jobID 
        INTEGER 
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    classREL 
        INTEGER 
        NOT NULL,
    start 
        DATETIME 
        NULL,
    stop 
        DATETIME 
        NULL,
    rescheduleInterval 
        INTEGER 
        NOT NULL,
    UNIQUE (classREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- JobSchedules
CREATE TABLE IF NOT EXISTS 
JobSchedules(
	jobREL
        INTEGER 
        NOT NULL,
	scheduled
		TIMESTAMP
		DEFAULT 0
		NOT NULL,
	started
		TIMESTAMP
		NULL,
	finished
		TIMESTAMP
		NULL,
	exitCode
		INTEGER
		NULL,
	exitMessage
		VARCHAR(64)
		NULL,
	INDEX (jobREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- Locations
CREATE TABLE IF NOT EXISTS 
Locations(
	locationID
        INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
		NOT NULL,
	location
		VARCHAR(128)
		NOT NULL
		UNIQUE,
	latitude
		DOUBLE
		NOT NULL,
	longitude
		DOUBLE
		NOT NULL,
	INDEX(latitude, longitude)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- IMAP Accounts
CREATE TABLE IF NOT EXISTS 
MailImportAccounts(
	mailImportAccountID 
		INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
		NOT NULL,
	label
		VARCHAR(64)
		NOT NULL,
	server
		VARCHAR(128)
		NOT NULL,
	port
		INTEGER
		NOT NULL
		DEFAULT 143,
	mailBox  	
		VARCHAR(255)
		NOT NULL
		DEFAULT 'INBOX',
	username
		VARCHAR(64)
		NOT NULL,
	password
		VARCHAR(64)
		NOT NULL,
	updated
		TIMESTAMP
		NULL,
	status
		Enum('DISABLED', 'ENABLED')
		NOT NULL
		DEFAULT 'ENABLED'
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- IMAP Flags
CREATE TABLE IF NOT EXISTS 
MailImportFlags(
	mailImportFlagID 
		INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
		NOT NULL,
	label
		VARCHAR(32)
		NOT NULL,
	flag
		VARCHAR(24)
		NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- IMAP Mails
CREATE TABLE IF NOT EXISTS 
MailImportMails(
	mailImportMailID 
		INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
		NOT NULL,
	mailImportAccountREL
		INTEGER 
		NOT NULL,
	imapID
		INTEGER 
		NOT NULL,
	messageID
		VARCHAR(255)
		UNIQUE
		NOT NULL,
	sender
		VARCHAR(128)
		NOT NULL,
	updated
		TIMESTAMP
		NOT NULL,
	contentREL
		INTEGER 
		NULL,
	UNIQUE(mailImportAccountREL, imapID)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- mimetypes
CREATE TABLE IF NOT EXISTS 
Mimetypes(
    mimetypeID 
        INTEGER 
		PRIMARY KEY
		AUTO_INCREMENT
        NOT NULL,
    mimetype 
        VARCHAR(64)
		UNIQUE
        NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- permission tags
CREATE TABLE IF NOT EXISTS 
PermissionTags(
    permissionTagREL 
		INTEGER
		UNIQUE
		NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- attribute names
CREATE TABLE IF NOT EXISTS 
PersonAttributes(
	personAttributeID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    personAttribute
    	VARCHAR(64)
    	NOT NULL
    	UNIQUE,
	personAttributeTypeREL
		INTEGER 
        NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- attribute types
CREATE TABLE IF NOT EXISTS 
PersonAttributeTypes(
	personAttributeTypeID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    personAttributeType
    	VARCHAR(32)
    	NOT NULL
    	UNIQUE
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- person attribute contexts relation
CREATE TABLE IF NOT EXISTS 
PersonAttributeContexts(
	personAttributeContextID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    personContextREL 
        INTEGER 
        NOT NULL,
    personAttributeREL 
        INTEGER 
        NOT NULL,
    INDEX (personContextREL),
    UNIQUE (personAttributeREL, personContextREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- attribute contexts
CREATE TABLE IF NOT EXISTS 
PersonContexts(
	personContextID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    personContext
    	VARCHAR(32)
    	NOT NULL
    	UNIQUE
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- attribute contexts
CREATE TABLE IF NOT EXISTS 
PersonRoles(
	personRoleID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    personRole
    	VARCHAR(32)
    	NOT NULL
    	UNIQUE
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- person attribute assignment
CREATE TABLE IF NOT EXISTS 
PersonData(
	contentREL
        INTEGER 
        NOT NULL,
    personAttributeContextREL
    	INTEGER
    	NOT NULL,
	personData
		varchar(4095)
		NOT NULL,
	INDEX (contentREL),
	INDEX (personAttributeContextREL),
	INDEX (personData)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- person attribute assignment
CREATE TABLE IF NOT EXISTS 
PersonPrimaryAttributes(
	contentREL
        INTEGER 
        NOT NULL
        UNIQUE,
	title
		varchar(64)
		NOT NULL
		DEFAULT '',
	forename
		varchar(100)
		NOT NULL
		DEFAULT '',
	surname
		varchar(100)
		NOT NULL
		DEFAULT '',
	company
		varchar(100)
		NOT NULL
		DEFAULT '',
	INDEX (title, forename, surname, company)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- person attribute assignment
CREATE TABLE IF NOT EXISTS 
PersonPermissions(
	personPermissionID
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
	personPermission
		varchar(128)
		NOT NULL
		UNIQUE
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- login credentials for persons
CREATE TABLE IF NOT EXISTS 
PersonLogins(
	contentREL
        INTEGER 
        NOT NULL
        UNIQUE,
    loginName
    	VARCHAR(32)
    	NOT NULL
  		UNIQUE,
    digestHA1
    	VARCHAR(32)
    	NOT NULL,
    digestRealm
    	VARCHAR(32)
    	NOT NULL,
	INDEX (loginName, digestHA1, digestRealm)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- search config
CREATE TABLE IF NOT EXISTS 
SearchConfig(
    contentREL 
        INTEGER 
        NOT NULL,
    `option`
        INTEGER 
        NOT NULL,
    `mode` 
        INTEGER 
        NOT NULL,
	caption
		VARCHAR(64)
		NOT NULL 
        DEFAULT '',
    UNIQUE(contentREL, `option`, `mode`)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;



CREATE TABLE IF NOT EXISTS 
SearchIndexOutdated(
    contentREL 
        INTEGER 
        NOT NULL,
    since
    	TIMESTAMP
    	DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
SearchIndex(
    contentREL 
        INTEGER 
        NOT NULL,
    searchAttributeWeightREL 
        INTEGER 
        NOT NULL,
    searchFeatureREL 
        INTEGER 
        NOT NULL,
    featureCount 
        INTEGER
        NOT NULL,
    UNIQUE (contentREL, searchAttributeWeightREL, searchFeatureREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS 
SearchFeatures(
    searchFeatureID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    searchFeature 
        VARCHAR(48) 
        UNIQUE 
        NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
SearchAttributeWeights(
    searchAttributeWeightID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    attribute 
        VARCHAR(64) 
        UNIQUE 
        NOT NULL,
    weight
    	FLOAT
    	NOT NULL
    	DEFAULT 0.0
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- tags
CREATE TABLE IF NOT EXISTS 
Tags(
    tagID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    tag 
        varchar(64) 
        UNIQUE 
        NOT NULL,
    blocked 
        INTEGER
        NOT NULL
        DEFAULT 0
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_swedish_ci
COMMENT "utf8_swedish_ci because utf8_unicode_ci treats some chars as equal that aren't ('ü' equals 'u')";


-- user list
CREATE TABLE IF NOT EXISTS 
Users(
    userID 
        INTEGER 
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    login 
        VARCHAR(32)
        UNIQUE 
        NOT NULL,
    name 
        varchar(100)
        NOT NULL
        DEFAULT '-',
	email
        varchar(100)
        NOT NULL
        DEFAULT '',
    primaryGroup
        INTEGER 
        NULL,
    INDEX (primaryGroup)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- spore views
CREATE TABLE IF NOT EXISTS 
SporeViews(
    viewID 
        INTEGER 
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    viewName 
        VARCHAR(16)
        UNIQUE 
        NOT NULL,
    active
    	ENUM('N', 'Y') 
        NOT NULL
        DEFAULT 'N',
    defaultContentREL 
        INTEGER 
        NULL,
	errorContentREL
        INTEGER 
        NULL,
    INDEX (defaultContentREL),
    INDEX (errorContentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

