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