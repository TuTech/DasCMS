-- --
-- name: cleanup
-- type: delete
DELETE
	FROM JobSchedules
	WHERE started <= DATE_SUB(NOW(), INTERVAL 1 WEEK)