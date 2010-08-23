-- --
-- name: setFileMeta
-- type: insert
-- inputTypes:	issssss
INSERT
	INTO __PFX__FileAttributes (contentREL, originalFileName, suffix, md5sum)
	VALUES (?, ?, ?, ?)
	ON DUPLICATE KEY
		UPDATE
			originalFileName = ?,
			suffix = ?,
			md5sum = ?

-- --
-- name: getContents
-- deterministic: yes
-- mutable: no
-- fields: 5
-- type: select
SELECT
		__PFX__Contents.contentID,
		__PFX__Aliases.alias,
		__PFX__Contents.title,
		__PFX__Contents.size,
		__PFX__Mimetypes.mimetype
	FROM __PFX__FileAttributes
		LEFT JOIN __PFX__Contents ON (__PFX__FileAttributes.contentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Mimetypes ON (__PFX__Contents.mimetypeREL = __PFX__Mimetypes.mimetypeID)

-- --
-- name: getMetaData
-- deterministic: yes
-- mutable: no
-- fields: 3
-- inputTypes:	i
-- type: select
SELECT
		__PFX__FileAttributes.originalFileName,
		__PFX__FileAttributes.suffix,
		__PFX__FileAttributes.md5sum
	FROM __PFX__FileAttributes
	WHERE __PFX__FileAttributes.contentREL = %d