<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-02-02
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QWImage extends BQuery 
{
    
    /**
     * @return DSQLResult
     */
    public static function getPreviewContents()
    {
        $DB = BQuery::Database();
        $sql = 
        	"SELECT DISTINCT Aliases.alias, Contents.title FROM Contents 
        		LEFT JOIN Aliases ON (Aliases.aliasID = Contents.GUID)
        		LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE 
    				Mimetypes.mimetype LIKE 'image/%'
    				AND (
    					Mimetypes.mimetype LIKE '%/jpeg'
    					OR Mimetypes.mimetype LIKE '%/jpg'
    					OR Mimetypes.mimetype LIKE '%/png'
    					OR Mimetypes.mimetype LIKE '%/gif'
					)
				ORDER BY Contents.title ASC";
		return $DB->query($sql, DSQL::NUM);
    }
    
    
    /**
     * @return DSQLResult
     */
    public static function idToAlias($id)
    {
        $sql = "SELECT alias FROM Aliases WHERE contentREL = %d LIMIT 1";
        return BQuery::Database()->query(sprintf($sql,$id), DSQL::NUM);
    }

    /**
     * @return DSQLResult
     */
    public static function aliasToId($alias)
    {
        $sql = "SELECT contentREL FROM Aliases WHERE alias = '%s' LIMIT 1";
        return BQuery::Database()->query(sprintf($sql,BQuery::Database()->escape($alias)), DSQL::NUM);
    }

    /**
     * fetch content id for alias if it is possible to use as preview
     * @return DSQLResult
     */
    public static function getPreviewId($alias)
    {
        $DB = BQuery::Database();
        $sql = sprintf(
        	"SELECT Aliases.contentREL FROM Aliases 
        		LEFT JOIN Contents ON (Aliases.contentREL = Contents.contentID)
        		LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE Aliases.alias = '%s'
    				AND Mimetypes.mimetype LIKE 'image/%%'
    				AND (
    					Mimetypes.mimetype LIKE '%%/jpeg'
    					OR Mimetypes.mimetype LIKE '%%/jpg'
    					OR Mimetypes.mimetype LIKE '%%/png'
    					OR Mimetypes.mimetype LIKE '%%/gif'
					)"
			,$DB->escape($alias)			
		);
		return $DB->query($sql, DSQL::NUM);
    }
    
}
?>