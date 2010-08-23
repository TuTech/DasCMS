-- --
-- name: exists
-- inputTypes:	s
-- deterministic: no
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__Aliases
	WHERE alias = ?

-- --
-- name: contentsForClassGuid
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 2
-- type: select
SELECT
		__PFX__Aliases.alias,
		__PFX__Contents.title
	FROM __PFX__Classes
		LEFT JOIN __PFX__Contents
			ON (__PFX__Classes.classID = __PFX__Contents.type)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.GUID = __PFX__Aliases.aliasID)
	WHERE
		__PFX__Classes.class = ?
		AND
		NOT ISNULL(alias)