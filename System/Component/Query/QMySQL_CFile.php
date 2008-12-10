<?php
class QCFile extends BQuery 
{
    /**
     * @param int $contentID
     * @param string $originalFileName
     * @param string $suffix
     * @param string $md5Sum
     * @param int|null $folderID
     * @return int
     */
    public static function saveFileMeta($contentID, $originalFileName, $suffix, $md5Sum, $folderID = null)
    {
        $DB = BQuery::Database();
        $sql = 
            "INSERT INTO FileAttributes 
				(contentREL, folderREL, originalFileName, suffix, md5sum)
				VALUES
				(
					%d, %s, '%s', '%s', '%s' 
				)";
        $sql = sprintf(
            $sql, 
            $contentID, 
            ($folderID == null ? 'NULL' : $DB->escape($folderID)),
            $DB->escape($originalFileName),
            $DB->escape($suffix),
            $DB->escape($md5Sum)
        );
        return $DB->queryExecute($sql);
    }
    /**
     * Folders.name, 
     * FileAttributes.originalFileName, 
     * FileAttributes.suffix, 
     * FileAttributes.md5sum, 
     * FileAttributes.folderREL
     * @return DSQLResult
     */
    public static function getMetaData($contentID)
    {
        $sql = 
            "SELECT 
				Folders.name, FileAttributes.originalFileName, FileAttributes.suffix, FileAttributes.md5sum, FileAttributes.folderREL
				FROM FileAttributes
					LEFT JOIN Folders ON (FileAttributes.folderREL = Folders.folderID)
				WHERE FileAttributes.contentREL = %d";
        return  BQuery::Database()->query(sprintf($sql, $contentID),  DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getChildFolders($parentFolder = null)
    {
        $DB = BQuery::Database();
        if($parentFolder == null)
        {
            $sql = "SELECT folderID, name FROM Folders WHERE ISNULL(parentFolderREL)";
        }
        else
        {
            $sql = "SELECT folderID, name FROM Folders WHERE parentFolderREL == %d";
        }
        return  BQuery::Database()->query(sprintf($sql, $parentFolder),  DSQL::NUM);
    }
    /**
     * @return int affected rows
     */
    public static function deleteFolder($folderID)
    {
        $sql = sprintf("DELETE FROM Folders WHERE folderID = %d", $folderID);
        return $DB->queryExecute($sql);
    }
    
    /**
     * @return int affected rows
     */
    public static function createFolder($named, $parentFolderID = null)
    {
        try
        {
            $DB = BQuery::Database();
            $sql = "INSERT INTO Folders (parentFolderREL, name) VALUES (%s, '%s')";
            $sql = sprintf(
                $sql,
                ($parentFolderID == null ? 'NULL' : $DB->escape($parentFolderID)),
                $DB->escape($named)
            );
            return $DB->queryExecute($sql);
        }
        catch (XDatabaseException$e)
        {
            return 0;
        }
    }

    public static function getFolderContents($folderID)
    {
        $sql = 
            "SELECT Contents.contentID, Aliases.alias, Contents.title, Contents.size, Mimetypes.mimetype  
				FROM FileAttributes 
					LEFT JOIN Contents ON (FileAttributes.contentREL = Contents.contentID)
					LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID) 
					LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				WHERE ".
                (($folderID == null) 
                    ? 'ISNULL(FileAttributes.folderREL)' 
					: 'FileAttributes.folderREL = %d');
        return BQuery::Database()->query(sprintf($sql, $folderID), DSQL::NUM);
    }
    
    
    //getFolderContents
    //getFolderID($c)
	//getFolderPath($f)
	//getFolderName($f)
	//getChildrenOf($f)
	//moveFile($c, $f)
}
?>