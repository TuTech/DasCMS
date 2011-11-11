-- --
-- name: getServices
-- inputTypes:	s
-- deterministic: no
-- fields: 2
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Aliases.alias
	 FROM __PFX__relClassesChainedContents
		LEFT JOIN __PFX__Contents ON (__PFX__relClassesChainedContents.chainedContentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Classes ON (__PFX__Contents.type = __PFX__Classes.classID)
		LEFT JOIN __PFX__Classes AS ClassHelper ON (__PFX__relClassesChainedContents.chainingClassREL = ClassHelper.classID)
	WHERE
		ClassHelper.class = ?
		AND __PFX__Contents.published = 1
	ORDER BY __PFX__Classes.class, __PFX__Aliases.alias ASC