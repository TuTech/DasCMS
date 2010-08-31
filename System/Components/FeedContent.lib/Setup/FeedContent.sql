-- --
-- name: feedsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Feeds(
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
COLLATE utf8_unicode_ci

-- --
-- name: relFeedsContentsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relFeedsContents(
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
COLLATE utf8_unicode_ci

-- --
-- name: relFeedsTagsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relFeedsTags(
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
COLLATE utf8_unicode_ci

-- --
-- name: feedsReferences
-- type: alter
ALTER TABLE
__PFX__Feeds
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relFeedsContentsReferences
-- type: alter
ALTER TABLE
__PFX__relFeedsContents
    ADD FOREIGN KEY (feedREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relFeedsTagsReferences
-- type: alter
ALTER TABLE
__PFX__relFeedsTags
    ADD FOREIGN KEY (feedREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (tagREL)
        REFERENCES __PFX__Tags(tagID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
