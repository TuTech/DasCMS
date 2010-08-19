-- --
-- name: countAssigned
-- inputTypes:	i
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX____@1__
	WHERE contentAggregatorREL = ?