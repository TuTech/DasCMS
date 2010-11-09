-- --
-- name: clear
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__Changes
	WHERE contentREL = ?

-- --
-- name: add
-- type: insert
-- inputTypes:	isiss
INSERT
	INTO __PFX__Changes (contentREL, changeDate, title, size, userREL, latest)
	VALUES (?, ?, ?, ?, ?, ?)

-- --
-- name: logUID
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT changedByUserID
	FROM __PFX__ChangedByUsers
	WHERE login = LEFT(?, 64)
	LIMIT 1

-- --
-- name: addLogUser
-- type: insert
-- inputTypes:	s
INSERT
	INTO __PFX__ChangedByUsers(login)
	VALUES (LEFT(?, 64))