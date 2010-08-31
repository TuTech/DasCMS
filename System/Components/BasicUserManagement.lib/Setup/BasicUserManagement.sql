-- --
-- name: groupsTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__Groups(
    groupID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    groupName
        VARCHAR(32)
        UNIQUE
        NOT NULL,
    description
        VARCHAR(255)
        NOT NULL
        DEFAULT ''
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: usersTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__Users(
    userID
        INTEGER
        PRIMARY KEY
        AUTO_INCREMENT
        NOT NULL,
    login
        VARCHAR(32)
        UNIQUE
        NOT NULL,
    name
        varchar(100)
        NOT NULL
        DEFAULT '-',
	email
        varchar(100)
        NOT NULL
        DEFAULT '',
    primaryGroup
        INTEGER
        NULL,
    INDEX (primaryGroup)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: permissiontagsTable
-- type:create
CREATE TABLE IF NOT EXISTS
__PFX__PermissionTags(
    permissionTagREL
		INTEGER
		UNIQUE
		NOT NULL
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci


-- --
-- name: relUsersGroupsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relUsersGroups(
    userREL
        INTEGER
        NOT NULL,
    groupREL
        INTEGER
        NOT NULL,
    INDEX (userREL),
    UNIQUE (groupREL, userREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relPermissionTagsGroupsTable
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relPermissionTagsGroups(
    permissionTagREL
        INTEGER
        NOT NULL,
    groupREL
        INTEGER
        NOT NULL,
    INDEX (permissionTagREL),
    UNIQUE (groupREL, permissionTagREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: relPermissionTagsUsers
-- type: create
CREATE TABLE IF NOT EXISTS
__PFX__relPermissionTagsUsers(
    permissionTagREL
        INTEGER
        NOT NULL,
    userREL
        INTEGER
        NOT NULL,
    INDEX (permissionTagREL),
    UNIQUE (userREL, permissionTagREL)
)
ENGINE = InnoDB
CHARACTER SET utf8
COLLATE utf8_unicode_ci

-- --
-- name: permissionTagsReferences
-- type: alter
ALTER TABLE
__PFX__PermissionTags
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES __PFX__Tags(tagID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION

-- --
-- name: relPermissionTagsGroupsReferences
-- type: alter
ALTER TABLE
__PFX__relPermissionTagsGroups
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES __PFX__PermissionTags(permissionTagREL)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (groupREL)
        REFERENCES __PFX__Groups(groupID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relPermissionTagsUsersReferences
-- type: alter
ALTER TABLE
__PFX__relPermissionTagsUsers
    ADD FOREIGN KEY (permissionTagREL)
        REFERENCES __PFX__PermissionTags(permissionTagREL)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (userREL)
        REFERENCES __PFX__Users(userID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: relUsersGroupsReferences
-- type: alter
ALTER TABLE
__PFX__relUsersGroups
    ADD FOREIGN KEY (userREL)
        REFERENCES __PFX__Users(userID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (groupREL)
        REFERENCES __PFX__Groups(groupID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION

-- --
-- name: usersReferences
-- type: alter
ALTER TABLE
__PFX__Users
    ADD FOREIGN KEY (primaryGroup)
        REFERENCES __PFX__Groups(groupID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION