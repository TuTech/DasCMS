-- new table for users names
CREATE TABLE IF NOT EXISTS 
ChangedByUsers(
    changedByUserID 
        INTEGER 
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    login 
        VARCHAR(32) 
        UNIQUE
        NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

INSERT INTO ChangedByUsers (changedByUserID, login)
	SELECT userID AS 'changedByUserID', login FROM Users;

-- remove old reference to users table
ALTER TABLE Changes DROP FOREIGN KEY `changed_by`;

-- set new reference to the new table
ALTER TABLE 
Changes
    ADD CONSTRAINT changed_by FOREIGN KEY (userREL)
        REFERENCES ChangedByUsers(changedByUserID)
        ON DELETE RESTRICT
        ON UPDATE RESTRICT;