-- --
-- name: countItems
-- deterministic: no
-- inputTypes:	i
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relFeedsContents
		LEFT JOIN __PFX__Contents ON (__PFX__relFeedsContents.contentREL = __PFX__Contents.contentID)
	WHERE
		__PFX__relFeedsContents.feedREL = ?
		AND __PFX__relFeedsContents.feedREL != __PFX__relFeedsContents.contentREL
		AND __PFX__Contents.pubDate > 0
		AND __PFX__Contents.pubDate <= NOW()

-- --
-- name: sitemapData
-- deterministic: no
-- inputTypes:	i
-- fields: 2
-- type: select
SELECT
		__PFX__Aliases.alias,
		MAX(__PFX__Changes.changeDate)
	FROM __PFX__relFeedsContents
		LEFT JOIN __PFX__Contents
			ON (__PFX__relFeedsContents.contentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Changes
			ON (__PFX__Contents.contentID = __PFX__Changes.contentREL)
	WHERE
		__PFX__relFeedsContents.feedREL = ?
		AND
		__PFX__relFeedsContents.feedREL != __PFX__relFeedsContents.contentREL
		AND
		__PFX__Contents.pubDate > 0
		AND
		__PFX__Contents.pubDate <= NOW()
	GROUP BY __PFX__Aliases.alias

-- --
-- name: setType
-- inputTypes: iii
-- type: insert
INSERT
	INTO __PFX__Feeds(contentREL, filterType, lastUpdate, associatedItems)
		VALUES(?, ?, NOW(), 0)
		ON DUPLICATE KEY UPDATE
			filterType = ?,
			lastUpdate = NOW()

-- --
-- name: addTag
-- inputTypes: ss
-- type: insert
INSERT
	INTO __PFX__Tags(tag)
		VALUES(?)
		ON DUPLICATE KEY UPDATE
			tag = ?

-- --
-- name: unlink
-- inputTypes: i
-- type: delete
DELETE
	FROM __PFX__relFeedsTags
	WHERE feedREL = ?

-- --
-- name: link
-- inputTypes: is
-- type: insert
INSERT
	INTO __PFX__relFeedsTags (feedREL, tagREL)
	SELECT
			? AS feedREL,
			tagID
		FROM __PFX__Tags
		WHERE tag = ?

-- --
-- name: items
-- deterministic: no
-- inputTypes:	iii
-- fields: 8
-- type: select-template
SELECT
		__PFX__Contents.title,
		__PFX__Contents.description,
		__PFX__Contents.pubDate,
		__PFX__Aliases.alias,
		IF(ISNULL(__PFX__ChangedByUsers.login), '-', __PFX__ChangedByUsers.login),
		__PFX__Changes.changeDate,
		'' AS __PFX__Placeholder,
		__PFX__Contents.subtitle
	FROM __PFX__relFeedsContents
		LEFT JOIN __PFX__Contents
			ON (__PFX__relFeedsContents.contentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
		LEFT JOIN __PFX__Changes
			ON (__PFX__Contents.contentID = __PFX__Changes.contentREL AND __PFX__Changes.latest = 'Y')
		LEFT JOIN __PFX__ChangedByUsers
			ON (__PFX__Changes.userREL = __PFX__ChangedByUsers.changedByUserID)
	WHERE
		__PFX__relFeedsContents.feedREL = ?
		AND
		__PFX__relFeedsContents.feedREL != __PFX__relFeedsContents.contentREL
		AND
		__PFX__Contents.pubDate > 0
		AND
		__PFX__Contents.pubDate <= NOW()
	ORDER BY __PFX__Contents.__@1__ __@2__
	LIMIT ?
	OFFSET ?

-- --
-- name: feedAliases
-- deterministic: no
-- inputTypes:	i
-- fields: 1
-- type: select
SELECT DISTINCT
		__PFX__Aliases.alias
	FROM __PFX__relFeedsContents
		LEFT JOIN __PFX__Contents
			ON (__PFX__relFeedsContents.contentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
	WHERE
		__PFX__relFeedsContents.feedREL = ?
		AND 
		__PFX__relFeedsContents.feedREL != __PFX__relFeedsContents.contentREL
		AND 
		__PFX__Contents.pubDate > 0
		AND 
		__PFX__Contents.pubDate <= NOW()
	ORDER BY __PFX__Contents.pubDate DESC
	LIMIT 15