-- --
-- name: accessLogTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__AccessLog(
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
COLLATE utf8_unicode_ci

-- --
-- name: aliasTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__Aliases(
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
COLLATE utf8_unicode_ci

-- --
-- name: changesTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__Changes(
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
    latest
        ENUM('Y', 'N')
        DEFAULT 'Y'
        NOT NULL,
    INDEX changes_date (changeDate),
    INDEX (contentREL),
    INDEX (userREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: changedByUsersTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__ChangedByUsers(
    changedByUserID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    login
        VARCHAR(64)
        UNIQUE
        NOT NULL
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci


-- --
-- name: contentTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Contents(
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
	revokeDate
		DATETIME
		NOT NULL
		DEFAULT '0000-00-00 00:00:00',
	published
		INT(1)
		NOT NULL
		DEFAULT 0
    description
        TEXT
        NOT NULL,
    allowSearchIndexing
        ENUM('Y', 'N')
        DEFAULT 'Y'
        NOT NULL,
    INDEX contents_title_desc (title, description(32)),
    INDEX (type),
    INDEX (mimetypeREL),
    INDEX (pubDate),
	INDEX(published)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: formattersTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Formatters(
    formatterID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    name
        VARCHAR(32)
        UNIQUE
        NOT NULL,
    formatterData
    	TEXT
    	NOT NULL
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: mimetypesTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Mimetypes(
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
COLLATE utf8_unicode_ci

-- --
-- name: tagsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Tags(
    tagID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    tag
        varchar(128)
        UNIQUE
        NOT NULL
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_swedish_ci
COMMENT "utf8_swedish_ci because utf8_unicode_ci treats some chars as equal that aren't ('ü' equals 'u')"

-- --
-- name: tagScoresTable
-- type: create
CREATE TABLE  IF NOT EXISTS
__PFX__TagScores(
	tagREL
		int(11)
		NOT NULL
		UNIQUE,
	score
		float(4,4)
		NOT NULL
		DEFAULT 0,
	percent
		TINYINT
		NOT NULL
		DEFAULT 0
)
ENGINE=InnoDB
CHARSET=utf8
COLLATE=utf8_unicode_ci;

-- --
-- name: sporeviewsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__SporeViews(
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
COLLATE utf8_unicode_ci

-- --
-- name: relContentsClassesChainedContentsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relContentsClassesChainedContents(
    ownerContentREL
        INTEGER
        NOT NULL,
    chainingClassREL
        INTEGER
        NOT NULL,
    chainedContentREL
        INTEGER
        NOT NULL,
    UNIQUE (ownerContentREL, chainingClassREL, chainedContentREL),
    INDEX(chainingClassREL),
    INDEX(chainedContentREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relClassesChainedContentsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relClassesChainedContents(
    chainingClassREL
        INTEGER
        NOT NULL,
    chainedContentREL
        INTEGER
        NOT NULL,
    UNIQUE (chainingClassREL, chainedContentREL),
    INDEX(chainedContentREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relContentsTargetViews
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relContentsTargetViews(
    contentREL
        INTEGER
        UNIQUE
        NOT NULL,
    viewREL
        INTEGER
        NOT NULL,
    INDEX (viewREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relContentsTagsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relContentsTags(
	contentREL
		INTEGER
		NOT NULL,
	tagREL
		INTEGER
		NOT NULL,
	INDEX (contentREL),
	INDEX (tagREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relContentsFormattersTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relContentsFormatters(
    contentREL
        INTEGER
        UNIQUE
        NOT NULL,
    formatterREL
        INTEGER
        NOT NULL,
	classREL
		INTEGER
		NULL,
    INDEX(formatterREL),
	UNIQUE(classREL, contentREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: accessLogReferences
-- type: alter
ALTER TABLE
__PFX__AccessLog
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
-- --
-- name: aliasesReferences
-- type: alter
ALTER TABLE
__PFX__Aliases
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: changesReferences
-- type: alter
ALTER TABLE
__PFX__Changes
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (userREL)
        REFERENCES __PFX__ChangedByUsers(changedByUserID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT

-- --
-- name: contentsReferences
-- type: alter
ALTER TABLE
__PFX__Contents
    ADD FOREIGN KEY (type)
        REFERENCES __PFX__Classes(classID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD FOREIGN KEY (mimetypeREL)
        REFERENCES __PFX__Mimetypes(mimetypeID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD FOREIGN KEY (primaryAlias)
        REFERENCES __PFX__Aliases(aliasID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (GUID)
        REFERENCES __PFX__Aliases(aliasID)
        ON DELETE CASCADE
        ON UPDATE RESTRICT


-- --
-- name: relContentsClassesChainedContentsReferences
-- type: alter
ALTER TABLE
__PFX__relContentsClassesChainedContents
    ADD FOREIGN KEY (ownerContentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (chainingClassREL)
        REFERENCES __PFX__Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (chainedContentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION

-- --
-- name: relClassesChainedContentsReferences
-- type: alter
ALTER TABLE
__PFX__relClassesChainedContents
    ADD FOREIGN KEY (chainingClassREL)
        REFERENCES __PFX__Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (chainedContentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION

-- --
-- name: relContentsFormattersReferences
-- tape: alter
ALTER TABLE
__PFX__relContentsFormatters
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (formatterREL)
        REFERENCES __PFX__Formatters(formatterID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
	ADD FOREIGN KEY (classREL)
        REFERENCES __PFX__Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relContentsTagsReferences
-- type: alter
ALTER TABLE
__PFX__relContentsTags
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (tagREL)
        REFERENCES __PFX__Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relTagsScoresReferences
-- type: alter
ALTER TABLE
__PFX__TagScores
	ADD FOREIGN KEY (tagREL)
	REFERENCES __PFX__Tags (tagID)
	ON DELETE CASCADE
	ON UPDATE NO ACTION

-- --
-- name: sporeViewsReferences
-- type: alter
ALTER TABLE
__PFX__SporeViews
    ADD FOREIGN KEY (defaultContentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (errorContentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION

-- --
-- name: relContentsTargetViewsReferences
-- type: alter
ALTER TABLE
__PFX__relContentsTargetViews
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (viewREL)
        REFERENCES __PFX__SporeViews(viewID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION
-- --
-- name: mimetypesInit
-- type: insert
INSERT 
	INTO __PFX__Mimetypes (mimetypeID, mimetype)
	VALUES (1, 'cms/internal')
