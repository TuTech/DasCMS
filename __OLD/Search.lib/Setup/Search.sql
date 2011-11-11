-- --
-- name: searchesTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Searches(
    searchID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    normalizedSearch
        VARCHAR(1024)
        NOT NULL,
    searchHash
        CHAR(40)
        UNIQUE
        NOT NULL,
    created
		TIMESTAMP
		DEFAULT CURRENT_TIMESTAMP
        NOT NULL,
    runTime
		FLOAT(10,10)
		UNSIGNED
		NOT NULL
		DEFAULT '0.0000000000',
	foundItems
		INT
		UNSIGNED
		NOT NULL
		DEFAULT '0'
)
ENGINE = MEMORY
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: searchResultsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__SearchResults(
	searchREL
		INTEGER
		NOT NULL,
	contentREL
		INTEGER
		NOT NULL,
	score
		FLOAT(10,10)
		UNSIGNED
		NOT NULL
		DEFAULT '0.0000000000',
	itemNr
		INTEGER
		UNSIGNED
		NULL,
	UNIQUE(searchREL, contentREL),
	INDEX(contentREL),
	INDEX(itemNr)
)
ENGINE = MEMORY
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: searchResultsReferences
-- type: alter
ALTER TABLE
__PFX__SearchResults
    ADD FOREIGN KEY (searchREL)
        REFERENCES __PFX__Searches(searchID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION
