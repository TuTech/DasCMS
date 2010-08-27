-- --
-- name: stats
-- inputTypes:	i
-- deterministic: no
-- mutable: yes
-- fields: 4
-- type: select
SELECT
		MIN(accessTime) AS 'firstAccess',
		MAX(accessTime) AS 'lastAccess',
		COUNT(*) AS 'accessCount',
		FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(), MIN(accessTime)))/(COUNT(*)+1)) AS 'SecondsBetweenAccesses'
	FROM __PFX__AccessLog
		WHERE contentREL = ?