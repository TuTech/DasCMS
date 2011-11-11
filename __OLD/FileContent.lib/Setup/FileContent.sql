-- --
-- name: fileAttributesTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__FileAttributes(
	contentREL
		INTEGER
		NOT NULL,
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
	UNIQUE(contentREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: fileAttributesReferences
-- type: alter
ALTER TABLE
__PFX__FileAttributes
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION