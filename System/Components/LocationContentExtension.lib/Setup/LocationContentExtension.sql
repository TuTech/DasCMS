-- --
-- name: locationsTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__Locations(
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
		NULL,
	longitude
		DOUBLE
		NULL,
	address
		VARCHAR(2048)
		NULL,
	INDEX(latitude, longitude)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relContentsLocationsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relContentsLocations(
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
COLLATE utf8_unicode_ci

-- --
-- name: relContentsLocationsReferences
-- type: alter
ALTER TABLE
__PFX__relContentsLocations
    ADD FOREIGN KEY (contentREL)
        REFERENCES __PFX__Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (locationREL)
        REFERENCES __PFX__Locations(locationID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION