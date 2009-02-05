<?php
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
        		LEFT JOIN MimeTypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE 
    				MimeTypes.mimetype LIKE 'image/%'
    				AND (
    					MimeTypes.mimetype LIKE '%/jpeg'
    					OR MimeTypes.mimetype LIKE '%/jpg'
    					OR MimeTypes.mimetype LIKE '%/png'
    					OR MimeTypes.mimetype LIKE '%/gif'
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
        		LEFT JOIN MimeTypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE Aliases.alias = '%s'
    				AND MimeTypes.mimetype LIKE 'image/%%'
    				AND (
    					MimeTypes.mimetype LIKE '%%/jpeg'
    					OR MimeTypes.mimetype LIKE '%%/jpg'
    					OR MimeTypes.mimetype LIKE '%%/png'
    					OR MimeTypes.mimetype LIKE '%%/gif'
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