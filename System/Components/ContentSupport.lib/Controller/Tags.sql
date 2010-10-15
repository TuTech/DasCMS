-- --
-- name: listTagsOf
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT __PFX__Tags.tag
	FROM __PFX__Contents
		LEFT JOIN __PFX__relContentsTags
			ON (__PFX__Contents.contentID = __PFX__relContentsTags.contentREL)
		LEFT JOIN __PFX__Tags
			ON (__PFX__relContentsTags.tagREL = __PFX__Tags.tagID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
	WHERE
		__PFX__Aliases.alias = ?
	ORDER BY __PFX__Tags.tag ASC

-- --
-- name: aliasToId
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		__PFX__Contents.contentID
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
	WHERE
		__PFX__Aliases.alias = ?
	LIMIT 1

-- --
-- name: unlink
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__relContentsTags
	WHERE contentREL = ?

-- --
-- name: setTag
-- type: insert
-- inputTypes: ss
INSERT
	INTO __PFX__Tags (tag)
		VALUES(?)
	ON DUPLICATE KEY UPDATE
		tag = ?

-- --
-- name: linkTag
-- type: insert
-- inputTypes: is
INSERT
	INTO __PFX__relContentsTags (tagREL, contentREL)
		SELECT
				tagID AS tagREL,
				? AS contentREL
			FROM __PFX__Tags
			WHERE tag = ?