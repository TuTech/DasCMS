-- --
-- name: resetStats
-- type: delete
DELETE
	FROM __PFX__TagScores;

-- --
-- name: buildStats
-- type: insert
INSERT
	INTO __PFX__TagScores
	SELECT
			tagID AS tagREL,
			COUNT(*)/ x,
			CEIL(COUNT(*)/ x * 100)
		FROM __PFX__Tags
			LEFT JOIN __PFX__relContentsTags
				ON (tagID = tagREL)
			JOIN (
				SELECT
						MAX(c) AS x
					FROM (
						SELECT COUNT(tagREL) AS c
							FROM __PFX__relContentsTags
								LEFT JOIN __PFX__Tags ON (tagREL = tagID)
							WHERE tag NOT LIKE '@%'
							GROUP BY tagREL
					) AS TagsWithCount
				) AS MaxTagCount
		WHERE tag NOT LIKE '@%'
		GROUP BY tagID

-- --
-- name: listTagsOf
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT __PFX__Tags.tag
	FROM __PFX__Contents
		LEFT JOIN __PFX__relContentsTags
			ON (__PFX__Contents.contentID = __PFX__relContentsTags.contentREL)
		LEFT JOIN __PFX__Tags
			ON (__PFX__relContentsTags.tagREL = __PFX__Tags.tagID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
	WHERE
		__PFX__Aliases.alias = ?
	ORDER BY __PFX__Tags.tag ASC

-- --
-- name: aliasToId
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		__PFX__Contents.contentID
	FROM __PFX__Contents
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.contentID = __PFX__Aliases.contentREL)
	WHERE
		__PFX__Aliases.alias = ?
	LIMIT 1

-- --
-- name: unlink
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__relContentsTags
	WHERE contentREL = ?

-- --
-- name: setTag
-- type: insert
-- inputTypes: s
INSERT IGNORE
	INTO __PFX__Tags (tag)
		VALUES(?)

-- --
-- name: linkTag
-- type: insert
-- inputTypes: is
INSERT
	INTO __PFX__relContentsTags (tagREL, contentREL)
		SELECT
				tagID AS tagREL,
				? AS contentREL
			FROM __PFX__Tags
			WHERE tag = ?