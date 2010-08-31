-- --
-- name: contentFormatter
-- deterministic: yes
-- fields: 1
-- inputTypes:	i
-- type: select
SELECT name
	FROM __PFX__Formatters
	LEFT JOIN __PFX__relContentsFormatters ON (formatterID = formatterREL)
	WHERE
		contentREL = ?
		AND
		ISNULL(classREL)
	LIMIT 1

-- --
-- name: unlink
-- inputTypes:	i
-- type: delete
DELETE
	FROM __PFX__relContentsFormatters
	WHERE
		contentREL = ?
		AND
		ISNULL(classREL)

-- --
-- name: link
-- inputTypes:	is
-- type: insert
INSERT
	INTO __PFX__relContentsFormatters
	SELECT
			? AS contentREL,
			formatterID AS formatterREL,
			NULL AS classREL
		FROM __PFX__Formatters
		WHERE __PFX__Formatters.name = ?