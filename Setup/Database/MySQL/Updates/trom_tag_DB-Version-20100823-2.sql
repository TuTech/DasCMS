-- removing unused folder feature of CFile
ALTER TABLE  FileAttributes
	DROP FOREIGN KEY  fileattributes_ibfk_2;
ALTER TABLE FileAttributes
	DROP INDEX folderREL,
	DROP COLUMN folderREL;
DROP TABLE Folders;
