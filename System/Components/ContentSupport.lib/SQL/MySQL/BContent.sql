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
-- name: basicMeta
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 8
-- type: select
SELECT
		__PFX__Contents.contentID,
		__PFX__Contents.title,
		__PFX__Contents.pubDate,
		__PFX__Contents.description,
		__PFX__Mimetypes.mimetype,
		__PFX__Contents.size,
		GUIDs.alias,
		PriAlias.alias,
		__PFX__Contents.subtitle
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
		LEFT JOIN __PFX__Aliases AS GUIDs ON (__PFX__Contents.GUID = GUIDs.aliasID)
		LEFT JOIN __PFX__Aliases AS PriAlias ON (__PFX__Contents.primaryAlias = PriAlias.aliasID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE __PFX__Aliases.alias = ?
	LIMIT 1

-- --
-- name: tags
-- inputTypes:	i
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT tag
	FROM __PFX__Tags
		LEFT JOIN __PFX__relContentsTags
			ON (__PFX__Tags.tagID = __PFX__relContentsTags.tagREL)
	WHERE
		__PFX__relContentsTags.contentREL = ?
	ORDER BY tag 