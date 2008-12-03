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
            		description = '%s',
					size = %d
            	WHERE
            		contentID = %d";
        $DB->queryExecute(sprintf(
                $sql, 
                $DB->escape($title), 
                $DB->escape($pubDate > 0 ? date('Y-m-d H:i:s', $pubDate) : '0000-00-00 00:00:00'), 
                $DB->escape($description),
                $size,
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
    	    
    public static function setMimeType($alias, $mime)
    {
        $DB = DSQL::alloc()->init();
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
            		Contents.description,
					Mimetypes.mimetype,
					Contents.size,
					GUIDs.alias
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
        list($id, $ttl, $pd, $desc, $mt, $sz, $guid) = $res->fetch();
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
            $id, $ttl, $pd, $desc, $tags, $mt, $sz, $guid
        );
    }
}
?>