-- --
-- name: gather
-- type: insert
-- inputTypes: is
INSERT IGNORE
	INTO __PFX__SearchResults(searchREL, contentREL)
	SELECT
			? AS searchREL,
			contentID AS contentREL
		FROM __PFX__Contents
			WHERE title LIKE ?

-- --
-- name: filterRequire
-- type: delete
-- inputTypes: is
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND title NOT LIKE ?

-- --
-- name: filterVeto
-- type: delete
-- inputTypes: is
DELETE
	__PFX__SearchResults
	FROM __PFX__SearchResults
		LEFT JOIN __PFX__Contents ON (contentREL = contentID)
	WHERE
		searchREL = ?
		AND title LIKE ?

-- --
-- name: initOrdering
-- type: select
SELECT 0 INTO @orderingScore;

-- --
-- name: order
-- type: insert
-- inputTypes: ii
INSERT INTO __PFX__SearchResults
	SELECT
			? AS searchREL,
			contentREL,
			1/(@orderingScore := @orderingScore+1) as score,
			@orderingScore as itemNr
		FROM
			(SELECT
					contentREL
				FROM __PFX__SearchResults
					LEFT JOIN __PFX__Contents ON (contentID = contentREL)
				WHERE
					searchREL = ?
				ORDER BY title
			) AS temp
	ON DUPLICATE KEY UPDATE
		score = 1/@orderingScore,
		itemNr = @orderingScore