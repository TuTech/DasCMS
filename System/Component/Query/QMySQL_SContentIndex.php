<?php
class QMySQL_SContentIndex extends BQuery 
{
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getDBID($alias)
    {
        $sql = "
            SELECT Contents.contentID
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	WHERE Aliases.alias = '%s'";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($alias)));
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getBasicInformation(array $aliases)
    {
        $DB = BQuery::Database();
        if(count($aliases) == 0)
        {
            $sel = '0';
        }
        else
        {
            $alias_esc = array();
            foreach ($aliases as $alias) 
            {
            	$alias_esc[] = 'Aliases.alias = "'.$DB->escape($alias).'"';
            }
            
            $sel = implode(' OR ', $alias_esc);
        }
        $sql = "
            SELECT 
            		Contents.title AS Title,
            		Contents.pubDate AS PubDate,
            		Aliases.alias AS Alias
            	FROM Contents 
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	WHERE ".
                    $sel;
       return $DB->query($sql);
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getPrimaryAliases(array $aliases)
    {
        $DB = BQuery::Database();
        if(count($aliases) == 0)
        {
            $sel = '0';
        }
        else
        {
            $alias_esc = array();
            foreach ($aliases as $alias) 
            {
            	$alias_esc[] = 'Aliases.alias = "'.$DB->escape($alias).'"';
            }
            
            $sel = implode(' OR ', $alias_esc);
        }
        $sql = "
            SELECT 
            		Help.request AS request,
            		Aliases.alias AS primary
            	FROM (
            		SELECT contentREL AS contentID, alias AS request FROM Aliases
            		WHERE %s
            	) AS Help
            	LEFT JOIN Contents UNSING (contentID)
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)";
       return $DB->query(sprintf($sql, $sel));
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getBasicInformationForClass($class)
    {
        $DB = BQuery::Database();
        $sql = "
            SELECT 
            		Contents.title AS Title,
            		Contents.pubDate AS PubDate,
            		Aliases.alias AS Alias
            	FROM Contents 
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
				LEFT JOIN Classes ON (Contents.type = Classes.classID)
            	WHERE 
					Classes.class = '%s'
				ORDER BY Contents.title ASC";
       return $DB->query(sprintf($sql, $DB->escape($class)));
    }
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function deleteContent($dbid)
    {
        $DB = BQuery::Database();
        $DB->query(sprintf("DELETE FROM Contents WHERE contentID = %d", $dbid));
        $DB->query(sprintf("DELETE FROM ContentSummaries WHERE contentID = %d", $dbid));
    }    
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getMetaInformation($alias)
    {
        $DB = BQuery::Database();
        $sql = "
            SELECT 
            		Contents.title,
            		Contents.pubDate,
            		Contents.desciption,
            		Classes.class,
            		Aliases.alias,
            		Changes.size,
            		Changes.changeDate,
            		Users.login,
            		Users.name,
            		Groups.groupName
            	FROM 
            		Contents
            		LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            		LEFT JOIN Classes ON (Contents.type = Classes.classID)
            		LEFT JOIN Changes ON (Contents.contentID = Changes.contentREL)
            		LEFT JOIN Users ON (Changes.userREL = Users.userID)
            		LEFT JOIN Groups ON (Users.primaryGroup = Groups.groupID)
            	WHERE
            		Aliases.alias = '%s'
            	ORDER BY Changes.changeDate DESC
            	LIMIT 1";
        return $DB->query(sprintf($sql, $DB->escape($alias)));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getCreateInformation($alias)
    {
        $DB = BQuery::Database();
        $sql = "
            SELECT 
            		Changes.title,
            		Changes.size,
            		Changes.changeDate,
            		Users.login,
            		Users.name,
            		Groups.groupName
            	FROM 
            		Contents
            		LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            		LEFT JOIN Classes ON (Contents.type = Classes.classID)
            		LEFT JOIN Changes ON (Contents.contentID = Changes.contentREL)
            		LEFT JOIN Users ON (Changes.userREL = Users.userID)
            		LEFT JOIN Groups ON (Users.primaryGroup = Groups.groupID)
            	WHERE
            		Aliases.alias = '%s'
            	ORDER BY Changes.changeDate ASC
            	LIMIT 1";
        return $DB->query(sprintf($sql, $DB->escape($alias)));
    }
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function getChangeHistory($alias)
    {
        $DB = BQuery::Database();
        $sql = "
            SELECT 
            		Changes.title,
            		Changes.size,
            		Changes.changeDate,
            		Users.login,
            		Users.name,
            	FROM 
            		Contents
            		LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            		LEFT JOIN Changes ON (Contents.contentID = Changes.contentREL)
            		LEFT JOIN Users ON (Changes.userREL = Users.userID)
            	WHERE
            		Aliases.alias = '%s'
            	ORDER BY Changes.changeDate DESC";
        return $DB->query(sprintf($sql, $DB->escape($alias)));
    }
}
?>