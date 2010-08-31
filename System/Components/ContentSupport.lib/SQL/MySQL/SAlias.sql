-- --
-- name: isAliasAssigned
-- inputTypes:	si
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		COUNT(*)
	FROM __PFX__Aliases
	WHERE
		alias = ?
		AND contentREL = ?

-- --
-- name: setActive
-- type: update
-- inputTypes:	ss
UPDATE
		__PFX__Contents
	SET primaryAlias = (SELECT aliasID FROM __PFX__Aliases WHERE alias = ?)
	WHERE contentID = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)

-- --
-- name: addAlias
-- type: insert
-- inputTypes:	si
INSERT IGNORE
	INTO __PFX__Aliases (alias, contentREL)
		VALUES(?, ?)

-- --
-- name: match
-- inputTypes:	ss
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		COUNT(*)
	FROM __PFX__Aliases
	WHERE
		alias = ?
		OR alias = ?

-- --
-- name: resolve
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		contentREL
	FROM __PFX__Aliases
	WHERE alias = ?

-- --
-- name: getPrimary
-- inputTypes:	s
-- deterministic: yes
-- fields: 1
-- type: select
SELECT
		PriAlias.alias
    FROM __PFX__Aliases
    LEFT JOIN __PFX__Contents
		ON (__PFX__Aliases.contentREL = __PFX__Contents.contentID)
	LEFT JOIN __PFX__Aliases AS PriAlias
		ON(__PFX__Contents.primaryAlias = PriAlias.aliasID)
    WHERE
    	__PFX__Aliases.alias = ?

-- --
-- name: getMatching
-- deterministic: yes
-- fields: 1
-- type: select-template
SELECT
		alias
	FROM __PFX__Aliases
	WHERE
		(alias = __@1__)
		AND contentREL = ?
		LIMIT 1