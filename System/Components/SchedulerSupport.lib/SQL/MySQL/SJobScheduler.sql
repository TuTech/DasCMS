-- --
-- name: count
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__Jobs

-- --
-- name: next
-- deterministic: no
-- mutable: yes
-- fields: 3
-- type: select
SELECT
		__PFX__Classes.class,
		__PFX__Jobs.jobID,
		__PFX__JobSchedules.scheduled
	FROM __PFX__Jobs
		LEFT JOIN __PFX__Classes
			ON (__PFX__Jobs.classREL = __PFX__Classes.classID)
		LEFT JOIN __PFX__JobSchedules
			ON (__PFX__Jobs.jobID = __PFX__JobSchedules.jobREL)
	WHERE
		__PFX__Jobs.start <= NOW()
		AND (
			__PFX__Jobs.stop > NOW()
			OR
			ISNULL(__PFX__Jobs.stop)
		)
		AND
		ISNULL(__PFX__JobSchedules.started)
		AND
		__PFX__JobSchedules.scheduled > 0
		AND
		__PFX__JobSchedules.scheduled <= NOW()
	ORDER BY __PFX__JobSchedules.scheduled ASC
	LIMIT 1
	FOR UPDATE

-- --
-- name: start
-- inputTypes: is
-- type:insert
UPDATE __PFX__JobSchedules
	SET
		started = NOW()
	WHERE
		jobREL = ?
		AND
		scheduled = ?

-- --
-- name: schedule
-- inputTypes: ii
-- type:insert
INSERT
	INTO __PFX__JobSchedules (jobREL, scheduled)
	VALUES (
		?,
		DATE_ADD(
			NOW(),
			INTERVAL (
				SELECT
						rescheduleInterval
					FROM __PFX__Jobs
					WHERE jobID = ?
			)
			SECOND
		)
	)

-- --
-- name: report
-- inputTypes: isis
-- type:update
UPDATE __PFX__JobSchedules
	SET
		exitCode = ?,
		exitMessage = ?,
		finished = NOW()
	WHERE
		jobREL = ?
		AND
		scheduled = ?