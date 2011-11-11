-- --
-- name: load
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT formatterData
	FROM __PFX__Formatters
	WHERE name = ?
	LIMIT 1

-- --
-- name: exists
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__Formatters
	WHERE name = ?
	LIMIT 1

-- --
-- name: set
-- type: insert
-- inputTypes:	sss
INSERT
	INTO __PFX__Formatters (formatterData, name)
	VALUES (?, ?)
	ON DUPLICATE KEY
		UPDATE
			formatterData = ?

-- --
-- name: del
-- type: delete
-- inputTypes:	s
DELETE
	FROM __PFX__Formatters
	WHERE name = ?

-- --
-- name: list
-- deterministic: yes
-- fields: 1
-- type: select
SELECT name
	FROM __PFX__Formatters
	ORDER BY name