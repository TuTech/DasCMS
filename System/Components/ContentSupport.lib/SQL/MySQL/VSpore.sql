-- --
-- name: loadSpores
-- deterministic: yes
-- mutable: no
-- fields: 4
-- type: select
SELECT
		__PFX__SporeViews.viewName,
		__PFX__SporeViews.active,
		DefAliases.alias,
		ErrAliases.alias
	FROM __PFX__SporeViews
		LEFT JOIN __PFX__Contents AS DefContent
			ON (__PFX__SporeViews.defaultContentREL = DefContent.contentID)
		LEFT JOIN __PFX__Contents AS ErrContent
			ON (__PFX__SporeViews.errorContentREL = ErrContent.contentID)
		LEFT JOIN __PFX__Aliases  AS DefAliases
			ON (DefContent.primaryAlias = DefAliases.aliasID)
		LEFT JOIN __PFX__Aliases  AS ErrAliases
			ON (ErrContent.primaryAlias = ErrAliases.aliasID)
	ORDER BY __PFX__SporeViews.viewName ASC


-- --
-- name: deleteSpore
-- type: delete
-- inputTypes:	s
DELETE 
	FROM __PFX__SporeViews
	WHERE viewName = ?

-- --
-- name: setSpore
-- type: insert
-- inputTypes:	sss
INSERT
	INTO __PFX__SporeViews (viewName, active)
		VALUES (?, ?)
		ON DUPLICATE KEY UPDATE
			active = ?,
			defaultContentREL = NULL,
			errorContentREL = NULL;

-- --
-- name: setSporeWDef
-- type: insert
-- inputTypes:	sssss
INSERT
	INTO __PFX__SporeViews (viewName, active, defaultContentREL)
		VALUES (?, ?, (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1))
		ON DUPLICATE KEY UPDATE
			active = ?,
			defaultContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1),
			errorContentREL = NULL;

-- --
-- name: setSporeWErr
-- type: insert
-- inputTypes:	sssss
INSERT
	INTO __PFX__SporeViews (viewName, active, errorContentREL)
		VALUES (?, ?, (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1))
		ON DUPLICATE KEY UPDATE
			active = ?,
			defaultContentREL = NULL,
			errorContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1);

-- --
-- name: setSporeWDefWErr
-- type: insert
-- inputTypes:	sssssss
INSERT
	INTO __PFX__SporeViews (viewName, active, defaultContentREL, errorContentREL)
		VALUES (
			?,
			?,
			(SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1),
			(SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1)
		)
		ON DUPLICATE KEY UPDATE
			active = ?,
			defaultContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1),
			errorContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias =  ? LIMIT 1);
