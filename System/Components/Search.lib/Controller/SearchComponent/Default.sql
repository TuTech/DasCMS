-- --
-- name: gather
-- type: insert
-- inputTypes: isss
INSERT IGNORE
	INTO __PFX__SearchResults(searchREL, contentREL)
	SELECT
			? AS searchREL,
			contentID AS contentREL
		FROM __PFX__Contents
			WHERE (
					title LIKE ?
					OR
					subtitle LIKE ?
					OR
					description LIKE ?
				)
				AND
				published = 1

-- --
-- name: filterRequire
-- type: delete
-- inputTypes: isss
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND
		title NOT LIKE ?
		AND
		subtitle NOT LIKE ?
		AND
		description NOT LIKE ?

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: isss
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND (
			title LIKE ?
			OR
			subtitle LIKE ?
			OR
			description LIKE ?
		)

