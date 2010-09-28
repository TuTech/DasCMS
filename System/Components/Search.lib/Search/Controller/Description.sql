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
	FROM __PFX__SearchResults
	WHERE
		searchREL = ?
		AND description NOT LIKE ?

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: is
DELETE
	FROM __PFX__SearchResults
	WHERE
		searchREL = ?
		AND description LIKE ?
