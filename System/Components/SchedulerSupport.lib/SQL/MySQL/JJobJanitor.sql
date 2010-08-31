-- --
-- name: list
-- deterministic: yes
-- fields: 4
-- type: select
SELECT
		__PFX__Jobs.jobID,
		__PFX__Classes.class,
		__PFX__Jobs.start,
		__PFX__Jobs.stop
	FROM __PFX__Jobs
		LEFT JOIN __PFX__Classes ON (__PFX__Jobs.classREL = __PFX__Classes.classID)

-- --
-- name: delete
-- type: delete
-- inputTypes:	i
DELETE
	FROM __PFX__Jobs
	WHERE jobID = ?

-- --
-- name: add
-- type: insert
-- inputTypes:	si
INSERT
	INTO __PFX__Jobs (classREL, start, stop, rescheduleInterval)
	VALUES(
		(SELECT classID FROM __PFX__Classes WHERE class = ?),
		NOW(),
		NULL,
		?
	)
-- --
-- name: addEnding
-- type: insert
-- inputTypes:	ssi
INSERT
	INTO __PFX__Jobs (classREL, start, stop, rescheduleInterval)
	VALUES(
		(SELECT classID FROM __PFX__Classes WHERE class = ?),
		NOW(),
		?,
		?
	)

-- --
-- name: schedule
-- type: insert
-- inputTypes:	s
INSERT 
	INTO __PFX__JobSchedules (jobREL, scheduled)
	VALUES (
		(SELECT
				__PFX__Jobs.jobID
			FROM __PFX__Jobs
				LEFT JOIN __PFX__Classes ON (__PFX__Jobs.classREL = __PFX__Classes.classID)
			WHERE class = ?),
		NOW()
	)