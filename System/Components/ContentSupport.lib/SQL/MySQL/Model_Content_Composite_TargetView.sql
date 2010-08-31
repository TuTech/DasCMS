-- --
-- name: removeViewBinding
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__relContentsTargetViews
	WHERE contentREL = ?

-- --
-- name: setViewBinding
-- type: insert
-- inputTypes:	iss
INSERT 
	INTO __PFX__relContentsTargetViews (contentREL, viewREL)
	SELECT ?, viewID
		FROM __PFX__SporeViews
		WHERE viewName = ?
	ON DUPLICATE KEY
		UPDATE viewREL = (
			SELECT viewID
				FROM __PFX__SporeViews
				WHERE viewName = ?
				LIMIT 1
		)

-- --
-- name: getViewBinding
-- inputTypes:	i
-- deterministic: yes
-- fields: 1
-- type: select
SELECT viewName
	FROM __PFX__relContentsTargetViews
		LEFT JOIN __PFX__SporeViews
			ON (__PFX__relContentsTargetViews.viewREL = __PFX__SporeViews.viewID)
	WHERE __PFX__relContentsTargetViews.contentREL = ?
	LIMIT 1