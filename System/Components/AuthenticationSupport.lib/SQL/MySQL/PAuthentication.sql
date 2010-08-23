-- --
-- name: log
-- type: insert
-- inputTypes:	iss
INSERT
	INTO __PFX__AuthorisationLog (IPAdr, UserName, Status)
	VALUES (?, ?, ?)

-- --
-- name: latestFails
-- inputTypes:	is
-- deterministic: no
-- mutable: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__AuthorisationLog
	WHERE
		`Status` = 'FAIL'
		AND
		`IPAdr` = ?
		AND
		`UserName` = ?
		AND
		`LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)

-- --
-- name: latestUserFails
-- inputTypes:	s
-- deterministic: no
-- mutable: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__AuthorisationLog
	WHERE
		`Status` = 'FAIL'
		AND
		`UserName` = ?
		AND
		`LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)

-- --
-- name: latestIPAdrFails
-- inputTypes:	i
-- deterministic: no
-- mutable: yes
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__AuthorisationLog
	WHERE
		`Status` = 'FAIL'
		AND
		`IPAdr` = ?
		AND
		`LoginTime` > DATE_SUB(NOW(), INTERVAL 15 MINUTE)