
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
    INDEX aliases_alias (alias),
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

