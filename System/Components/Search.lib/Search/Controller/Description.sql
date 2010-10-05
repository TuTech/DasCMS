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
			WHERE description LIKE ?

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
		AND description NOT LIKE ?

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
		AND description LIKE ?

