-- --
-- name: checkHash
-- inputTypes: s
-- fields: 3
-- type: select
SELECT
		searchID,
		runTime,
		created
	FROM __PFX__Searches
		WHERE searchHash = ?
-- --
-- name: getId
-- inputTypes: s
-- fields: 1
-- type: select
SELECT
		searchID
	FROM __PFX__Searches
		WHERE searchHash = ?

-- --
-- name: createQuery
-- type: insert
-- inputTypes: ss
INSERT
	INTO __PFX__Searches (normalizedSearch, searchHash)
	VALUES (?, ?)

-- --
-- name: setStats
-- type: insert
-- inputTypes: dii
UPDATE __PFX__Searches
	SET
		runTime = ?,
		foundItems = (SELECT COUNT(*) FROM __PFX__SearchResults WHERE searchID = ?)
	WHERE searchID = ?

-- --
-- name: dump
-- inputTypes: i
-- type: select
-- fields: 5
SELECT
		title,
		subtitle,
		pubdate,
		GROUP_CONCAT(tag),
		description
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
		LEFT JOIN __PFX__relContentsTags USING(contentREL)
		LEFT JOIN __PFX__Tags ON (tagREL = tagID)
	WHERE searchREL = ?
	GROUP BY contentREL
	ORDER BY itemNr

-- --
-- name: flush
-- type: delete
DELETE FROM __PFX__Searches
	WHERE NOT ISNULL(runTime)