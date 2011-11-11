-- --
-- name: locationExists
-- type: select
-- inputTypes: s
-- fields: 1
SELECT COUNT(*)
	FROM __PFX__Locations
	WHERE location = ?

-- --
-- name: update
-- type: update
-- inputTypes: ssddii
UPDATE __PFX__Locations
	SET location = ?,
		address = ?,
		latitude = ?,
		longitude = ?,
		accuracy = ?
	WHERE
		locationID = ?

-- --
-- name: create
-- type: insert
-- inputTypes: s
INSERT
	INTO __PFX__Locations(location)
		VALUES (?)

-- --
-- name: load
-- type:select
-- inputTypes: is
-- fields: 6
SELECT
		locationID,
		location,
		address,
		latitude,
		longitude,
		accuracy
	FROM __PFX__Locations
	WHERE
		locationID = ?
		OR
		location = ?
	LIMIT 1