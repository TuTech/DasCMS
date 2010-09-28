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
			WHERE tag = ?
