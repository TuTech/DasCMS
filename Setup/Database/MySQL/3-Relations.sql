CREATE TABLE IF NOT EXISTS 
relContentsClassesChainedContents(
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
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
relClassesChainedContents(
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
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
relContentsFormatters(
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
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
relContentsTargetViews(
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
COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS 
relContentsLocations(
	contentREL
		INTEGER
		UNIQUE
		NOT NULL,
	locationREL
		INTEGER
		NOT NULL,
	INDEX (locationREL)
) 
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


CREATE TABLE IF NOT EXISTS 
relContentsTags(
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
COLLATE utf8_unicode_ci;


-- feed to contents relation
CREATE TABLE IF NOT EXISTS 
relFeedsContents(
    feedREL 
        INTEGER 
        NOT NULL,
    contentREL 
        INTEGER 
        NOT NULL,
    INDEX (feedREL),
    INDEX (contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- feed to tags relation
CREATE TABLE IF NOT EXISTS 
relFeedsTags(
    feedREL 
        INTEGER 
        NOT NULL,
    tagREL 
        INTEGER 
        NOT NULL,
    INDEX (feedREL),
    INDEX (tagREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- user and group relation
CREATE TABLE IF NOT EXISTS 
relPermissionTagsGroups(
    permissionTagREL 
        INTEGER 
        NOT NULL,
    groupREL 
        INTEGER 
        NOT NULL,
    INDEX (permissionTagREL),
    UNIQUE (groupREL, permissionTagREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- user and group relation
CREATE TABLE IF NOT EXISTS 
relPermissionTagsUsers(
    permissionTagREL 
        INTEGER 
        NOT NULL,
    userREL 
        INTEGER 
        NOT NULL,
    INDEX (permissionTagREL),
    UNIQUE (userREL, permissionTagREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- user and group relation
CREATE TABLE IF NOT EXISTS 
relUsersGroups(
    userREL 
        INTEGER 
        NOT NULL,
    groupREL 
        INTEGER 
        NOT NULL,
    INDEX (userREL),
    UNIQUE (groupREL, userREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- contents assigned to this aggregator
CREATE TABLE IF NOT EXISTS 
relAggregatorsContents(
    contentAggregatorREL 
        INTEGER 
        NOT NULL,
    contentREL 
        INTEGER 
        NOT NULL,
    INDEX(contentAggregatorREL),
    UNIQUE(contentREL, contentAggregatorREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- contents using an aggregator
CREATE TABLE IF NOT EXISTS 
relContentsAggregator(
    contentREL 
        INTEGER 
        NOT NULL,
    contentAggregatorREL 
        INTEGER 
        NOT NULL,
    INDEX(contentAggregatorREL),
    UNIQUE(contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;
