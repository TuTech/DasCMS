-- --
-- name: updateStats
-- type: update
-- inputTypes:	ii
UPDATE __PFX__Feeds
	SET lastUpdate = NOW(),
		associatedItems = (
			SELECT COUNT(*)
				FROM __PFX__relFeedsContents
				WHERE feedREL = ?
		)
	WHERE contentREL = ?

-- --
-- name: setType
-- type: insert
-- inputTypes:	iii
INSERT
	INTO __PFX__Feeds (contentREL, filterType, lastUpdate, associatedItems)
	VALUES (?, ?, NOW(), 0)
	ON DUPLICATE KEY UPDATE
		filterType = ?,
		lastUpdate = NOW()

-- --
-- name: clear
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__relFeedsContents
	WHERE feedREL = ?

-- --
-- name: unlink
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__relFeedsContents
	WHERE contentREL = ?

-- --
-- name: link
-- type: insert
-- inputTypes:	ii
INSERT IGNORE
	INTO __PFX__relFeedsContents (feedREL, contentREL)
	VALUES (?, ?)

-- --
-- name: getType
-- deterministic: yes
-- inputTypes:	i
-- fields: 1
-- type: select
SELECT filterType
	FROM __PFX__Feeds
	WHERE contentREL = ?
	LIMIT 1

-- --
-- name: feedsWithType
-- deterministic: yes
-- fields: 2
-- type: select
SELECT contentREL,filterType
	FROM __PFX__Feeds

-- --
-- name: feedsWithTypeAndTags
-- deterministic: yes
-- fields: 3
-- type: select
SELECT
		__PFX__Feeds.contentREL,
		__PFX__Feeds.filterType,
		__PFX__Tags.tag
	FROM __PFX__Feeds
		LEFT JOIN __PFX__relFeedsTags ON (__PFX__Feeds.contentREL = __PFX__relFeedsTags.feedREL)
		LEFT JOIN __PFX__Tags ON (__PFX__relFeedsTags.tagREL = __PFX__Tags.tagID)

-- --
-- name: assignToAll
-- type: insert
-- inputTypes:	ii
INSERT
	INTO __PFX__relFeedsContents
	SELECT ? AS feedREL, contentID AS contentREL
		FROM __PFX__Contents
		WHERE contentID != ?


-- --
-- name: assignMatchSome
-- type: insert
-- inputTypes:	ii
INSERT
	INTO __PFX__relFeedsContents
	SELECT ? AS feedREL, contentREL
		FROM __PFX__relContentsTags
		WHERE tagREL IN
		(
			SELECT tagREL
				FROM __PFX__relFeedsTags
				WHERE feedREL = ?
		)

-- --
-- name: assignMatchAll
-- type: insert
-- inputTypes:	iii
INSERT
	INTO __PFX__relFeedsContents
	SELECT ? AS feedREL, contentREL
		FROM
		(
			SELECT contentREL, COUNT(*) AS nrOfTags
				FROM __PFX__relContentsTags
				WHERE tagREL IN
				(
					SELECT tagREL
						FROM __PFX__relFeedsTags
						WHERE feedREL = ?
				)
				GROUP BY contentREL
		) AS derived
		WHERE derived.nrOfTags =
		(
			SELECT COUNT(*)
				FROM __PFX__relFeedsTags
				WHERE feedREL = ?
		)
-- --
-- name: assignMatchNone
-- type: insert
-- inputTypes:	ii
INSERT
	INTO __PFX__relFeedsContents
	SELECT DISTINCT ? AS feedREL, contentID AS contentREL
		FROM __PFX__Contents
		LEFT JOIN __PFX__relContentsTags ON (__PFX__Contents.contentID = __PFX__relContentsTags.contentREL)
		WHERE
			ISNULL(tagREL)
			OR tagREL NOT IN
			(
				SELECT tagREL
					FROM __PFX__relFeedsTags
					WHERE feedREL = ?
			)
