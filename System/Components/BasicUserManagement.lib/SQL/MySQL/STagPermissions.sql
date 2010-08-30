-- --
-- name: isProtected
-- deterministic: yes
-- inputTypes:	i
-- mutable: no
-- fields: 1
-- type: select
SELECT COUNT(*)
	FROM __PFX__PermissionTags
		LEFT JOIN __PFX__relContentsTags ON (permissionTagREL = tagREL)
	WHERE contentREL = ?

-- --
-- name: check
-- deterministic: yes
-- inputTypes:	iss
-- mutable: no
-- fields: 1
-- type: select
SELECT
		COUNT(*) AS FailedPermissions
	FROM __PFX__Contents
		 LEFT JOIN __PFX__relContentsTags
			ON (__PFX__Contents.contentID = __PFX__relContentsTags.contentREL)
		 LEFT JOIN __PFX__PermissionTags
			ON (__PFX__relContentsTags.tagREL = __PFX__PermissionTags.permissionTagREL)
	WHERE
		__PFX__Contents.contentID = ?
		AND
		__PFX__PermissionTags.permissionTagREL  NOT IN   (
			SELECT
					__PFX__relPermissionTagsGroups.permissionTagREL AS PTID
				FROM __PFX__Users
					LEFT JOIN __PFX__relUsersGroups
						ON (__PFX__Users.userID = __PFX__relUsersGroups.userREL)
					LEFT JOIN __PFX__relPermissionTagsGroups
						USING (groupREL)
				WHERE __PFX__Users.login = ?
			UNION
			SELECT
					__PFX__relPermissionTagsUsers.permissionTagREL AS PTID
				FROM __PFX__Users
					LEFT JOIN __PFX__relPermissionTagsUsers
						ON(__PFX__Users.userID = __PFX__relPermissionTagsUsers.userREL)
				WHERE __PFX__Users.login = ?
		)

-- --
-- name: getTags
-- deterministic: yes
-- mutable: no
-- fields: 1
-- type: select
SELECT tag
	FROM __PFX__PermissionTags
	LEFT JOIN __PFX__Tags ON (permissionTagREL = tagID)

-- --
-- name: getUserTags
-- deterministic: yes
-- inputTypes:	s
-- mutable: no
-- fields: 1
-- type: select
SELECT	__PFX__Tags.tag
	FROM __PFX__Users
		LEFT JOIN __PFX__relPermissionTagsUsers
			ON (__PFX__relPermissionTagsUsers.userREL = __PFX__Users.userID)
		LEFT JOIN __PFX__PermissionTags
			USING (permissionTagREL)
		LEFT JOIN __PFX__Tags
			ON (__PFX__PermissionTags.permissionTagREL = __PFX__Tags.tagID)
	WHERE __PFX__Users.login = ?

-- --
-- name: getGroupTags
-- deterministic: yes
-- inputTypes:	s
-- mutable: no
-- fields: 1
-- type: select
SELECT	__PFX__Tags.tag
	FROM __PFX__Groups
		LEFT JOIN __PFX__relPermissionTagsGroups
			ON (__PFX__relPermissionTagsGroups.groupREL = __PFX__Groups.groupID)
		LEFT JOIN __PFX__PermissionTags
			USING (permissionTagREL)
		LEFT JOIN __PFX__Tags
			ON (__PFX__PermissionTags.permissionTagREL = __PFX__Tags.tagID)
	WHERE __PFX__Groups.groupName = ?

-- --
-- name: clear
-- type: delete
DELETE
	FROM __PFX__PermissionTags

-- --
-- name: set
-- inputTypes:	s
-- type: insert
INSERT
	INTO __PFX__PermissionTags (permissionTagREL)
    SELECT tagID as permissionTagREL
    	FROM __PFX__Tags
    	WHERE tag = ?

-- --
-- name: clearUser
-- inputTypes:	s
-- type: delete
DELETE
	FROM __PFX__relPermissionTagsUsers
	WHERE userREL = (SELECT userID FROM __PFX__Users WHERE login = ?)

-- --
-- name: setUser
-- inputTypes:	ss
-- type: insert
INSERT
	INTO __PFX__relPermissionTagsUsers(permissionTagREL, userREL)
    SELECT DISTINCT
			tagID,
			userID
		FROM
			__PFX__Users
			JOIN __PFX__Tags
		WHERE
			login = ?
			AND
			tag = ?

-- --
-- name: clearGroup
-- inputTypes:	s
-- type: delete
DELETE
	FROM __PFX__relPermissionTagsGroups
	WHERE groupREL = (SELECT groupID FROM __PFX__Groups WHERE groupName = ?)

-- --
-- name: setGroup
-- inputTypes:	ss
-- type: insert
INSERT
	INTO __PFX__relPermissionTagsGroups(permissionTagREL, groupREL)
    SELECT DISTINCT
			tagID,
			groupID
		FROM
			__PFX__Groups
			JOIN __PFX__Tags
		WHERE
			groupName = ?
			AND
			tag = ?