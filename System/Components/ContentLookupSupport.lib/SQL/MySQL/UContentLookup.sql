-- --
-- name: listAll
-- deterministic: yes
-- inputTypes:	sii
-- mutable: no
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
	WHERE
		__PFX__Contents.title LIKE ?
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?

-- --
-- name: listPriv
-- deterministic: yes
-- inputTypes:	sii
-- mutable: no
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate = "0000-00-00 00:00:00"
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?

-- --
-- name: listSched
-- deterministic: no
-- inputTypes:	sii
-- mutable: yes
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate > NOW()
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?
-- --
-- name: listPub
-- deterministic: no
-- inputTypes:	sii
-- mutable: yes
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate > "0000-00-00 00:00:00"
		AND
		__PFX__Contents.pubDate < NOW()
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?


-- --
-- name: listImgAll
-- deterministic: yes
-- inputTypes:	sii
-- mutable: no
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND (
			__PFX__Mimetypes.mimetype = "image/jpg"
			OR
			__PFX__Mimetypes.mimetype = "image/jpeg"
			OR
			__PFX__Mimetypes.mimetype = "image/png"
			OR
			__PFX__Mimetypes.mimetype = "image/gif"
		)
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?

-- --
-- name: listImgPriv
-- deterministic: yes
-- inputTypes:	sii
-- mutable: no
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate = "0000-00-00 00:00:00"
		AND (
			__PFX__Mimetypes.mimetype = "image/jpg"
			OR
			__PFX__Mimetypes.mimetype = "image/jpeg"
			OR
			__PFX__Mimetypes.mimetype = "image/png"
			OR
			__PFX__Mimetypes.mimetype = "image/gif"
		)
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?

-- --
-- name: listImgSched
-- deterministic: no
-- inputTypes:	sii
-- mutable: yes
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate > NOW()
		AND (
			__PFX__Mimetypes.mimetype = "image/jpg"
			OR
			__PFX__Mimetypes.mimetype = "image/jpeg"
			OR
			__PFX__Mimetypes.mimetype = "image/png"
			OR
			__PFX__Mimetypes.mimetype = "image/gif"
		)
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?
-- --
-- name: listImgPub
-- deterministic: no
-- inputTypes:	sii
-- mutable: yes
-- fields: 4
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.pubDate
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Contents.title LIKE ?
		AND
		__PFX__Contents.pubDate > "0000-00-00 00:00:00"
		AND
		__PFX__Contents.pubDate < NOW()
		AND (
			__PFX__Mimetypes.mimetype = "image/jpg"
			OR
			__PFX__Mimetypes.mimetype = "image/jpeg"
			OR
			__PFX__Mimetypes.mimetype = "image/png"
			OR
			__PFX__Mimetypes.mimetype = "image/gif"
		)
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT ?
	OFFSET ?


