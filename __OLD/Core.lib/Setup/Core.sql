-- --
-- name: classesTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Classes(
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
        NULL
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci