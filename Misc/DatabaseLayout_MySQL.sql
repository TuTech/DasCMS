SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `Capricore_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `Capricore_test`;

-- -----------------------------------------------------
-- Table `Capricore_test`.`Managers`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Managers` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Managers` (
  `managerID` INT NOT NULL AUTO_INCREMENT ,
  `manager` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`managerID`) ,
  UNIQUE INDEX  (`manager` ASC) )
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Languages`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Languages` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Languages` (
  `languageID` INT NOT NULL AUTO_INCREMENT ,
  `country` VARCHAR(2) NOT NULL ,
  `language` VARCHAR(3) NULL ,
  `intlTitle` VARCHAR(64) NULL ,
  `localTitle` VARCHAR(64) NULL ,
  PRIMARY KEY (`languageID`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`InternalDataTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`InternalDataTypes` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`InternalDataTypes` (
  `internalDataTypeID` INT NOT NULL AUTO_INCREMENT ,
  `dataType` VARCHAR(32) NULL ,
  PRIMARY KEY (`internalDataTypeID`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Contents` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents` (
  `contentID` INT NOT NULL AUTO_INCREMENT ,
  `objectID` VARCHAR(32) NOT NULL COMMENT 'Content identifying object for the manager class. Ususally a md5-sum.' ,
  `title` VARCHAR(255) NOT NULL ,
  `pubDate` INT NULL DEFAULT NULL ,
  `summary` TEXT NULL DEFAULT NULL ,
  `Managers_managerID` INT NULL ,
  `source` VARCHAR(255) NULL DEFAULT 'local' ,
  `description` VARCHAR(200) NULL ,
  `language` VARCHAR(45) NULL ,
  `Languages_languageID` INT NULL ,
  `InternalDataTypes_internalDataTypeID` INT NULL ,
  PRIMARY KEY (`contentID`) ,
  UNIQUE INDEX  (`objectID` ASC) ,
  INDEX managerContentID (`objectID` ASC) ,
  INDEX fk_ContentIndex_Managers (`Managers_managerID` ASC) ,
  INDEX fk_Contents_Languages (`Languages_languageID` ASC) ,
  INDEX fk_Contents_InternalDataTypes (`InternalDataTypes_internalDataTypeID` ASC) ,
  CONSTRAINT `fk_ContentIndex_Managers`
    FOREIGN KEY (`Managers_managerID` )
    REFERENCES `Capricore_test`.`Managers` (`managerID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_Languages`
    FOREIGN KEY (`Languages_languageID` )
    REFERENCES `Capricore_test`.`Languages` (`languageID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_InternalDataTypes`
    FOREIGN KEY (`InternalDataTypes_internalDataTypeID` )
    REFERENCES `Capricore_test`.`InternalDataTypes` (`internalDataTypeID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Persons`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Persons` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Persons` (
  `personID` INT NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(45) NULL ,
  `firstName` VARCHAR(30) NULL ,
  `surname` VARCHAR(45) NULL ,
  `email` VARCHAR(64) NULL ,
  PRIMARY KEY (`personID`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Changes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Changes` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Changes` (
  `changeID` INT NOT NULL AUTO_INCREMENT ,
  `title` VARCHAR(255) NOT NULL ,
  `size` INT NULL DEFAULT NULL ,
  `date` TIMESTAMP NOT NULL DEFAULT NOW() ,
  `changedBy` INT NOT NULL ,
  `ContentIndex_contentID` INT NOT NULL ,
  `actions` VARCHAR(128) NULL ,
  PRIMARY KEY (`changeID`, `ContentIndex_contentID`) ,
  INDEX fk_Changes_ContentIndex (`ContentIndex_contentID` ASC) ,
  INDEX fk_ChangedBy (`changedBy` ASC) ,
  CONSTRAINT `fk_Changes_ContentIndex`
    FOREIGN KEY (`ContentIndex_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_ChangedBy`
    FOREIGN KEY (`changedBy` )
    REFERENCES `Capricore_test`.`Persons` (`personID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Tags` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Tags` (
  `tagID` INT NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(64) NOT NULL ,
  `blocked` INT NULL DEFAULT NULL ,
  PRIMARY KEY (`tagID`) ,
  UNIQUE INDEX  (`tag` ASC) )
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Aliases`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Aliases` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Aliases` (
  `aliasID` INT NOT NULL AUTO_INCREMENT ,
  `alias` VARCHAR(128) NOT NULL ,
  `active` INT NULL DEFAULT NULL ,
  `ContentIndex_contentID` INT NULL ,
  PRIMARY KEY (`aliasID`) ,
  UNIQUE INDEX  (`alias` ASC) ,
  INDEX fk_Aliases_ContentIndex (`ContentIndex_contentID` ASC) ,
  CONSTRAINT `fk_Aliases_ContentIndex`
    FOREIGN KEY (`ContentIndex_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents_has_Tags`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Contents_has_Tags` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Tags` (
  `Contents_contentID` INT NOT NULL ,
  `Tags_tagID` INT NOT NULL ,
  PRIMARY KEY (`Contents_contentID`, `Tags_tagID`) ,
  INDEX fk_Contents_has_Tags_Contents (`Contents_contentID` ASC) ,
  INDEX fk_Contents_has_Tags_Tags (`Tags_tagID` ASC) ,
  CONSTRAINT `fk_Contents_has_Tags_Contents`
    FOREIGN KEY (`Contents_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Tags_Tags`
    FOREIGN KEY (`Tags_tagID` )
    REFERENCES `Capricore_test`.`Tags` (`tagID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `Capricore_test`.`Roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Roles` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Roles` (
  `roleID` INT NOT NULL AUTO_INCREMENT ,
  `role` VARCHAR(45) NULL ,
  PRIMARY KEY (`roleID`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents_has_Contents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Contents_has_Contents` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Contents` (
  `Contents_contentID` INT NOT NULL ,
  `relationType` SET('equals', 'references', 'depends', 'owns') NULL ,
  `Contents_contentID1` INT NOT NULL ,
  PRIMARY KEY (`Contents_contentID`, `Contents_contentID1`) ,
  INDEX fk_Contents_has_Contents_Contents (`Contents_contentID` ASC) ,
  INDEX fk_Contents_has_Contents_Contents1 (`Contents_contentID1` ASC) ,
  CONSTRAINT `fk_Contents_has_Contents_Contents`
    FOREIGN KEY (`Contents_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Contents_Contents1`
    FOREIGN KEY (`Contents_contentID1` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `Capricore_test`.`Users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Users` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Users` (
  `userID` INT NOT NULL AUTO_INCREMENT ,
  `loginName` VARCHAR(32) NULL ,
  `passwordHash` VARCHAR(32) NULL ,
  `Digest_HA1` VARCHAR(32) NULL ,
  `Digest_HA2` VARCHAR(32) NULL ,
  `Contents_contentID` INT NULL ,
  `Persons_personID` INT NULL ,
  PRIMARY KEY (`userID`) ,
  INDEX fk_Users_Contents (`Contents_contentID` ASC) ,
  INDEX fk_Users_Persons (`Persons_personID` ASC) ,
  CONSTRAINT `fk_Users_Contents`
    FOREIGN KEY (`Contents_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Users_Persons`
    FOREIGN KEY (`Persons_personID` )
    REFERENCES `Capricore_test`.`Persons` (`personID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents_has_Persons_with_Roles`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Contents_has_Persons_with_Roles` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Persons_with_Roles` (
  `Contents_contentID` INT NOT NULL ,
  `Persons_personID` INT NOT NULL ,
  `Roles_roleID` INT NOT NULL ,
  INDEX Contents (`Contents_contentID` ASC) ,
  INDEX Persons (`Persons_personID` ASC) ,
  INDEX Roles (`Roles_roleID` ASC) ,
  PRIMARY KEY (`Contents_contentID`, `Persons_personID`, `Roles_roleID`) ,
  CONSTRAINT `Contents`
    FOREIGN KEY (`Contents_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `Persons`
    FOREIGN KEY (`Persons_personID` )
    REFERENCES `Capricore_test`.`Persons` (`personID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `Roles`
    FOREIGN KEY (`Roles_roleID` )
    REFERENCES `Capricore_test`.`Roles` (`roleID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


DELIMITER //
DROP function IF EXISTS `Capricore_test`.`Capricore_resolve` //
-- CMSID to ContentIndex.contentID ("Manager:managerContentID" -> INT)
CREATE FUNCTION `Capricore_test`.`Capricore_resolve`(contentClassID VARCHAR(97)) RETURNS INT
BEGIN
    DECLARE ManagerStr VARCHAR(64);
    DECLARE managerContentIDStr VARCHAR(32);
    DECLARE pos, cid INT DEFAULT 0;
    
    SET pos = LOCATE(':', contentClassID);
    IF(pos > 0) THEN 
        SET ManagerStr = LEFT(contentClassID, pos-1);
        SET managerContentIDStr = SUBSTR(contentClassID, pos+1);
        
        SELECT ContentIndex.contentID 
            FROM ContentIndex 
            LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID)
            WHERE 
                Managers.manager = ManagerStr
                AND ContentIndex.managerContentID = managerContentIDStr
            LIMIT 1
            INTO cid;
    END IF;
    RETURN cid;
END//
DROP function IF EXISTS `Capricore_test`.`Capricore_desolve` //
-- ContentIndex.contentID to CMSID (INT -> "Manager:managerContentID")
CREATE FUNCTION `Capricore_test`.`Capricore_desolve`(DBContentID INT) RETURNS VARCHAR(97)
BEGIN
    DECLARE ManagerStr VARCHAR(64);
    DECLARE managerContentIDStr VARCHAR(32);
    
    SELECT ContentIndex.managerContentID, Managers.manager 
        FROM ContentIndex 
        LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID)
        WHERE 
            ContentIndex.contentID = DBContentID
        LIMIT 1
        INTO managerContentIDStr, ManagerStr;
    RETURN CONCAT(ManagerStr, ":", managerContentIDStr);
END//
DROP procedure IF EXISTS `Capricore_test`.`STag_clean` //
CREATE PROCEDURE `Capricore_test`.`STag_clean`(IN contentClassID VARCHAR(97))
BEGIN
    DECLARE cid INT;    
    SELECT Capricore_resolve(contentClassID) INTO cid;
    
    IF(cid > 0) THEN 
        DELETE FROM relContentTags WHERE contentREL = cid;
    END IF;
END;//
DROP procedure IF EXISTS `Capricore_test`.`STag_link` //
CREATE PROCEDURE `Capricore_test`.`STag_link`(IN contentClassID VARCHAR(97), IN tagsAsCSV TEXT)
BEGIN
    DECLARE tagStr VARCHAR(64);
    DECLARE pos, cid INT;
    
    SELECT Capricore_resolve(contentClassID) INTO cid;
    DELETE FROM relContentTags WHERE contentREL = cid;
    
    tagLoop: WHILE NOT (tagsAsCSV = "") DO
        SET pos = LOCATE(',', tagsAsCSV);
        IF(pos = 0) THEN
            SET tagsAsCSV = "";
        ELSE
            SET tagStr = TRIM(LEFT(tagsAsCSV, pos-1));
            SET tagsAsCSV = SUBSTR(tagsAsCSV, pos+1);
            INSERT IGNORE INTO Tags (tag) VALUES (tagStr);
            INSERT INTO relContentTags(contentREL, tagREL) VALUES ((SELECT tag FROM Tags WHERE tag = tagStr LIMIT 1), cid);
        END IF;
    END WHILE tagLoop;
END;//
DROP procedure IF EXISTS `Capricore_test`.`STag_getTags` //
CREATE PROCEDURE `Capricore_test`.`STag_getTags`(IN contentClassID VARCHAR(97))
BEGIN
    DECLARE cid INT;
    SELECT Capricore_resolve(contentClassID) INTO cid;
    
    SELECT Tags.tag FROM Tags 
        LEFT JOIN relContentTags ON (relContentTags.tagREL = Tags.tagID) 
        WHERE 
            relContentTags.contentREL = cid 
        ORDER BY Tags.tag;
END;//
DROP procedure IF EXISTS `Capricore_test`.`SAlias_reset` //
-- trigger update alias on contentindex.title change
 
 -- trigger remove aliases if content deleted / link them to MError:404
 
 -- equals
 
 -- currentAlias
 
CREATE PROCEDURE `Capricore_test`.`SAlias_reset`() 
BEGIN
 -- remove all entries from Aliases
 -- reset alias counter
 -- select all contents where pubDate > 0
 -- GenerateAlias(contentID)
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_Generate` //
CREATE FUNCTION `Capricore_test`.`SAlias_Generate`(contentID INT) RETURNS INT 
BEGIN
    DECLARE DatePrefix CHAR(10);
    DECLARE PubDate, c_pubDate TIMESTAMP;
    DECLARE AliasText VARCHAR(80);
    DECLARE currentAlias VARCHAR(100);
    DECLARE AliasSet INT DEFAULT 0;
    DECLARE AliasTried INT DEFAULT 0;
    DECLARE cid, foundMatches, currentAliasOwner INT;  
    DECLARE c_title VARCHAR(255);
    
    -- get db id
    SELECT Capricore_resolve(contentID) INTO cid;
    
    -- remove active flag
    UPDATE Aliases 
        SET active = 0 
        WHERE ContentIndex_contentID = cid;
    
    -- get title as ASCII and pubdate
    SELECT CONVERT(title USING ascii), pubDate
        FROM ContentIndex
        WHERE contentID = cid
        INTO c_title, c_pubDate;
    
    -- truncate title if too long
    IF(CHAR_LENGTH(c_title) > 84) THEN 
        SET c_title = LEFT(c_title, 84);
    END IF;
    
    -- add a date prefix to the alias
    SELECT CONCAT(DATE_FORMAT(PubDate, '%Y-%m-%d-'), c_title) INTO c_title;
    
    -- select alias
    WHILE (AliasSet = 0) DO
        -- is the ascii verstion of title already an alias?
        SELECT COUNT(aliasID)
            FROM Aliases
            WHERE alias = c_title
            INTO foundMatches;
        
        -- no! use it
        IF (foundMatches = 0) THEN
            INSERT INTO Aliases
                (alias, active, ContentIndex_contentID)
                VALUES (c_title, 1, cid);
            SET AliasSet = 1;
        
        -- yes! 
        ELSE
            -- is it ours?
            SELECT aliasID, ContentIndex_contentID
                FROM Aliases 
                WHERE alias = c_title
                INTO currentAlias, currentAliasOwner;
            
            -- yes!
            IF (currentAliasOwner = cid) THEN
                UPDATE Aliases
                    SET active = 1
                    WHERE aliasID = currentAlias;
                SET AliasSet = 1;
                
            -- no! guess new one
            ELSE
                SET AliasTried = AliasTried + 1;
                SELECT CONCAT(c_title, '-', AliasTried) INTO c_title;
                
            END IF;
            
        END IF;
        
    END WHILE;
  
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_resolve_to_DB_CID` //
CREATE FUNCTION `Capricore_test`.`SAlias_resolve_to_DB_CID`(_alias VARCHAR(100)) RETURNS INT
BEGIN 
    DECLARE ret INT DEFAULT 0;
    SELECT ContentIndex_contentID 
        FROM Aliases 
        WHERE alias = _alias
        INTO ret;
    
    RETURN ret;
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_resolve_to_CMS_CID` //
CREATE FUNCTION `Capricore_test`.`SAlias_resolve_to_CMS_CID`(_alias VARCHAR(100)) RETURNS VARCHAR(97)
BEGIN 
    RETURN Capricore_desolve(SAlias_resolve_to_DB_CID(_alias));
END;//

DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
