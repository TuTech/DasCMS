<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QSTagPermissions extends BQuery 
{
    /**
     * @param int $contentId
     * @param string $user
     * @return boolean
     */
    public static function isPermitted($contentId, $user)
    {
        $DB = BQuery::Database();
        $sql = 
            "SELECT COUNT(*) AS FailedPermissions
                FROM Contents
                     LEFT JOIN relContentsTags ON (Contents.contentID = relContentsTags.contentREL)
                     LEFT JOIN PermissionTags ON (relContentsTags.tagREL = PermissionTags.permissionTagREL)
                WHERE Contents.contentID = %d
                      AND PermissionTags.permissionTagREL  NOT IN 
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
        $sql = sprintf($sql, $contentId, $DB->escape($user), $DB->escape($user));
		$res = $DB->query($sql, DSQL::NUM);
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
				LEFT JOIN Tags ON (PermissionTags.permissionTagREL = Tags.tagID)";
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
    
    public static function insertTags(array $tags)
    {
        if(count($tags))
        {
            $DB = BQuery::Database();
            $tagData = array();
            foreach ($tags as $tag)
            {
                $tagData[] = '("'.$DB->escape($tag).'")';
            }
            $sql = 'INSERT IGNORE INTO Tags (tag) VALUES '.implode(', ', $tagData);
            $DB->queryExecute($sql);
        }
    }
    
    public static function removeAllTags()
    {
        $DB = BQuery::Database();
        $sql = 
    	    "DELETE 
				FROM PermissionTags 
				WHERE 1";
	    $DB->queryExecute($sql);
    }
    
    public static function setProtectedTags(array $tags)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $fail = false;
        try
        {
            self::removeAllTags();
            if(count($tags))
            {
                $tags = array_unique($tags);
                self::insertTags($tags);
                $tsql = array();
            	foreach ($tags as $tag) 
            	{
            		$tsql[] = sprintf('tag = "%s"', $DB->escape($tag));
            	}
                $sql =
                    "INSERT INTO PermissionTags 
        				(permissionTagREL) 
        					SELECT tagID as permissionTagREL
        						FROM Tags
        						WHERE ".implode(' OR ',$tsql);
                $DB->queryExecute($sql);
            }
        }
        catch (XDatabaseException $e)
        {
            $DB->rollback();
            SNotificationCenter::report('warning', $e->getMessage());
            $fail = true;
        }
        if(!$fail)
        {
            $DB->commit();
        }
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
                                    SELECT 1 AS link, PermissionTags.permissionTagREL AS ptagID
                                        FROM Tags
                                            LEFT JOIN PermissionTags ON (Tags.tagID = PermissionTags.permissionTagREL)
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
        $DB = BQuery::Database();
        //resolve rels
        $sql = 
            "SELECT	Tags.tag
				FROM Users
    				LEFT JOIN relPermissionTagsUsers ON (relPermissionTagsUsers.userREL = Users.userID)
    				LEFT JOIN PermissionTags USING (permissionTagREL)
    				LEFT JOIN Tags ON (PermissionTags.permissionTagREL = Tags.tagID)
				WHERE Users.login = '%s'";
        return $DB->query(sprintf($sql, $DB->escape($name)), DSQL::NUM);
    }
    
    public static function setGroupPermissions($name, array $tags)
    {
        $DB = BQuery::Database();
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
                                    SELECT 1 AS link, PermissionTags.permissionTagREL AS ptagID
                                        FROM Tags
                                            LEFT JOIN PermissionTags ON (Tags.tagID = PermissionTags.permissionTagREL)
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
        $DB = BQuery::Database();
        //resolve rels
        $sql = 
            "SELECT	Tags.tag
				FROM Groups
    				LEFT JOIN relPermissionTagsGroups ON (relPermissionTagsGroups.groupREL = Groups.groupID)
    				LEFT JOIN PermissionTags USING (permissionTagREL)
    				LEFT JOIN Tags ON (PermissionTags.permissionTagREL = Tags.tagID)
				WHERE Groups.groupName = '%s'";
        return $DB->query(sprintf($sql, $DB->escape($name)), DSQL::NUM);
    }
}
?>