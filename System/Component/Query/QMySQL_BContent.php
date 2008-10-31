<?php
class QBContent extends BQuery 
{
    
    public static function saveMetaData($id, $title, $pubDate, $description, $size)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $sql = "
            UPDATE Contents 
            	SET 
            		title = '%s',
            		pubDate = '%s',
            		description = '%s'
            	WHERE
            		contentID = %d";
        $DB->queryExecute(sprintf(
                $sql, 
                $DB->escape($title), 
                $DB->escape($pubDate > 0 ? date('Y-m-d H:i:s', $pubDate) : '0000-00-00 00:00:00'), 
                $DB->escape($description),
                $id)
            , DSQL::NUM);
        $sql = "
			INSERT INTO Changes
				(contentREL, title, size, userREL)
				VALUES
				(%d, '%s', %d, (SELECT userID FROM Users WHERE login = '%s'))";
        $sql = sprintf($sql, $id, $DB->escape($title), $size, $DB->escape(PAuthentication::getUserID()));
        $DB->queryExecute($sql);
        $DB->commit();
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
    	    
    public static function getAdditionalMetaData($alias)
    {
        $sql = "
            SELECT 
            		Changes.changeDate,
            		Changes.size,
            		Users.login
            	FROM Changes 
            	LEFT JOIN Users ON (Changes.userREL = Users.userID)
            	LEFT JOIN Aliases ON (Changes.contentREL = Aliases.contentREL)
            	WHERE 
            		Aliases.alias = '%s'
            	ORDER BY Changes.changeDate %s
            	LIMIT 1";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($alias), 'DESC'), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException();
        }
        list($md, $sz, $mb) = $res->fetch();
        $res->free();
        $res = $DB->query(sprintf($sql, $DB->escape($alias), 'ASC'), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException();
        }
        list($cd, $null, $cb) = $res->fetch();
        $res->free();
        return array($cb, $cd, $mb, $md, $sz);
    }
    
    public static function getBasicMetaData($alias)
    {
        $sql = "
            SELECT 
            		Contents.contentID,
            		Contents.title,
            		Contents.pubDate,
            		Contents.description
            	FROM Contents
            	LEFT JOIN Aliases ON (Contents.contentID = Aliases.contentREL)
            		WHERE Aliases.alias = '%s'";
        $DB = BQuery::Database();
        $res = $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
        if($res->getRowCount() != 1)
        {
            throw new XUndefinedIndexException();
        }
        list($id, $ttl, $pd, $desc) = $res->fetch();
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
            $id, $ttl, $pd, $desc, $tags
        );
    }
}
?>