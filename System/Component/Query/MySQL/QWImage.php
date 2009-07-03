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
    public static function getPreviewAlias($id)
    {
		$sql = "SELECT 
            		relContentsPreviewImages.previewREL
            	FROM relContentsPreviewImages
            	WHERE relContentsPreviewImages.contentREL = %d";
		return BQuery::Database()->query(sprintf($sql,$id), DSQL::NUM);
    }
    
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
    public static function getRetainCounts()
    {
		$sql = "SELECT 
			 		relContentsPreviewImages.previewREL,
			 		COUNT(relContentsPreviewImages.contentREL)
            	FROM relContentsPreviewImages
            	GROUP BY relContentsPreviewImages.previewREL";
		return BQuery::Database()->query($sql, DSQL::NUM);
    }    
    
    /**
     * @return DSQLResult
     */
    public static function getRetainers($alias)
    {
        $DB = BQuery::Database();
		$sql = "SELECT 
					Aliases.alias,
					Classes.class,
					Contents.title
					FROM relContentsPreviewImages
            	LEFT JOIN Contents ON (relContentsPreviewImages.contentREL = Contents.contentID)
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
            	LEFT JOIN Classes ON (Classes.classID = Contents.type)
            	WHERE relContentsPreviewImages.previewREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')
            	ORDER BY Classes.class,Contents.title ASC";
		return $DB->query(sprintf($sql, $DB->escape($alias)), DSQL::NUM);
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
    
    public static function removePreview($contentAlias)
    {
        $sql = "DELETE FROM relContentsPreviewImages WHERE relContentsPreviewImages.contentREL = (SELECT contentREL FROM Aliases WHERE alias = '%s')";
        $DB = BQuery::Database();
        $DB->queryExecute(sprintf($sql,$DB->escape($contentAlias)));
    }
    
    public static function setPreview($contentAlias, $previewID)
    {
        $sql = "INSERT 
        			INTO 
        				relContentsPreviewImages (contentREL, previewREL) 
        			VALUES 
        				((SELECT contentREL FROM Aliases WHERE alias = '%s'), %d)
        			ON DUPLICATE KEY UPDATE 
        				previewREL = %d";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($contentAlias), $previewID, $previewID);
        $DB->queryExecute($sql);
    }
}
?>