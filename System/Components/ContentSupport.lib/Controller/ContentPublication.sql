-- --
-- name: getChanged
-- type: select
-- fields: 2
SELECT published, alias
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases ON (primaryAlias = aliasID)
	WHERE (
			published = 1
			AND (
				pubDate > NOW()
				OR (
					revokeDate <= NOW()
					AND
					revokeDate > '0000-00-00 00:00:00'
				)
			)
		)
		OR (
			published = 0
			AND	(
				pubDate <= NOW()
				AND
				pubDate > '0000-00-00 00:00:00'
			)
			AND	(
				revokeDate > NOW()
				OR
				revokeDate = '0000-00-00 00:00:00'
			)
		)
	FOR UPDATE

-- --
-- name: changeStatus
-- type: update
-- inputTypes: is
UPDATE __PFX__Contents
	SET published = ?
	WHERE contentID = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
	