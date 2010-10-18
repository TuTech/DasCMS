-- --
-- name: updateClassIndex
-- type: insert
-- inputTypes:	ssss
INSERT 
	INTO __PFX__Classes(class, guid)
	VALUES (?, ?)
	ON DUPLICATE KEY UPDATE
		class = ?,
		guid = ?

-- --
-- name: updateClassIndexNoGUID
-- type: insert
-- inputTypes:	ss
INSERT
	INTO __PFX__Classes(class, guid)
	VALUES (?, NULL)
	ON DUPLICATE KEY UPDATE
		class = ?,
		guid = NULL