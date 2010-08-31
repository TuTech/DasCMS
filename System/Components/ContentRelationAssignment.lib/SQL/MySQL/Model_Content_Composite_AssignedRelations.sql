-- --
-- name: contentFormatter
-- deterministic: yes
-- fields: 1
-- inputTypes:	is
-- type: select
SELECT name
	FROM __PFX__Formatters
	LEFT JOIN __PFX__relContentsFormatters ON (formatterID = formatterREL)
	WHERE
		contentREL = ?
		AND
		classREL = (SELECT classID FROM __PFX__Classes WHERE class = ?)

-- --
-- name: unlink
-- inputTypes:	is
-- type: delete
DELETE
	FROM __PFX__relContentsFormatters
	WHERE
		contentREL = ?
		AND
		classREL = (SELECT classID FROM __PFX__Classes WHERE class = ?)

-- --
-- name: link
-- inputTypes:	iss
-- type: insert
INSERT
	INTO __PFX__relContentsFormatters
	SELECT
			? AS contentREL,
			formatterID AS formatterREL,
			(SELECT classID FROM __PFX__Classes WHERE class = ?) AS classREL
		FROM __PFX__Formatters
		WHERE __PFX__Formatters.name = ?