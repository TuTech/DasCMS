-- --
-- name: meta
-- inputTypes: i
-- type: select
-- fields: 3
SELECT
		runTime,
		foundItems,
		created
	FROM __PFX__Searches
	WHERE searchID = ?

-- --
-- name: page
-- inputTypes: iii
-- type: select
-- fields: 1
SELECT
		alias
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents
			ON (contentREL = contentID)
		LEFT JOIN __PFX__Aliases
			ON (primaryAlias = aliasID)
	WHERE
		searchREL = ?
		AND
		itemNr BETWEEN ? AND ?
	ORDER BY itemNr ASC