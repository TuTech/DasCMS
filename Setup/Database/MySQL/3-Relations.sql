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

-- feed to contents relation
CREATE TABLE IF NOT EXISTS 
relContentsPreviewImages(
    contentREL 
        INTEGER 
        UNIQUE
        NOT NULL,
    previewREL 
        INTEGER 
        NOT NULL,
    INDEX (previewREL)
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


-- IMAP Account flags
CREATE TABLE IF NOT EXISTS 
relMailImportAccountsMailImportFlags(
	mailImportAccountREL
		INTEGER 
		NOT NULL,
	mailImportFlagREL
		INTEGER
		NOT NULL
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

-- user role relation
CREATE TABLE IF NOT EXISTS 
relPersonsRoles(
    contentREL 
        INTEGER 
        NOT NULL,
    personRoleREL
        INTEGER 
        NOT NULL,
    INDEX (personRoleREL),
    UNIQUE (contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- user role relation
CREATE TABLE IF NOT EXISTS 
relPersonsPermissions(
    contentREL 
        INTEGER 
        NOT NULL,
    personPermissionREL
        INTEGER 
        NOT NULL,
    UNIQUE (contentREL, personPermissionREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- user role relation
CREATE TABLE IF NOT EXISTS 
relPersonsPermissionTags(
    contentREL 
        INTEGER 
        NOT NULL,
    tagREL
        INTEGER 
        NOT NULL,
    UNIQUE (contentREL, tagREL)
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
