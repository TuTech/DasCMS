-- --
-- name: gather
-- type: insert
-- inputTypes: is
INSERT IGNORE
	INTO __PFX__SearchResults(searchREL, contentREL)
	SELECT
			? AS searchREL,
			contentREL
		FROM __PFX__relContentsTags
			LEFT JOIN __PFX__Tags
				ON (tagREL = tagID)
			LEFT JOIN __PFX__Contents
				ON (contentREL = contentID)
			WHERE 
				tag = ?
				AND
				published = 1


-- --
-- name: filterRequire
-- type: delete
-- inputTypes: is
DELETE
	FROM __PFX__SearchResults
	WHERE searchREL = ?
	AND contentREL NOT IN (
		SELECT contentREL
			FROM __PFX__relContentsTags
				LEFT JOIN __PFX__Tags
					ON (tagID = tagREL)
			WHERE tag = ?
		)

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: is
DELETE
	FROM __PFX__SearchResults
	WHERE searchREL = ?
	AND contentREL IN (
		SELECT contentREL
			FROM __PFX__relContentsTags
				LEFT JOIN __PFX__Tags
					ON (tagID = tagREL)
			WHERE tag = ?
		)