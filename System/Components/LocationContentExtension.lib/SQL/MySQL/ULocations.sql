-- --
-- name: get
-- deterministic: yes
-- inputTypes:	s
-- mutable: no
-- fields: 4
-- type: select
SELECT 	location,
		IF(ISNULL(latitude),'',latitude),
		IF(ISNULL(longitude),'',longitude),
		IF(ISNULL(address),'',address)
	FROM __PFX__Aliases
	LEFT JOIN __PFX__relContentsLocations USING (contentREL)
	LEFT JOIN __PFX__Locations ON (__PFX__relContentsLocations.locationREL = __PFX__Locations.locationID)
	WHERE __PFX__Aliases.alias = ?

-- --
-- name: getId
-- deterministic: yes
-- inputTypes:	s
-- mutable: no
-- fields: 1
-- type: select
SELECT locationID
	FROM __PFX__Locations
	WHERE location = ?

-- --
-- name: link
-- inputTypes: isi
-- type: insert
INSERT
	INTO __PFX__relContentsLocations (contentREL, locationREL)
		SELECT contentREL, ? as loc FROM __PFX__Aliases WHERE alias = ?
	ON DUPLICATE KEY UPDATE locationREL = ?

-- --
-- name: unlink
-- inputTypes: s
-- type: delete
DELETE
	FROM __PFX__relContentsLocations
	WHERE relContentsLocations.contentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)

-- --
-- name: set
-- inputTypes: sddsdds
-- type: insert
INSERT
	INTO __PFX__Locations (location, latitude, longitude, address)
		VALUES(?, ?, ?, ?)
	ON DUPLICATE KEY UPDATE
		latitude = ?,
		longitude = ?,
		address = ?

-- --
-- name: list
-- deterministic: yes
-- inputTypes:	s
-- mutable: no
-- fields: 1
-- type: select
SELECT location
	FROM __PFX__Locations
	WHERE location = ?
	ORDER BY location
