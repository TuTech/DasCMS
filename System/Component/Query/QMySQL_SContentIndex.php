<?php
class QSContentIndex extends BQuery 
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
     * @param string $alias
     * @param string $asType
     * @return boolean
     */
    public static function exists($alias, $asType = null)
    {
        $DB = BQuery::Database();
        $join = '';
        $where = '';
        if($asType)
        {
            $join = 'LEFT JOIN Classes ON (Contents.type = Classes.classID)';
            $where = 'AND Classes.class = "'.$DB->escape($asType).'"';
        }
        $sql = "
            SELECT count(*)
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	%s
				WHERE 
					Aliases.alias = '%s'
					%s";
        
        $res = $DB->query(sprintf($sql, $join, $DB->escape($alias), $where), DSQL::NUM);
        list($num) = $res->fetch();
        $res->free();
        return $num;
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
                    Aliases.alias,
                    A2.alias
                FROM Contents
                LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
                LEFT JOIN Aliases AS A2 ON (Contents.primaryAlias = A2.aliasID)
                WHERE %s";
       return $DB->query(sprintf($sql, $sel), DSQL::NUM);
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
            		Aliases.alias AS Alias,
					Mimetypes.mimetype,
					Contents.contentID
            	FROM Contents 
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
				LEFT JOIN Classes ON (Contents.type = Classes.classID)
				LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
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
        return $DB->queryExecute(sprintf("DELETE FROM Contents WHERE contentID = %d", $dbid));
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
    
    /**
     * @param string $type class
     * @param string $title
     * @return array [dbid,alias]
     */
    public static function create($type, $title)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $sql = "INSERT INTO Contents
            	(type, title, description)
            	VALUES
            	((SELECT classID FROM Classes WHERE class = '%s'), '%s', '')";
        $DB->queryExecute(sprintf($sql, $DB->escape($type), $DB->escape($title)));
        $id = $DB->lastInsertID();
        $sql = "INSERT INTO Aliases
            	(alias, contentREL)
            	VALUES
            	(UUID(), %d)";
        $DB->queryExecute(sprintf($sql, $id));
        $aliasID = $DB->lastInsertID();
        $sql = "UPDATE Contents
				SET primaryAlias = %d,
					GUID = %d
				WHERE contentID = %d";
        $DB->queryExecute(sprintf($sql, $aliasID, $aliasID, $id));
        $sql = "INSERT INTO Changes
				(contentREL, title, size, userREL)
				VALUES
				(%d, '%s', 0, (SELECT userID FROM Users WHERE login = '%s'))";
        $DB->queryExecute(sprintf($sql, $id, $DB->escape($title), $DB->escape(PAuthentication::getUserID())));
        $sql = sprintf("SELECT alias FROM Aliases WHERE aliasID = %d", $aliasID);
        list($UUID) = $DB->query($sql, DSQL::NUM)->fetch();
        $DB->commit();
        return array($id, $UUID);
    }
}
?>