-- --
-- name: lookup
-- type: select
-- inputTypes: s
-- fields: 1
SELECT classID
	FROM __PFX__Classes
	WHERE class = ?
	LIMIT 1

-- --
-- name: gather
-- type: insert
-- inputTypes: is
INSERT IGNORE
	INTO __PFX__SearchResults(searchREL, contentREL)
	SELECT
			? AS searchREL,
			contentID AS contentREL
		FROM __PFX__Contents
			WHERE 
				__PFX__Contents.type = ?
				AND
				published = 1

-- --
-- name: filterRequire
-- type: delete
-- inputTypes: is
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND
		__PFX__Contents.type != ?

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: is
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND
		__PFX__Contents.type = ?
