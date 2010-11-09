DROP TABLE relContentsLocations;

DROP TABLE Locations;

CREATE TABLE IF NOT EXISTS 
Locations(
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

ALTER TABLE 
relContentsLocations
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (locationREL)
        REFERENCES Locations(locationID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;
 