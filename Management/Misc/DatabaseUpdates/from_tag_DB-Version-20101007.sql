ALTER TABLE Contents
	ADD COLUMN published INT(1) NOT NULL DEFAULT 0
	AFTER pubDate

ALTER TABLE Contents
	ADD INDEX(published)

UPDATE Contents
	SET published = 1
	WHERE
		pubDate > '0000-00-00 00:00:00'
		AND
		pubDate <= NOW()

ALTER TABLE Contents
	ADD COLUMN revokeDate DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
	AFTER pubDate

UPDATE Classes SET class = CONCAT("View_UIElement_", RIGHT(class, LENGTH(class)-1))
	WHERE class LIKE "W%"