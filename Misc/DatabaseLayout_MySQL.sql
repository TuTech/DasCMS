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
  UNIQUE INDEX object_index (`objectID` ASC) ,
  INDEX managerContentID (`objectID` ASC) ,
  INDEX fk_Contents_Managers (`Managers_managerID` ASC) ,
  INDEX fk_Contents_Languages (`Languages_languageID` ASC) ,
  INDEX fk_Contents_InternalDataTypes (`InternalDataTypes_internalDataTypeID` ASC) ,
  CONSTRAINT `fk_Contents_Managers`
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


DELIMITER //

DROP TRIGGER IF EXISTS `Capricore_test`.`T_Contents_AfterInsert` //
CREATE TRIGGER T_Contents_AfterInsert AFTER INSERT ON Contents
FOR EACH ROW 
BEGIN
    SELECT SAlias_generate(NEW.contentID);
    SELECT SContentIndex_updateChangeLog(NEW.contentID); 
END;//


DROP TRIGGER IF EXISTS `Capricore_test`.`T_Contents_AfterUpdate` //
CREATE TRIGGER T_Contents_AfterUpdate AFTER UPDATE ON Contents
FOR EACH ROW 
BEGIN
    SELECT SAlias_generate(NEW.contentID);
    SELECT SContentIndex_updateChangeLog(NEW.contentID);
END;//


DELIMITER ;

-- -----------------------------------------------------
-- Table `Capricore_test`.`Persons`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Persons` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Persons` (
  `personID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(64) NULL ,
  `email` VARCHAR(64) NULL ,
  PRIMARY KEY (`personID`) ,
  INDEX index (`name` ASC, `email` ASC) )
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
  `actions` VARCHAR(128) NULL ,
  `Contents_contentID` INT NOT NULL ,
  PRIMARY KEY (`changeID`, `Contents_contentID`) ,
  INDEX fk_ChangedBy (`changedBy` ASC) ,
  INDEX fk_Changes_Contents (`Contents_contentID` ASC) ,
  CONSTRAINT `fk_ChangedBy`
    FOREIGN KEY (`changedBy` )
    REFERENCES `Capricore_test`.`Persons` (`personID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Changes_Contents`
    FOREIGN KEY (`Contents_contentID` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
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
  `Contents_contentID` INT NOT NULL ,
  PRIMARY KEY (`aliasID`, `Contents_contentID`) ,
  UNIQUE INDEX  (`alias` ASC) ,
  INDEX fk_Aliases_Contents (`Contents_contentID` ASC) ,
  CONSTRAINT `fk_Aliases_Contents`
    FOREIGN KEY (`Contents_contentID` )
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
-- Table `Capricore_test`.`Relations`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Relations` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Relations` (
  `relationID` INT NOT NULL AUTO_INCREMENT ,
  `relation` VARCHAR(32) NOT NULL ,
  PRIMARY KEY (`relationID`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents_has_Contents`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Capricore_test`.`Contents_has_Contents` ;

CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Contents` (
  `Contents_contentID_a` INT NOT NULL ,
  `Relations_relationID` INT NOT NULL ,
  `Contents_contentID_b` INT NOT NULL ,
  PRIMARY KEY (`Contents_contentID_a`, `Contents_contentID_b`, `Relations_relationID`) ,
  INDEX fk_Contents_has_Contents_Contents_a (`Contents_contentID_a` ASC) ,
  INDEX fk_Contents_has_Contents_Contents_b (`Contents_contentID_b` ASC) ,
  INDEX fk_Contents_has_Contents_Relations () ,
  INDEX fk_Contents_has_Contents_Relation (`Relations_relationID` ASC) ,
  CONSTRAINT `fk_Contents_has_Contents_Contents_a`
    FOREIGN KEY (`Contents_contentID_a` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Contents_Contents_b`
    FOREIGN KEY (`Contents_contentID_b` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Contents_Relation`
    FOREIGN KEY (`Relations_relationID` )
    REFERENCES `Capricore_test`.`Relations` (`relationID` )
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
DROP function IF EXISTS `Capricore_test`.`Capricore_desolve` //
-- Contents.contentID to CMSID (INT -> "Manager:managerContentID")
CREATE FUNCTION `Capricore_test`.`Capricore_desolve`(_db_cid INT) RETURNS VARCHAR(97)
BEGIN
    DECLARE _manager VARCHAR(64);
    DECLARE _object VARCHAR(32);
    
    SELECT Contents.managerContentID, Managers.manager 
        FROM Contents 
        LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
        WHERE 
            Contents.contentID = _db_cid
        LIMIT 1
        INTO _object, _manager;
    RETURN CONCAT(_manager, ":", _object);
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
-- remove all entries from alias db and regenerate aliases for all ubloshed content items
CREATE PROCEDURE `Capricore_test`.`SAlias_reset`() 
BEGIN
    DECLARE _cid INT;
    DECLARE _object VARCHAR(32);
    DECLARE _manager VARCHAR(64);
    
    DECLARE _ptr CURSOR FOR
        SELECT Contents.contentID, Contents.objectID, Managers.manager
            FROM Contents
            LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
            WHERE Contents.pubDate > 0;
    
    -- remove all entries from Aliases
    DELETE FROM Aliases WHERE 1; 

    OPEN _ptr;
    -- GenerateAlias(contentID)
    LOOP 
        FETCH _ptr INTO _cid, _object, _manager;
        SELECT SAlias_generate(_cid);
    END LOOP;
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_equals` //
CREATE FUNCTION `Capricore_test`.`SAlias_equals`(_a VARCHAR(100), _b VARCHAR(100)) RETURNS INT
BEGIN
    DECLARE _aid, _bid INT;
    
    SELECT SAlias_resolveToDBCID(_a) INTO _aid;
    SELECT SAlias_resolveToDBCID(_b) INTO _bid;
    
    IF (_aid = _bid) THEN
        RETURN 1;
    ELSE
        RETURN 0;
    END IF;

END;//
DROP procedure IF EXISTS `Capricore_test`.`STag_setTags` //
CREATE PROCEDURE `Capricore_test`.`STag_setTags`(IN contentClassID VARCHAR(97), IN tagsAsCSV TEXT)
BEGIN
    DECLARE tagStr VARCHAR(64);
    DECLARE pos, cid INT;
    
    SELECT Capricore_resolve(contentClassID) INTO cid;
    DELETE FROM relContentTags WHERE contentREL = cid;
    
    WHILE NOT (tagsAsCSV = "") DO
        SET pos = LOCATE(',', tagsAsCSV);
        IF(pos = 0) THEN
            SET tagsAsCSV = "";
        ELSE
            SET tagStr = TRIM(LEFT(tagsAsCSV, pos-1));
            SET tagsAsCSV = SUBSTR(tagsAsCSV, pos+1);
            INSERT IGNORE INTO Tags (tag) VALUES (tagStr);
            INSERT INTO relContentTags(contentREL, tagREL) VALUES ((SELECT tag FROM Tags WHERE tag = tagStr LIMIT 1), cid);
        END IF;
    END WHILE;
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_currentAlias` //
-- trigger update alias on Contents.title change
 
-- trigger remove aliases if content deleted / link them to MError:404

CREATE FUNCTION `Capricore_test`.`SAlias_currentAlias`(_db_cid INT) RETURNS VARCHAR(128)
BEGIN
    DECLARE _alias VARCHAR(128);

    SELECT alias
        FROM Aliases 
            WHERE Contents_contentID = _db_cid
            AND active = 1
            LIMIT 1
            INTO _alias;
            
    IF ISNULL(_alias) THEN
       SELECT alias
            FROM Aliases 
                WHERE Contents_contentID = _db_cid
                LIMIT 1
                INTO _alias;
    END IF;
    RETURN _alias;
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_resolveToDBCID` //
CREATE FUNCTION `Capricore_test`.`SAlias_resolveToDBCID`(_alias VARCHAR(128)) RETURNS INT
BEGIN 
    DECLARE ret INT DEFAULT 0;
    SELECT Contents_contentID 
        FROM Aliases 
        WHERE alias = _alias
        INTO ret;
    
    RETURN ret;
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_resolveToCMSCID` //
CREATE FUNCTION `Capricore_test`.`SAlias_resolveToCMSCID`(_alias VARCHAR(128)) RETURNS VARCHAR(97)
BEGIN 
    RETURN Capricore_desolve(SAlias_resolveToDBCID(_alias));
END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_publicContents` //
-- Fetches public: manager, objectID, currentAlias, Title, pubDate
CREATE PROCEDURE `Capricore_test`.`Capricore_publicContents`()
BEGIN
    CALL Capricore_contentsRangePtr(1,NOW());
END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_allContents` //
-- Fetches all: manager, objectID, currentAlias, Title, pubDate
CREATE PROCEDURE `Capricore_test`.`Capricore_allContents`()
BEGIN
    CALL Capricore_contentsRangePtr(0,0);
END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_registerManagers` //
CREATE PROCEDURE `Capricore_test`.`Capricore_registerManagers`(IN _managersCSV TEXT)
BEGIN
    DECLARE _manager VARCHAR(64);
    DECLARE _pos INT;
    
    WHILE NOT (_managersCSV = "") DO
        SET _pos = LOCATE(',', _managersCSV);
        IF(_pos = 0) THEN
            SET _managersCSV = "";
        ELSE
            SET _manager = TRIM(LEFT(_managersCSV, pos-1));
            SET _managersCSV = SUBSTR(_managersCSV, pos+1);
            INSERT IGNORE INTO Managers (manager) VALUES (_manager);
        END IF;
    END WHILE;
END;//
DROP procedure IF EXISTS `Capricore_test`.`SContentIndex_simpleContentAttributes` //
-- Fetches all: manager, objectID, currentAlias, Title, pubDate, createDate, modifyDate, 
--              createdBy, modifiedBy, (? Tags), size, language, internalDataType
CREATE PROCEDURE `Capricore_test`.`SContentIndex_simpleContentAttributes`(IN _db_cid INT, IN _public_only TINYINT) 
BEGIN 

END;//
DROP procedure IF EXISTS `Capricore_test`.`SComponentIndex_listContents` //
CREATE PROCEDURE `Capricore_test`.`SComponentIndex_listContents`(IN _offset INT, IN _items INT, IN _sortDirection TINYINT, 
                                              IN _tagFilterCSV TEXT, IN _textFilter TEXT)
BEGIN 

END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_contentsRangePtr` //
CREATE PROCEDURE `Capricore_test`.`Capricore_contentsRangePtr`(IN _startTimestamp INT, IN _endTimestamp INT)
BEGIN 
    SELECT Managers.manager, Contents.contentID, Aliases.alias, 
            Contents.title, Contents.pubDate
        FROM Contents
        LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
        LEFT JOIN Aliases ON (Aliases.Contents_contentID = Contents.contentID)
        WHERE Contents.pubDate >= _startTimestamp
        AND (Contents.pubDate <= _endTimestamp OR _endTimestamp = 0)
        ORDER BY Contents.title;
END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_setTablePrefix` //
-- -------- set this ob db::init()
CREATE PROCEDURE `Capricore_test`.`Capricore_setTablePrefix`(IN _prefix VARCHAR(10))
BEGIN
    SET @cpcr_tbl_prefix = _prefix; 
END;//
DROP function IF EXISTS `Capricore_test`.`SAlias_generate` //
CREATE FUNCTION `Capricore_test`.`SAlias_generate`(contentID INT) RETURNS INT 
BEGIN
    DECLARE DatePrefix CHAR(10);
    DECLARE PubDate, c_pubDate TIMESTAMP;
    DECLARE AliasText VARCHAR(100);
    DECLARE currentAlias VARCHAR(128);
    DECLARE AliasSet INT DEFAULT 0;
    DECLARE AliasTried INT DEFAULT 0;
    DECLARE cid, foundMatches, currentAliasOwner INT;  
    DECLARE c_title VARCHAR(255);
    DECLARE _object VARCHAR(32);
    DECLARE _manager VARCHAR(64);
   
    -- get db id
    SELECT Capricore_resolve(contentID) INTO cid;
    
    -- remove active flag
    UPDATE Aliases 
        SET active = 0 
        WHERE Contents_contentID = cid;
    
    -- get title as ASCII and pubdate
    SELECT Contents.title, Contents.pubDate, Managers.manager, Contents.objectID
        FROM Contents
        LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
        WHERE Contents.contentID = cid
        INTO c_title, c_pubDate, _manager, _object;
    
    -- create basic cms id alias if it is the first or whatever
    INSERT IGNORE 
        INTO Aliases (alias, active, Contents_contentID)
        VALUES (CONCAT(_manager,':',_object), 0, cid);

    
    -- CONVERT(... USING ASCII)
    
    -- allow( a-zA-Z0-9.-_+*!$: )
    
    
    
    
    
    
    -- truncate title if too long
    IF(CHAR_LENGTH(c_title) > 100) THEN 
        SET c_title = LEFT(c_title, 100);
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
                (alias, active, Contents_contentID)
                VALUES (c_title, 1, cid);
            SET AliasSet = 1;
        
        -- yes! 
        ELSE
            -- is it ours?
            SELECT aliasID, Contents_contentID
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
    RETURN AliasSet;
END;//
DROP function IF EXISTS `Capricore_test`.`SContentIndex_isPublic` //
-- move to capricore ns? 

CREATE FUNCTION `Capricore_test`.`SContentIndex_isPublic`(_db_cid INT) RETURNS TINYINT
BEGIN 
    DECLARE _ts TIMESTAMP;
    SELECT pubDate FROM Contents WHERE contentID = _db_cid INTO _ts;
    
    RETURN IF (_ts > 0 AND _ts <= NOW(), 1, 0);
END;//
DROP function IF EXISTS `Capricore_test`.`Capricore_getTablePrefix` //
CREATE FUNCTION `Capricore_test`.`Capricore_getTablePrefix`() RETURNS VARCHAR(10)
BEGIN
    RETURN IF (ISNULL(@cpcr_tbl_prefix), "", @cpcr_tbl_prefix);
END;//
DROP procedure IF EXISTS `Capricore_test`.`SContentIndex_specificContentsRangePtr` //
CREATE PROCEDURE `Capricore_test`.`SContentIndex_specificContentsRangePtr`(IN _aliasCSV TEXT, IN _startTimestamp INT, IN _endTimestamp INT)
BEGIN 

    -- todo 
    
    -- select where in split(, alias)
    
    -- SELECT Managers.manager, Contents.contentID, Aliases.alias, 
    --         Contents.title, Contents.pubDate
    --     FROM Contents
    --     LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
    --     LEFT JOIN Aliases ON (Aliases.Contents_contentID = Contents.contentID)
    --     WHERE Contents.pubDate >= _startTimestamp
    --     AND (Contents.pubDate <= _endTimestamp OR _endTimestamp = 0)
    --     ORDER BY Contents.title;
END;//
DROP procedure IF EXISTS `Capricore_test`.`Capricore_setCurrentUser` //
CREATE PROCEDURE `Capricore_test`.`Capricore_setCurrentUser`(IN _userName VARCHAR(64))
BEGIN
    SET @cpcr_curr_usr = _userName; 
END;//
DROP function IF EXISTS `Capricore_test`.`Capricore_getCurrentUser` //
CREATE FUNCTION `Capricore_test`.`Capricore_getCurrentUser`() RETURNS VARCHAR(64)
BEGIN
    RETURN IF (ISNULL(@cpcr_curr_usr), "", @cpcr_curr_usr);
END;//
DROP procedure IF EXISTS `Capricore_test`.`BManager_createContent` //
CREATE PROCEDURE `Capricore_test`.`BManager_createContent`()
BEGIN END;//
DROP procedure IF EXISTS `Capricore_test`.`BManager_updateContent` //
CREATE PROCEDURE `Capricore_test`.`BManager_updateContent`()
BEGIN END;//
DROP procedure IF EXISTS `Capricore_test`.`BManager_deleteContent` //
CREATE PROCEDURE `Capricore_test`.`BManager_deleteContent`()
BEGIN END;//
DROP function IF EXISTS `Capricore_test`.`Capricore_resolve` //
-- CMSID to Contents.contentID ("Manager:managerContentID" -> INT)
CREATE FUNCTION `Capricore_test`.`Capricore_resolve`(contentClassID VARCHAR(97)) RETURNS INT
BEGIN
    DECLARE _manager VARCHAR(64);
    DECLARE _object VARCHAR(32);
    DECLARE _pos, _cid INT DEFAULT 0;
    
    SET _pos = LOCATE(':', contentClassID);
    IF(_pos > 0) THEN 
        SET _manager = LEFT(contentClassID, pos-1);
        SET _object = SUBSTR(contentClassID, pos+1);
        
        SELECT Contents.contentID 
            FROM Contents 
            LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
            WHERE 
                Managers.manager = _manager
                AND Contents.managerContentID = _object
            LIMIT 1
            INTO _cid;
    END IF;
    RETURN _cid;
END//
DROP function IF EXISTS `Capricore_test`.`Capricore_getPersonID` //
-- -----------------------

CREATE FUNCTION `Capricore_test`.`Capricore_getPersonID`(_name VARCHAR(64), _email VARCHAR(64)) RETURNS INT
BEGIN 

END;//
DROP function IF EXISTS `Capricore_test`.`SContentIndex_updateChangeLog` //
CREATE FUNCTION `Capricore_test`.`SContentIndex_updateChangeLog`(_db_cid INT) RETURNS TINYINT 
BEGIN 

END;//

DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
