-- --
-- name: add
-- inputTypes: sdd
-- type: insert
INSERT
	INTO __PFX__Locations (location, latitude, longitude)
		VALUES (?, ?, ?)

-- --
-- name: rename
-- inputTypes: ss
-- type: update
UPDATE __PFX__Locations
	SET location = ?
	WHERE location = ?

-- --
-- name: relocate
-- inputTypes: dds
-- type: update
UPDATE __PFX__Locations
	SET
		latitude = ?,
		longitude = ?
	WHERE location = ?

-- --
-- name: delete
-- inputTypes: s
-- type: delete
DELETE
	FROM __PFX__Locations
	WHERE location = ?

-- --
-- name: get
-- deterministic: yes
-- inputTypes:	s
-- fields: 3
-- type: select
SELECT
		location,
		latitude,
		longitude
	FROM __PFX__Locations
	WHERE location = ?

-- --
-- name: getForContent
-- deterministic: yes
-- inputTypes:	i
-- fields: 3
-- type: select
SELECT
		__PFX__Locations.location,
		__PFX__Locations.latitude,
		__PFX__Locations.longitude
	FROM __PFX__Locations
		LEFT JOIN __PFX__relContentsLocations
			ON (__PFX__relContentsLocations.locationREL = __PFX__Locations.locationID)
	WHERE __PFX__relContentsLocations.contentREL = ?

-- --
-- name: unlink
-- inputTypes: i
-- type: delete
DELETE
	FROM __PFX__relContentsLocations
	WHERE contentREL = ?

-- --
-- name: link
-- inputTypes: is
-- type: insert
INSERT
	INTO __PFX__relContentsLocations (contentREL, locationREL)
		SELECT
				? AS 'contentREL',
				locationID
			FROM __PFX__Locations
			WHERE location = ?
