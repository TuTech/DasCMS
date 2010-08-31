-- --
-- name: jobsTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__Jobs(
    jobID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    classREL
        INTEGER
        NOT NULL,
    start
        DATETIME
        NULL,
    stop
        DATETIME
        NULL,
    rescheduleInterval
        INTEGER
        NOT NULL,
    UNIQUE (classREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: jobSchedulesTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__JobSchedules(
	jobREL
        INTEGER
        NOT NULL,
	scheduled
		TIMESTAMP
		DEFAULT 0
		NOT NULL,
	started
		TIMESTAMP
		NULL,
	finished
		TIMESTAMP
		NULL,
	exitCode
		INTEGER
		NULL,
	exitMessage
		VARCHAR(64)
		NULL,
	INDEX (jobREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: jobsReferences
-- type: alter
ALTER TABLE
__PFX__Jobs
    ADD FOREIGN KEY (classREL)
        REFERENCES __PFX__Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: jobSchedulesReferences
-- type: alter
ALTER TABLE
__PFX__JobSchedules
    ADD FOREIGN KEY (jobREL)
        REFERENCES __PFX__Jobs(jobID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION