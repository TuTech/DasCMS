-- --
-- name: basicMeta
-- inputTypes:	s
-- deterministic: yes
-- fields: 10
-- type: select
SELECT
		__PFX__Contents.contentID,
		__PFX__Contents.title,
		__PFX__Contents.pubDate,
		__PFX__Contents.revokeDate,
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
-- fields: 1
-- type: select
SELECT tag
	FROM __PFX__Tags
		LEFT JOIN __PFX__relContentsTags
			ON (__PFX__Tags.tagID = __PFX__relContentsTags.tagREL)
	WHERE
		__PFX__relContentsTags.contentREL = ?
	ORDER BY tag

-- --
-- name: searchable
-- inputTypes:	i
-- deterministic: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__Contents
	WHERE
		contentID = ?
		AND
		allowSearchIndexing = 'Y'

-- --
-- name: setSearchable
-- type: update
-- inputTypes:	si
UPDATE __PFX__Contents
	SET
		allowSearchIndexing = ?
	WHERE
		contentID = ?

-- --
-- name: saveMeta
-- inputTypes: ssssisi
-- type: update
UPDATE __PFX__Contents
	SET
		title = ?,
		pubDate = ?,
		revokeDate = ?,
		description = ?,
		size = ?,
		subtitle = ?
	WHERE
		contentID = ?

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

-- --
-- name: setLogOutdated
-- type: update
-- inputTypes:	i
UPDATE __PFX__Changes
	SET latest = 'N'
	WHERE contentREL = ?

-- --
-- name: log
-- type: insert
-- inputTypes:	isis
INSERT
	INTO __PFX__Changes (contentREL, title, size, userREL, latest)
	VALUES (?, ?, ?, ?, 'Y')

-- --
-- name: createContent
-- type: insert
-- inputTypes:	ss
INSERT
	INTO __PFX__Contents(type, title, description)
    SELECT
			classID,
			? AS title,
			'' AS description
		FROM __PFX__Classes
		WHERE class = ?

-- --
-- name: createGUID
-- type: insert
-- inputTypes:	i
INSERT
	INTO __PFX__Aliases(alias, contentREL)
    VALUES(UUID(), ?)

-- --
-- name: linkGUID
-- type: update
-- inputTypes:	iii
UPDATE __PFX__Contents
	SET primaryAlias = ?,
		GUID = ?
	WHERE contentID = ?

-- --
-- name: getGUID
-- inputTypes:	i
-- deterministic: yes
-- fields: 1
-- type: select
SELECT __PFX__Aliases.alias
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (GUID = aliasID)
	WHERE contentID = ?

-- --
-- name: addMime
-- type: insert
-- inputTypes:	ss
INSERT
	INTO __PFX__Mimetypes (mimetype)
		VALUES (?)
	ON DUPLICATE KEY UPDATE
		mimetype = ?

-- --
-- name: setMime
-- type: update
-- inputTypes:	ss
UPDATE __PFX__Contents
	SET mimetypeREL = (SELECT mimetypeID from __PFX__Mimetypes WHERE mimetype = ?)
	WHERE contentID = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
