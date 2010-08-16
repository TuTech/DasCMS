-- --
-- name:	getPreviewContents
-- types:	sssss
-- deterministic: yes
-- mutable: no
SELECT DISTINCT 
		__PFX__Aliases.´alias´,
		__PFX__Contents.title
	FROM
		__PFX__Contents
	LEFT JOIN
		__PFX__Aliases ON (__PFX__Aliases.aliasID = __PFX__Contents.GUID)
	LEFT JOIN
		__PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Mimetypes.mimetype LIKE ?
		AND (
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
		)
	ORDER BY
		__PFX__Contents.title ASC

-- --
-- name:	idToAlias
-- types:	i
-- deterministic: yes
-- mutable: no
SELECT 
		´alias´
	FROM
		__PFX__Aliases
	WHERE
		contentREL = ?
	LIMIT 1

-- --
-- name:	aliasToId
-- types:	s
-- deterministic: yes
-- mutable: no
SELECT 
		contentREL
	FROM
		__PFX__Aliases
	WHERE
		alias = ?
	LIMIT 1

-- --
-- name:	idToAlias
-- types:	ssssss
-- deterministic: yes
-- mutable: no
SELECT
		__PFX__Aliases.contentREL
	FROM
		__PFX__Aliases
	LEFT JOIN
		__PFX__Contents ON (__PFX__Aliases.contentREL = __PFX__Contents.contentID)
	LEFT JOIN
		__PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Aliases.alias = ?
		AND __PFX__Mimetypes.mimetype LIKE ?
		AND (
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
			OR
			__PFX__Mimetypes.mimetype LIKE ?
		)