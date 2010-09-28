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
			WHERE subtitle LIKE ?

-- --
-- name: filterRequire
-- type: delete
-- inputTypes: is
DELETE
	FROM __PFX__SearchResults
	WHERE
		searchID = ?
		AND subtitle NOT LIKE ?

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: is
DELETE
	FROM __PFX__SearchResults
	WHERE
		searchID = ?
		AND subtitle LIKE ?