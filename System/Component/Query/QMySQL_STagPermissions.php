<?php
class QSTagPermissions extends BQuery 
{
    /**
     * @param int $contentId
     * @param string $user
     * @return boolean
     */
    public static function isPermitted($contentId, $user)
    {
        $sql = 
            "SELECT COUNT(*) AS FailedPermissions
                FROM Contents
                     LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
                     LEFT JOIN PermissionTags ON (relContentsTags.tagREL = PermissionTags.tagREL)
                WHERE Contents.contentID = %d
                      AND NOT ISNULL(PermissionTags.permissionTagID)
                      AND PermissionTags.permissionTagID  NOT IN 
                      (
                        SELECT DISTINCT PTID FROM 
                            (
                                SELECT relPermissionTagsGroups.permissionTagREL AS PTID
                                    FROM Users
                                         LEFT JOIN relUsersGroups ON (Users.userID = relUsersGroups.userREL)
                                         LEFT JOIN relPermissionTagsGroups USING (groupREL)
                                    WHERE Users.login = '%s'
                                UNION
                                SELECT relPermissionTagsUsers.permissionTagREL AS PTID
                                    FROM Users
                                         LEFT JOIN relPermissionTagsUsers ON(Users.userID = relPermissionTagsUsers.userREL)
                                    WHERE Users.login = '%s'
                            ) AS sub
                        )";
        $DB = BQuery::Database();
		$res = $DB->query(sprintf($sql, $contentId, $DB->escape($user), $DB->escape($user)), DSQL::NUM);
		list($failed) = $res->fetch();
		return ($failed == 0);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getProtectedTags()
    {
        $sql = 
            "SELECT Tags.tag
				FROM PermissionTags
				LEFT JOIN Tags ON (PermissionTags.tagREL = Tags.tagID)";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function setProtectedTags(array $tags)
    {
        $DB = BQuery::Database();
        if(count($tags))
        {
            $tagval = array();
			foreach ($tags as $tag) 
			{
				$tagval[] = array($tag);
			}
			$DB->insert('Tags',array('tag'),$tagval, true);
            $sql =
                "INSERT IGNORE INTO PermissionTags 
    				(tagREL) 
    					SELECT tagID as tagREL
    						FROM Tags
    						WHERE %s";
            $tsql = array();
        	foreach ($tags as $tag) 
        	{
        		$tsql[] = sprintf('tag = "%s"', $DB->escape($tag));
        	}
        	$tagsql = implode(' OR ', $tsql);
        	$DB->queryExecute(sprintf($sql, $tagsql));
        	
        	$sql = 
        	    "DELETE 
    				FROM PermissionTags
    				WHERE tagREL NOT IN 
    				(
    					SELECT tagID 
    						FROM Tags
    						WHERE %s
    				)";
        }
        else
        {
            $sql = 
        	    "DELETE 
    				FROM PermissionTags 
    				WHERE 1";
        }
	    $DB->queryExecute(sprintf($sql, $tagsql));
    }
    
    public static function setUserPermissions($name, array $tags)
    {
        $DB = BQuery::Database();
        //delete rels
        $sql = "DELETE FROM relPermissionTagsUsers WHERE userREL = (SELECT userID FROM Users WHERE login = '%s')";
        $DB->queryExecute(sprintf($sql, $DB->escape($name)));
        //create new rels
        if(count($tags))
        {
        	$sql = 
        	    "INSERT INTO relPermissionTagsUsers
					(permissionTagREL, userREL)
                        SELECT DISTINCT pt.ptagID, u.userID
                            FROM
                                (
                                    SELECT 1 AS link, userID 
                                        FROM Users 
                                        WHERE login = '%s'
                                ) AS u
                                LEFT JOIN
                                (
                                    SELECT 1 AS link, PermissionTags.permissionTagID AS ptagID
                                        FROM Tags
                                            LEFT JOIN PermissionTags ON (Tags.tagID = PermissionTags.tagREL)
                                        WHERE 
                                            %s
                                ) AS pt
                                USING(link)";
        	$tsql = array();
        	foreach ($tags as $tag) 
        	{
        		$tsql[] = sprintf('Tags.tag = "%s"', $DB->escape($tag));
        	}
        	$DB->queryExecute(sprintf($sql, $DB->escape($name), implode(' OR ', $tsql)));
        }
    }
    
    /**
     * @param string $name
     * @return DSQLResult
     */
    public static function getUserPermissionTags($name)
    {
        //resolve rels
        $sql = 
            "SELECT	Tags.tag
				FROM Users
    				LEFT JOIN relPermissionTagsUsers ON (relPermissionTagsUsers.userREL = Users.userID)
    				LEFT JOIN PermissionTags ON (relPermissionTagsUsers.permissionTagREL = PermissionTags.permissionTagID)
    				LEFT JOIN Tags ON (PermissionTags.tagREL = Tags.tagID)
				WHERE Users.login = '%s'";
        return $DB->query(sprintf($sql, $DB->escape($name)), DSQL::NUM);
    }
    
    public static function setGroupPermissions($name, array $tags)
    {
        $sql = "DELETE FROM relPermissionTagsGroups WHERE groupREL = (SELECT groupID FROM Groups WHERE groupName = '%s')";
        $DB->queryExecute(sprintf($sql, $DB->escape($name)));
        //create new rels
        if(count($tags))
        {
        	$sql = 
        	    "INSERT INTO relPermissionTagsGroups
					(permissionTagREL, groupREL)
                        SELECT DISTINCT pt.ptagID, g.groupID
                            FROM
                                (
                                    SELECT 1 AS link, groupID 
                                        FROM Groups 
                                        WHERE groupName = '%s'
                                ) AS g
                                LEFT JOIN
                                (
                                    SELECT 1 AS link, PermissionTags.permissionTagID AS ptagID
                                        FROM Tags
                                            LEFT JOIN PermissionTags ON (Tags.tagID = PermissionTags.tagREL)
                                        WHERE 
                                            %s
                                ) AS pt
                                USING(link)";
        	$tsql = array();
        	foreach ($tags as $tag) 
        	{
        		$tsql[] = sprintf('Tags.tag = "%s"', $DB->escape($tag));
        	}
        	$DB->queryExecute(sprintf($sql, $DB->escape($name), implode(' OR ', $tsql)));
        }
    }
    
    /**
     * @param string $name
     * @return DSQLResult
     */
    public static function getGroupPermissionTags($name)
    {
        //resolve rels
        $sql = 
            "SELECT	Tags.tag
				FROM Groups
    				LEFT JOIN relPermissionTagsGroups ON (relPermissionTagsGroups.groupREL = Groups.groupID)
    				LEFT JOIN PermissionTags ON (relPermissionTagsGroups.permissionTagREL = PermissionTags.permissionTagID)
    				LEFT JOIN Tags ON (PermissionTags.tagREL = Tags.tagID)
				WHERE Groups.groupName = '%s'";
        return $DB->query(sprintf($sql, $DB->escape($name)), DSQL::NUM);
    }
}
?>