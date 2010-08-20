-- --
-- name: load
-- deterministic: yes
-- mutable: no
-- fields: 2
-- type: select
SELECT guid, class
	FROM __PFX__Classes
	WHERE LENGTH(guid) > 0