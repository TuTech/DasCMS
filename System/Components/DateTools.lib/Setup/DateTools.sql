-- --
-- name: daterunnerTable
-- type: create
CREATE TABLE
__PFX__DateRunner(
	classREL
		INT
		NOT NULL,
	lapse
		DATETIME
		NOT NULL,
	UNIQUE(classREL, lapse)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: daterunnerRefernces
-- type: alter
ALTER TABLE
__PFX__DateRunner
    ADD FOREIGN KEY (classREL)
        REFERENCES __PFX__Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION