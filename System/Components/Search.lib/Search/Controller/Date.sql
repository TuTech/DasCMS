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
			1/(@orderingScore := @orderingScore+1) AS score,
			@orderingScore AS itemNr
		FROM
			(SELECT
					contentREL
				FROM __PFX__SearchResults
					LEFT JOIN __PFX__Contents
						ON (contentID = contentREL)
				WHERE
					searchREL = ?
				ORDER BY pubDate
			) AS temp
	ON DUPLICATE KEY UPDATE
		score = 1/@orderingScore,
		itemNr = @orderingScore