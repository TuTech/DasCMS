-- --
-- name: getAggregatorName
-- inputTypes:	i
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT
		__PFX__ContentAggregators.name
	FROM __PFX__ContentAggregators
	LEFT JOIN __PFX__relContentsAggregator
		ON (contentAggregatorID = contentAggregatorREL)
	WHERE contentREL = ?

-- --
-- name: removeAggregator
-- type: delete
-- inputTypes:	i
DELETE 
	FROM __PFX__relContentsAggregator
	WHERE contentREL = ?

-- --
-- name: setAggregator
-- type: insert
-- inputTypes:	is
INSERT 
	INTO __PFX__relContentsAggregator
		SELECT
				? AS contentREL,
				contentAggregatorID AS contentAggregatorREL
			FROM __PFX__ContentAggregators
			WHERE __PFX__ContentAggregators.name = ?