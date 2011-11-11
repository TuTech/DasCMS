-- --
-- name: authorisationLogTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__AuthorisationLog(
	LoginTime
		TIMESTAMP
		DEFAULT CURRENT_TIMESTAMP
		NOT NULL,
	IPAdr
		INTEGER
		NOT NULL,
	UserName
		VARCHAR(32)
		NOT NULL,
	Status
		ENUM('FAIL', 'SUCCESS')
		NOT NULL,
	INDEX(`IPAdr`, `UserName`, `Status`),
	INDEX (`LoginTime`)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci