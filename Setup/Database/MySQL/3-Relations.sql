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
