<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-16
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QBContent extends BQuery 
{
    /**
     * @param int $contentID
     * @return DSQLResult
     */
    public static function getAccessStats($contentID)
    {
        $sql = "SELECT 
        				MIN(accessTime) AS 'firstAccess',
        				MAX(accessTime) AS 'lastAccess',
        				COUNT(*) AS 'accessCount',
        				FLOOR(TIME_TO_SEC(TIMEDIFF(NOW(), MIN(accessTime)))/(COUNT(*)+1)) AS 'SecondsBetweenAccesses'
					FROM AccessLog
        				WHERE contentREL = %d";
        return BQuery::Database()->query(sprintf($sql, $contentID), DSQL::NUM);
    }
    
    public static function removeViewBinding($contentId)
    {
        $sql = 
            "DELETE 
            	FROM relContentsTargetViews 
            	WHERE contentREL = %d";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf($sql, $contentId));
    }
    
    public static function getViewBinding($contentId)
    {
        $sql = 
            "SELECT viewName
            	FROM relContentsTargetViews 
            	LEFT JOIN SporeViews ON (relContentsTargetViews.viewREL = SporeViews.viewID)
            	WHERE relContentsTargetViews.contentREL = %d";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $contentId), DSQL::NUM);
    }
    
    public static function setViewBinding($contentId, $viewName)
    {
        $DB = BQuery::Database();
        $sql = "INSERT INTO relContentsTargetViews (contentREL, viewREL) 
        			(SELECT '%d', viewID FROM SporeViews WHERE viewName = '%s')
        			ON DUPLICATE KEY 
        				UPDATE viewREL =  (SELECT viewID FROM SporeViews WHERE viewName = '%s' LIMIT 1)";
        $viewName = $DB->escape($viewName);
        $DB->queryExecute(sprintf($sql, $contentId, $viewName, $viewName, $contentId));
    }
    
    
    /**
     * @param int $contentID
     * @return DSQLResult
     */
    public static function getAllowSearchIndexing($contentID)
    {
        $sql = "SELECT 
        				1 AS 'yes'
					FROM Contents
        				WHERE 
        					contentID = %d
        					AND allowSearchIndexing = 'Y'";
        $sql = sprintf($sql, $contentID);
        return BQuery::Database()->query($sql, DSQL::NUM);
    }
       
    public static function setAllowSearchIndexing($contentID, $allowed)
    {
        $sql = "UPDATE Contents
        			SET 
        				allowSearchIndexing = '%s'
    				WHERE 
    					contentID = %d";
        return BQuery::Database()->queryExecute(sprintf($sql, empty($allowed) ? 'N' : 'Y', $contentID), DSQL::NUM);
    }
    
    
    public static function logAccess($contentID, $countyCodeHash, $ipAddressHash)
    {
        $sql = 
        	"INSERT IGNORE INTO AccessLog
				(contentREL, countyCodeHash, ipAddressHash)
				VALUES
				(%d, %d, %d)";
        $sql = sprintf($sql, $contentID, $countyCodeHash, $ipAddressHash);
        return BQuery::Database()->queryExecute($sql);
    }
    
    public static function saveMetaData($id, $title, $pubDate, $description, $size, $subtitle)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $sql = "
            UPDATE Contents 
            	SET 
            		title = '%s',
            		pubDate = '%s',
            		description = '%s',
					size = %d,
					subtitle = '%s'
            	WHERE
            		contentID = %d";
        $DB->queryExecute(sprintf(
                $sql, 
                $DB->escape($title), 
                $DB->escape($pubDate > 0 ? date('Y-m-d H:i:s', $pubDate) : '0000-00-00 00:00:00'), 
                $DB->escape($description),
                $size,
                $DB->escape($subtitle),
                $id)
            , DSQL::NUM);
        self::logChange($id, $title, $size);
        $DB->commit();
    }

    private static function logChange($cid, $title, $size)
    {
        $DB = BQuery::Database();
        //set username for change
        $sql = sprintf("SELECT changedByUserID FROM ChangedByUsers WHERE login = LEFT('%s', 32) LIMIT 1", $DB->escape(PAuthentication::getUserID()));
        $res = $DB->query($sql, DSQL::NUM);
        if($res->getRowCount() == 0)
        {
            $sql = sprintf("INSERT INTO ChangedByUsers (login) VALUES (LEFT('%s', 32))", $DB->escape(PAuthentication::getUserID()));
            if($DB->queryExecute($sql))
            {
                $rel = 'LAST_INSERT_ID()';
            }
            else
            {
                $rel = 'NULL';
            }
        }
        else
        {
            list($rel) = $res->fetch();
        }
        $res->free();
        //insert change
        $sql = 
        	"INSERT INTO Changes
				(contentREL, title, size, userREL)
				VALUES
				(%d, '%s', %d, %s)";
        $sql = sprintf($sql, $cid, $DB->escape($title), $size, $rel);
        $DB->queryExecute($sql);
    }
    
    public static function deleteContent($alias)
    {
        $sql = 
            "DELETE 
            	FROM Contents 
            	WHERE Contents.contentID = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf($sql, $DB->escape($alias)));
    }
    
    public static function getClass($alias)
    {
        $sql = "
            SELECT Classes.class 
            	FROM Contents
            	LEFT JOIN Classes ON (Contents.type = Classes.classID)
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	WHERE Aliases.alias = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException($alias);
        }
        list($class) = $res->fetch();
        return $class;
    }
    	    
    public static function setMimeType($alias, $mime)
    {
        $DB = DSQL::getSharedInstance();
        $sql = "INSERT IGNORE INTO Mimetypes (mimetype) VALUES ('%s')";
        $DB->queryExecute(sprintf($sql, $DB->escape($mime)));
        $sql = 
            "UPDATE Contents 
				SET mimetypeREL = (SELECT mimetypeID from Mimetypes WHERE mimetype = '%s')
				WHERE contentID = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
		$DB->queryExecute(sprintf($sql, $DB->escape($mime), $DB->escape($alias)));
    }
    
    public static function getAdditionalMetaData($alias)
    {
        $sql = "
            SELECT 
            		Changes.changeDate,
            		Changes.size,
            		IF(ISNULL(ChangedByUsers.login), '-', ChangedByUsers.login) as user
            	FROM Changes 
            	LEFT JOIN ChangedByUsers ON (Changes.userREL = ChangedByUsers.changedByUserID)
            	LEFT JOIN Aliases ON (Changes.contentREL = Aliases.contentREL)
            	WHERE 
            		Aliases.alias = '%s'
            	ORDER BY Changes.changeDate %s
            	LIMIT 1";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($alias), 'DESC'), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            $md = date('Y-m-d H:i:s'); 
            $sz = 0; 
            $mb = '-';
        }
        else
        {
            list($md, $sz, $mb) = $res->fetch();
        }
        $res->free();
        $res = $DB->query(sprintf($sql, $DB->escape($alias), 'ASC'), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            $cd = date('Y-m-d H:i:s'); 
            $cb = '-';
        }
        else
        {
            list($cd, $null, $cb) = $res->fetch();
        }
        $res->free();
        return array($cb, $cd, $mb, $md, $sz);
    }
    /**
     * 
     * @param $alias
     * @param $asType
     * @return DSQLResult
     */
    public static function exists($alias, $asType = null)
    {
        $DB = BQuery::Database();
        $type = '';
        if($asType != null)
        {
            $type = " AND Contents.type = (SELECT classID FROM Classes WHERE class = '%s')";
            $type = sprintf($type, $DB->escape($asType));
        }
        $sql = "SELECT Contents.contentID 
				FROM Contents
				LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
				WHERE Aliases.alias = '%s'%s";
        return $DB->query(sprintf($sql, $DB->escape($alias), $type), DSQL::NUM);
    }
    
    public static function getBasicMetaData($alias)
    {
        $sql = "
            SELECT 
            		Contents.contentID,
            		Contents.title,
            		Contents.pubDate,
            		Contents.description,
					Mimetypes.mimetype,
					Contents.size,
					GUIDs.alias,
					Contents.subtitle
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            	LEFT JOIN Aliases AS GUIDs ON (Contents.GUID = GUIDs.aliasID)
				LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
            		WHERE Aliases.alias = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException();
        }
        list($id, $ttl, $pd, $desc, $mt, $sz, $guid, $sttl) = $res->fetch();
        $sql = "
            SELECT tag 
            	FROM Tags 
            	LEFT JOIN relContentsTags ON (Tags.tagID = relContentsTags.tagREL)
            	WHERE relContentsTags.contentREL = %d";
        $res = $DB->query(sprintf($sql, $id), DSQL::NUM);
        $tags = array();
        while ($row = $res->fetch()) 
        {
        	$tags[] = $row[0];
        }
        return array(
            $id, $ttl, $pd, $desc, $tags, $mt, $sz, $guid, $sttl
        );
    }
    
    /**
     * @param string $type class
     * @param string $title
     * @return array [dbid,alias]
     */
    public static function create($type, $title)
    {
        try
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
            self::logChange($id, $title, 0);
            $sql = sprintf("SELECT alias FROM Aliases WHERE aliasID = %d", $aliasID);
            list($UUID) = $DB->query($sql, DSQL::NUM)->fetch();
            $DB->commit();
            return array($id, $UUID);
        }
	    catch (XDatabaseException $dbe)
	    {
	        $dbe->rollback();
	        throw $dbe;
	    }
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
}
?>