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
}
?>