-- --
-- name: cleanup
-- type: delete
DELETE
	FROM __PFX__JobSchedules
	WHERE started <= DATE_SUB(NOW(), INTERVAL 1 WEEK)