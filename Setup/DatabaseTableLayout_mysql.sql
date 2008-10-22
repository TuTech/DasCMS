-- clean up
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS Classes;
DROP TABLE IF EXISTS Contents;
DROP TABLE IF EXISTS Feeds;
DROP TABLE IF EXISTS relFeedsContents;
DROP TABLE IF EXISTS relFeedsTags;
DROP TABLE IF EXISTS Changes;
DROP TABLE IF EXISTS Aliases;
DROP TABLE IF EXISTS Tags;
DROP TABLE IF EXISTS relContentsTags;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Groups;
DROP TABLE IF EXISTS relUsersGroups;

SET FOREIGN_KEY_CHECKS = 1;

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
        NULL,
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
    type 
        INTEGER 
        NOT NULL,
    title 
        VARCHAR(255) 
        NOT NULL,
    pubDate 
        DATETIME 
        NOT NULL 
        DEFAULT 0,
    description 
        VARCHAR(255) 
        NOT NULL 
        DEFAULT '',
    INDEX contents_title_desc (title, description(32)),
    INDEX (type)
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
COLLATE utf8_unicode_ci;

-- relation of contents and tags
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
    primaryGroup
        INTEGER 
        NOT NULL,
    INDEX (primaryGroup)
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
    INDEX (groupREL)
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

-- Foreign keys for relFeedsTags
ALTER TABLE 
Feeds
    ADD CONSTRAINT changed_feed FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for relFeedsTags
ALTER TABLE 
relFeedsTags
    ADD FOREIGN KEY (feedREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (tagREL)
        REFERENCES Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for relFeedsContents
ALTER TABLE 
relFeedsContents
    ADD FOREIGN KEY (feedREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for Contents
ALTER TABLE 
Contents
    ADD CONSTRAINT content_class FOREIGN KEY (type)
        REFERENCES Classes(classID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT,
    ADD CONSTRAINT primary_alias FOREIGN KEY (primaryAlias) 
        REFERENCES Aliases(aliasID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for Aliases
ALTER TABLE 
Aliases
    ADD CONSTRAINT assigned_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for Changes
ALTER TABLE 
Changes
    ADD CONSTRAINT changed_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT changed_by FOREIGN KEY (userREL)
        REFERENCES Users(userID)
        ON DELETE SET NULL
        ON UPDATE NO ACTION;

-- Foreign keys for relContentsTags
ALTER TABLE 
relContentsTags
    ADD CONSTRAINT tagged_content FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT tagged_with FOREIGN KEY (tagREL)
        REFERENCES Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

-- Foreign keys for Users
ALTER TABLE 
Users
    ADD CONSTRAINT primary_group FOREIGN KEY (primaryGroup)
        REFERENCES Groups(groupID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;

-- Foreign keys for relUsersGroups
ALTER TABLE 
relUsersGroups
    ADD CONSTRAINT group_member FOREIGN KEY (userREL)
        REFERENCES Users(userID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT group_relation FOREIGN KEY (groupREL)
        REFERENCES Groups(groupID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

