-- --
-- name: latest
-- deterministic: yes
-- inputTypes:	i
-- mutable: no
-- fields: 2
-- type: select
SELECT
		__PFX__Changes.changeDate,
		IF(ISNULL(__PFX__ChangedByUsers.login), '-', __PFX__ChangedByUsers.login) as user
	FROM __PFX__Changes
	LEFT JOIN __PFX__ChangedByUsers ON (__PFX__Changes.userREL = __PFX__ChangedByUsers.changedByUserID)
	WHERE
		__PFX__Changes.contentREL = ?
	ORDER BY __PFX__Changes.changeDate DESC
	LIMIT 1

-- --
-- name: created
-- deterministic: yes
-- inputTypes:	i
-- mutable: no
-- fields: 2
-- type: select
		__PFX__Changes.changeDate,
		IF(ISNULL(__PFX__ChangedByUsers.login), '-', __PFX__ChangedByUsers.login) as user
	FROM __PFX__Changes
	LEFT JOIN __PFX__ChangedByUsers ON (__PFX__Changes.userREL = __PFX__ChangedByUsers.changedByUserID)
	WHERE
		__PFX__Changes.contentREL = ?
	ORDER BY __PFX__Changes.changeDate ASC
	LIMIT 1