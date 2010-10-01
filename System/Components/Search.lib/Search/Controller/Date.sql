-- --
-- name: initOrdering
-- type: set
SET @orderingScore = 0;

-- --
-- name: order
-- type: insert
-- inputTypes: ii
INSERT INTO `SearchResults`
	SELECT
			? AS searchREL,
			contentREL,
			1/(@orderingScore := @orderingScore+1) as score,
			@orderingScore as itemNr
		FROM
			(SELECT
					contentREL
				FROM SearchResults
					LEFT JOIN Contents ON (contentID = contentREL)
				WHERE
					searchREL = ?
				ORDER BY pubDate
			) AS temp
	ON DUPLICATE KEY UPDATE
		score = 1/@orderingScore,
		itemNr = @orderingScore