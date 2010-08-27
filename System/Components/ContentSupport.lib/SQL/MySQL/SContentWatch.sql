-- --
-- name: log
-- inputTypes: iii
-- type: insert
-- invalidateCache: no
INSERT IGNORE
	INTO __PFX__AccessLog(contentREL, countyCodeHash, ipAddressHash)
	VALUES(?, ?, ?)