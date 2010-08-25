-- --
-- name: addUser
-- inputTypes: sss
-- type:insert
INSERT
	INTO __PFX__Users (login, name, email)
		VALUES (?, ?, ?)

-- --
-- name: setName
-- inputTypes: ss
-- type:update
UPDATE __PFX__Users
	SET
		name = ?
	WHERE
		login = ?
-- --
-- name: setEMail
-- inputTypes: ss
-- type:update
UPDATE __PFX__Users
	SET
		email = ?
	WHERE
		login = ?


-- --
-- name: addGroup
-- inputTypes: ss
-- type:insert
INSERT
	INTO __PFX__Groups(groupName, description)
		VALUES (?, ?)

-- --
-- name: setPriGrp
-- inputTypes: ss
-- type:update
UPDATE __PFX__Users
	SET primaryGroup = (SELECT groupID FROM __PFX__Groups WHERE groupName = ?)
	WHERE login = ?

-- --
-- name: linkUserToGroup
-- inputTypes: ss
-- type:insert
INSERT IGNORE
	INTO __PFX__relUsersGroups (userREL, groupREL)
	SELECT userID, groupID
		FROM __PFX__Users
			JOIN __PFX__Groups
		WHERE
			login = ?
			AND
			groupName = ?

-- --
-- name: clearGroupsExcept
-- inputTypes: ss
-- type:delete
DELETE FROM __PFX__relUsersGroups
	WHERE
		userREL = (SELECT userID FROM __PFX__Users WHERE login = ?)
		AND
		groupREL != (SELECT groupID FROM __PFX__Groups WHERE groupName = ?)

-- --
-- name: link
-- inputTypes: ss
-- type:insert
INSERT IGNORE
	INTO __PFX__relUsersGroups (userREL, groupREL)
		SELECT userID, groupID
			FROM __PFX__Users
				JOIN __PFX__Groups
			WHERE
				login = ?
				AND
				groupName = ?

-- --
-- name: delGroup
-- inputTypes: s
-- type:delete
DELETE
	FROM __PFX__Groups
	WHERE groupName = ?

-- --
-- name: delUser
-- inputTypes: s
-- type:delete
DELETE
	FROM __PFX__Users
	WHERE login = ?

-- --
-- name: setDesc
-- inputTypes: ss
-- type:update
UPDATE __PFX__Groups
	SET description = ?
	WHERE groupName = ?