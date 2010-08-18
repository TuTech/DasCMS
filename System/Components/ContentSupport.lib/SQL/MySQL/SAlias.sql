-- --
-- name: isAliasAssigned
-- inputTypes:	si
-- deterministic: yes
-- mutable: no
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
-- mutable: no
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
-- mutable: no
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
-- mutable: no
-- fields: 1
-- type: select
SELECT
		alias
    FROM __PFX__Aliases
    LEFT JOIN __PFX__Contents
		ON (contentREL = contentID)
    WHERE
    	contentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
    	AND aliasID = primaryAlias

-- --
-- name: getMatching
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select-template
SELECT
		alias
	FROM __PFX__Aliases
	WHERE
		(alias = __@1__)
		AND contentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
		LIMIT 1