-- --
-- name: checkHash
-- inputTypes: s
-- fields: 3
-- type: select
SELECT
		searchID,
		runTimeInMilliSec,
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

