-- --
-- name: createChain
-- type: insert
-- inputTypes:	sss
INSERT IGNORE
	INTO __PFX__relContentsClassesChainedContents(
		chainingClassREL,
		ownerContentREL,
		chainedContentREL
	)
	VALUES(
		(SELECT classID    FROM __PFX__Classes WHERE class = ?),
		(SELECT contentREL FROM __PFX__Aliases WHERE alias = ?),
		(SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
	)

-- --
-- name: getAllChainedToClass
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT __PFX__Aliases.alias
	FROM __PFX__relContentsClassesChainedContents
		LEFT JOIN __PFX__Contents
			ON (__PFX__relContentsClassesChainedContents.chainedContentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
	WHERE
		__PFX__relContentsClassesChainedContents.chainingClassREL = (
			SELECT classID
				FROM __PFX__Classes
				WHERE class = ?
		)

-- --
-- name: getAllChainedToClassAndContent
-- inputTypes:	ss
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT __PFX__Aliases.alias
	FROM __PFX__relContentsClassesChainedContents
		LEFT JOIN __PFX__Contents
			ON (__PFX__relContentsClassesChainedContents.chainedContentREL = __PFX__Contents.contentID)
		LEFT JOIN __PFX__Aliases
			ON (__PFX__Contents.primaryAlias = __PFX__Aliases.aliasID)
	WHERE
		__PFX__relContentsClassesChainedContents.ownerContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)
		AND
		__PFX__relContentsClassesChainedContents.chainingClassREL = (
			SELECT classID FROM __PFX__Classes WHERE class = ?
		)

-- --
-- name: getClassesChaining
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT __PFX__Classes.class
	FROM __PFX__relContentsClassesChainedContents
		LEFT JOIN __PFX__Aliases
			ON (__PFX__relContentsClassesChainedContents.chainedContentREL = __PFX__Aliases.contentREL)
		LEFT JOIN __PFX__Classes
			ON (__PFX__relContentsClassesChainedContents.chainingClassREL = __PFX__Classes.classID)
	WHERE
		__PFX__Aliases.alias = ?

-- --
-- name: getContentsChaining
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 4
-- type: select
SELECT
		OwnerAliases.alias,
		OwnerClasses.class,
		OwnerContents.title,
		ChainingClasses.class
	FROM __PFX__relContentsClassesChainedContents
		LEFT JOIN __PFX__Aliases
			ON (__PFX__relContentsClassesChainedContents.chainedContentREL = __PFX__Aliases.contentREL)
		LEFT JOIN __PFX__Contents AS OwnerContents
			ON (__PFX__relContentsClassesChainedContents.ownerContentREL = OwnerContents.contentID)
		LEFT JOIN __PFX__Classes AS OwnerClasses
			ON (OwnerContents.type = OwnerClasses.classID)
		LEFT JOIN __PFX__Aliases AS OwnerAliases
			ON (OwnerContents.primaryAlias = OwnerAliases.aliasID)
		LEFT JOIN __PFX__Classes AS ChainingClasses
			ON (__PFX__relContentsClassesChainedContents.chainingClassREL = ChainingClasses.classID)
	WHERE
		__PFX__Aliases.alias = ?

-- --
-- name: getRetainCount
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		chainedContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)

-- --
-- name: deleteClassChains
-- type: delete
-- inputTypes:	s
DELETE
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		chainingClassREL = (
			SELECT classID FROM __PFX__Classes WHERE class = ?
		)

-- --
-- name: deleteClassChainsForOwner
-- type: delete
-- inputTypes:	ss
DELETE
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		chainingClassREL = (
			SELECT classID FROM __PFX__Classes WHERE class = ?
		)
		AND
		ownerContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)

-- --
-- name: deleteOwnerChains
-- type: delete
-- inputTypes:	s
DELETE
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		ownerContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)

-- --
-- name: deleteChain
-- type: delete
-- inputTypes:	sss
DELETE
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		chainingClassREL = (SELECT classID FROM __PFX__Classes WHERE class = ?)
		AND
		ownerContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)
		AND
		chainedContentREL = (SELECT contentREL FROM __PFX__Aliases WHERE alias = ?)

-- --
-- name: isRetained
-- inputTypes:	s
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		__PFX__relContentsClassesChainedContents.chainedContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)

-- --
-- name: isRetainedOwner
-- inputTypes:	ss
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		__PFX__relContentsClassesChainedContents.chainedContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)
		AND
		__PFX__relContentsClassesChainedContents.ownerContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)

-- --
-- name: isRetainedClass
-- inputTypes:	ss
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		__PFX__relContentsClassesChainedContents.chainedContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)
		AND __PFX__relContentsClassesChainedContents.chainingClassREL = (
			SELECT classID FROM __PFX__Classes WHERE class = ?
		)

-- --
-- name: isRetainedOwnerClass
-- inputTypes:	sss
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__relContentsClassesChainedContents
	WHERE
		__PFX__relContentsClassesChainedContents.chainedContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)
		AND
		__PFX__relContentsClassesChainedContents.ownerContentREL = (
			SELECT contentREL FROM __PFX__Aliases WHERE alias = ?
		)
		AND
		__PFX__relContentsClassesChainedContents.chainingClassREL = (
			SELECT classID FROM __PFX__Classes WHERE class = ?
		)
