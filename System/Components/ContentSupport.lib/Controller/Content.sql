-- --
-- name: exists
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__Aliases
	WHERE alias = ?

-- --
-- name: contentsForClassGuid
-- inputTypes:	s
-- deterministic: yes
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

-- --
-- name: chainToClass
-- inputTypes: ss
-- type: insert
INSERT IGNORE
	INTO __PFX__relClassesChainedContents (chainingClassREL, chainedContentREL)
	SELECT 
			__PFX__Classes.classID,
			__PFX__Contents.contentID
		FROM __PFX__Classes
			LEFT JOIN __PFX__Contents ON (1)
			LEFT JOIN __PFX__Aliases ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
		WHERE
			__PFX__Classes.class = ?
			AND
			__PFX__Aliases.alias = ?

-- --
-- name: getChainedToClass
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		__PFX__Aliases.alias
	FROM __PFX__Classes
		LEFT JOIN __PFX__relClassesChainedContents
			ON (__PFX__Classes.classID = __PFX__relClassesChainedContents.chainingClassREL)
		LEFT JOIN __PFX__Contents
			ON (__PFX__relClassesChainedContents.chainedContentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.GUID = __PFX__Aliases.aliasID)
	WHERE
		__PFX__Classes.class = ?

-- --
-- name: unlinkClass
-- inputTypes: s
-- type: delete
DELETE
	FROM __PFX__relClassesChainedContents
    WHERE chainingClassREL = (SELECT classID FROM __PFX__Classes WHERE class = ?)

-- --
-- name: unlinkContent
-- inputTypes: ss
-- type: delete
DELETE
	FROM __PFX__relClassesChainedContents
    WHERE
		chainingClassREL = (SELECT classID FROM __PFX__Classes WHERE class = ?)
		AND
		chainedContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)


-- --
-- name: delete
-- type: delete
-- inputTypes:	s
DELETE
	FROM __PFX__Contents
	WHERE contentID = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)

-- --
-- name: getClass
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT __PFX__Classes.class
	FROM __PFX__Contents
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
	WHERE __PFX__Aliases.alias = ?

-- --
-- name: getPri
-- inputTypes:	s
-- deterministic: yes
-- fields: 3
-- type: select
SELECT
		__PFX__Contents.title,
		Pri.alias,
		__PFX__Contents.published
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (contentID = contentREL)
		LEFT JOIN __PFX__Aliases AS Pri ON (primaryAlias = Pri.aliasID)
	WHERE __PFX__Aliases.alias = ?

-- --
-- name: index
-- inputTypes:	s
-- deterministic: yes
-- fields: 5
-- type: select
SELECT
		__PFX__Contents.title AS Title,
		__PFX__Contents.pubDate AS PubDate,
		__PFX__Aliases.alias AS Alias,
		__PFX__Mimetypes.mimetype,
		__PFX__Contents.contentID
	FROM __PFX__Contents
	LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
	LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
	LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)
	WHERE
		__PFX__Classes.class = ?
	ORDER BY __PFX__Contents.title ASC
