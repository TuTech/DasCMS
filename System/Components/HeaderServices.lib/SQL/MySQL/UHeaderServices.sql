-- --
-- name: getServices
-- inputTypes:	s
-- deterministic: no
-- mutable: yes
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
		AND __PFX__Contents.pubDate > '0000-00-00 00:00:00'
		AND __PFX__Contents.pubDate <= NOW()
	ORDER BY __PFX__Classes.class, __PFX__Aliases.alias ASC