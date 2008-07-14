SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `Capricore_test` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ;
USE `Capricore_test`;

-- -----------------------------------------------------
-- Table `Capricore_test`.`Managers`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Managers` (
  `managerID` INT NOT NULL AUTO_INCREMENT ,
  `manager` VARCHAR(64) NOT NULL ,
  PRIMARY KEY (`managerID`) ,
  UNIQUE INDEX UNIQUE_manager (`manager` ASC) )
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Languages`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Languages` (
  `languageID` INT NOT NULL AUTO_INCREMENT ,
  `country` VARCHAR(2) NOT NULL ,
  `language` VARCHAR(3) NULL ,
  `intlTitle` VARCHAR(64) NULL ,
  `localTitle` VARCHAR(64) NULL ,
  PRIMARY KEY (`languageID`) ,
  UNIQUE INDEX UNIQUE_language_country (`language` ASC, `country` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`InternalDataTypes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`InternalDataTypes` (
  `internalDataTypeID` INT NOT NULL AUTO_INCREMENT ,
  `dataType` VARCHAR(32) NULL ,
  PRIMARY KEY (`internalDataTypeID`) ,
  UNIQUE INDEX UNIQUE_dataType (`dataType` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents` (
  `contentID` INT NOT NULL AUTO_INCREMENT ,
  `objectID` VARCHAR(32) NOT NULL COMMENT 'Content identifying object for the manager class. Ususally a md5-sum.' ,
  `title` VARCHAR(255) NOT NULL ,
  `pubDate` DATETIME NULL DEFAULT NULL ,
  `summary` TEXT NULL DEFAULT NULL ,
  `Managers_managerID` INT NULL ,
  `source` VARCHAR(255) NULL DEFAULT 'local' ,
  `description` VARCHAR(200) NULL ,
  `language` VARCHAR(45) NULL ,
  `Languages_languageID` INT NULL ,
  `InternalDataTypes_internalDataTypeID` INT NULL ,
  PRIMARY KEY (`contentID`) ,
  UNIQUE INDEX UNIQUE_manager_object (`Managers_managerID` ASC, `objectID` ASC) ,
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
CREATE TRIGGER T_Contents_AfterInsert AFTER INSERT ON Contents
FOR EACH ROW 
BEGIN
    CALL SAlias_generate(NEW.contentID);
    CALL SContentIndex_updateChangeLog(NEW.contentID); 
END;//

CREATE TRIGGER T_Contents_AfterUpdate AFTER UPDATE ON Contents
FOR EACH ROW 
BEGIN
    CALL SAlias_generate(NEW.contentID);
    CALL SContentIndex_updateChangeLog(NEW.contentID);
END;//


DELIMITER ;

-- -----------------------------------------------------
-- Table `Capricore_test`.`Persons`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Persons` (
  `personID` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(64) NULL ,
  `email` VARCHAR(64) NULL ,
  PRIMARY KEY (`personID`) ,
  UNIQUE INDEX UNIQUE_name_email (`name` ASC, `email` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Changes`
-- -----------------------------------------------------
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
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Tags` (
  `tagID` INT NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(64) NOT NULL ,
  `blocked` INT NULL DEFAULT NULL ,
  PRIMARY KEY (`tagID`) ,
  UNIQUE INDEX UNIQUE_tag (`tag` ASC) )
ENGINE = MYISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Aliases`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Aliases` (
  `aliasID` INT NOT NULL AUTO_INCREMENT ,
  `alias` VARCHAR(128) NOT NULL ,
  `active` INT NULL DEFAULT NULL ,
  `Contents_contentID` INT NOT NULL ,
  PRIMARY KEY (`aliasID`, `Contents_contentID`) ,
  UNIQUE INDEX UNIQUE_alias (`alias` ASC) ,
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
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Tags` (
  `Contents_contentID` INT NOT NULL ,
  `Tags_tagID` INT NOT NULL ,
  PRIMARY KEY (`Contents_contentID`, `Tags_tagID`) ,
  INDEX fk_Contents_has_Tags_Contents (`Contents_contentID` ASC) ,
  INDEX fk_Contents_has_Tags_Tags (`Tags_tagID` ASC) ,
  UNIQUE INDEX UNIQUE_ct (`Contents_contentID` ASC, `Tags_tagID` ASC) ,
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
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Roles` (
  `roleID` INT NOT NULL AUTO_INCREMENT ,
  `role` VARCHAR(45) NULL ,
  PRIMARY KEY (`roleID`) ,
  UNIQUE INDEX UNIQUE_role (`role` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Users`
-- -----------------------------------------------------
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
  UNIQUE INDEX UNIQUE_loginName (`loginName` ASC) ,
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
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Relations`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Relations` (
  `relationID` INT NOT NULL AUTO_INCREMENT ,
  `relation` VARCHAR(32) NOT NULL ,
  PRIMARY KEY (`relationID`) ,
  UNIQUE INDEX UNIQUE_relation (`relation` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `Capricore_test`.`Contents_has_Contents`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `Capricore_test`.`Contents_has_Contents` (
  `Contents_contentID_a` INT NOT NULL ,
  `Contents_contentID_b` INT NOT NULL ,
  `Relations_relationID` INT NOT NULL ,
  PRIMARY KEY (`Contents_contentID_a`, `Contents_contentID_b`, `Relations_relationID`) ,
  INDEX fk_Contents_has_Contents_Contents (`Contents_contentID_a` ASC) ,
  INDEX fk_Contents_has_Contents_Contents1 (`Contents_contentID_b` ASC) ,
  INDEX fk_Contents_has_Contents_Relations (`Relations_relationID` ASC) ,
  UNIQUE INDEX UNIQUE_ccr (`Contents_contentID_a` ASC, `Contents_contentID_b` ASC, `Relations_relationID` ASC) ,
  CONSTRAINT `fk_Contents_has_Contents_Contents`
    FOREIGN KEY (`Contents_contentID_a` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Contents_Contents1`
    FOREIGN KEY (`Contents_contentID_b` )
    REFERENCES `Capricore_test`.`Contents` (`contentID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_Contents_has_Contents_Relations`
    FOREIGN KEY (`Relations_relationID` )
    REFERENCES `Capricore_test`.`Relations` (`relationID` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


DELIMITER //
//
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
            REPLACE INTO Managers (manager) VALUES (_manager);
        END IF;
    END WHILE;
END;//
//
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
//
-- -------- set this ob db::init()
CREATE PROCEDURE `Capricore_test`.`Capricore_setTablePrefix`(IN _prefix VARCHAR(10))
BEGIN
    SET @cpcr_tbl_prefix = _prefix; 
END;//
//
CREATE FUNCTION `Capricore_test`.`Capricore_getTablePrefix`() RETURNS VARCHAR(10)
BEGIN
    RETURN IF (ISNULL(@cpcr_tbl_prefix), "", @cpcr_tbl_prefix);
END;//
//
CREATE PROCEDURE `Capricore_test`.`Capricore_setCurrentUser`(IN _userName VARCHAR(64))
BEGIN
    SET @cpcr_curr_usr = _userName; 
END;//
//
CREATE FUNCTION `Capricore_test`.`Capricore_getCurrentUser`() RETURNS VARCHAR(64)
BEGIN
    RETURN IF (ISNULL(@cpcr_curr_usr), "", @cpcr_curr_usr);
END;//
//
CREATE PROCEDURE `Capricore_test`.`BManager_createContent`()
BEGIN END;//
//
CREATE PROCEDURE `Capricore_test`.`BManager_updateContent`()
BEGIN END;//
//
CREATE PROCEDURE `Capricore_test`.`BManager_deleteContent`()
BEGIN END;//
//
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
END;//
//
-- -----------------------

CREATE FUNCTION `Capricore_test`.`Capricore_getPersonID`(_name VARCHAR(64), _email VARCHAR(64)) RETURNS INT
BEGIN 
    RETURN 0;
END;//
//
-- FUNCTIONS

CREATE FUNCTION `Capricore_test`.`Alias_fromTitle`(cid INT) 
RETURNS VARCHAR(97)
READS SQL DATA
BEGIN
    DECLARE _title VARCHAR(255);
    DECLARE _pubDate INT; -- DATETIME;
    DECLARE _Alias VARCHAR(100);
    
    SELECT Contents.title, Contents.pubDate
        FROM Contents
        WHERE Contents.contentID = cid
        INTO _title, _pubDate;
    
    SET _Alias = DATE_FORMAT(NOW(), '%Y-%m-%d-');
    SET _Alias = CONCAT(_Alias, LEFT(_title, 89));
    SET _Alias = Alias_Validate(_Alias);

    RETURN _Alias;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_Validate`(_in VARCHAR(100)) 
RETURNS VARCHAR(100)
READS SQL DATA
BEGIN
    DECLARE _char CHAR(1);
    DECLARE _out VARCHAR(100) DEFAULT '';
    DECLARE _ok INT;
    WHILE (_in != '') DO
        SET _char = LEFT(_in,1);
        SET _in = SUBSTR(_in,2);
        
        SELECT (_char regexp '^[a-z0-9_+*\'":\(\).!-]+$') INTO _ok;
        SET _char = IF(_ok = 1, _char,'_');
        SET _out = CONCAT(_out, _char);
    END WHILE;
    RETURN _out;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_Of`(_cid INT)
RETURNS VARCHAR(100)
READS SQL DATA
BEGIN
    DECLARE _alias INT DEFAULT '';
    
    SELECT alias 
        FROM Aliases 
        WHERE Contents_contentID = _cid
        ORDER BY active DESC
        LIMIT 1
        INTO _alias;
    
    RETURN _alias;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_assignedContent`(_alias VARCHAR(100))
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE _cid INT DEFAULT '';
    
    SELECT Contents_contentID 
        FROM Aliases 
        WHERE alias = _alias
        LIMIT 1
        INTO _cid;
    
    RETURN _cid;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_count`(_cid INT)
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE _count INT DEFAULT '';
    
    SELECT COUNT(*)
        FROM Aliases 
        WHERE Contents_contentID = _cid
        INTO _count;
    
    RETURN _count;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_fromID`(cid INT) 
RETURNS VARCHAR(97)
READS SQL DATA
BEGIN
    DECLARE _object VARCHAR(32);
    DECLARE _manager VARCHAR(64);
    
    SELECT Managers.manager, Contents.objectID
        FROM Contents
        LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
        WHERE Contents.contentID = cid
        INTO _manager, _object;
    RETURN CONCAT(_manager,':', _object);
END;//
//
CREATE PROCEDURE `Capricore_test`.`Alias_allAssignedTo`(IN _cid INT)
BEGIN 
    SELECT alias, active 
        FROM Aliases
        WHERE Contents_contentID = _cid
        ORDER BY alias;
END;//
//
CREATE PROCEDURE `Capricore_test`.`Alias_resetContent`(IN _cid INT)
BEGIN 
    START TRANSACTION;
    DELETE FROM Aliases WHERE Contents_contentID = _cid;
    CALL Alias_create(_cid);
    COMMIT;
END;//
//
-- PROCEDURES

CREATE PROCEDURE `Capricore_test`.`Alias_resetDB`()
BEGIN 
    DECLARE _cid INT;
    DECLARE _ptr CURSOR FOR
        SELECT contentID
            FROM Contents
            WHERE Contents.pubDate > 0;

    START TRANSACTION;
    DELETE FROM Aliases;
    OPEN _ptr;
    LOOP 
        FETCH _ptr INTO _cid;
        CALL Alias_create(_cid);
    END LOOP;
    COMMIT;
END;//
//
CREATE FUNCTION `Capricore_test`.`Alias_equalContent`(_alias_a VARCHAR(100),_alias_b VARCHAR(100))
RETURNS INT
READS SQL DATA
BEGIN
    RETURN IF((Alias_assignedContent(_alias_a) = Alias_assignedContent(_alias_b)),1,0);
END;//
//
CREATE PROCEDURE `Capricore_test`.`Alias_create`(IN _cid INT)
BEGIN 
    DECLARE _Alias VARCHAR(100);
    DECLARE _Suffix VARCHAR(10);
    DECLARE _looped, _ok INT DEFAULT 1;
    
    -- set default id
    SET _Alias = Alias_fromID(_cid);
    INSERT IGNORE 
        INTO Aliases 
            (alias, Contents_contentID) 
            VALUES (_Alias, _cid);
            
    SET _Alias = Alias_fromTitle(_cid);
    
    -- find better id using content title
    WHILE (_ok > 0) DO
        -- is someone else using this alias?
        SELECT COUNT(*) 
            FROM Aliases 
            WHERE alias = _Alias 
                AND Contents_contentID != _cid 
            INTO _ok;
        
        IF(_ok = 0) THEN
        -- not used or used by us: get it
            REPLACE 
                INTO Aliases 
                    (alias, Contents_contentID)
                    VALUES (_Alias, _cid);
        ELSE
        -- used by someone else: generate new alias
            SET _Suffix = CONCAT('-', _looped);
            SET _looped = _looped + 1;
            SET _Alias = CONCAT(LEFT(_Alias, CHAR_LENGTH(_Suffix)+1), _Suffix);
        END IF;
    END WHILE;
END;//
//
-- todo:
-- CREATE PROCEDURE Tags_related(IN _cid INT)
-- get tags of cid - select contents order by intersecting tag count
-- BEGIN END;
-- //


-- CREATE PROCEDURE Tags_match(IN _tags TEXT)
-- get cids matching all tags
-- BEGIN END;
-- //



-- FUNCTIONS

CREATE FUNCTION `Capricore_test`.`Tag_usage`(_tag VARCHAR(64))
RETURNS INT
READS SQL DATA
BEGIN 
    DECLARE _usage INT DEFAULT 0;
    SELECT COUNT(*) 
        FROM Tags 
        WHERE tag = _tag 
        INTO _usage;
    RETURN _usage;
END;//
//
CREATE FUNCTION `Capricore_test`.`Tag_count`(_cid INT)
RETURNS INT
READS SQL DATA
BEGIN
    DECLARE _count INT DEFAULT 0;

    IF (ISNULL(_cid))THEN
        SELECT COUNT(*) 
            FROM Tags
            RIGHT JOIN Contents_has_Tags
                ON (Tags.tagID = Contents_has_Tags.Tags_tagID)
            INTO _count;
    ELSE
        SELECT COUNT(*) 
            FROM Tags
            RIGHT JOIN Contents_has_Tags
                ON (Tags.tagID = Contents_has_Tags.Tags_tagID)
            WHERE Contents_has_Tags.Contents_contentID = _cid
            INTO _count;
    
    END IF;
    RETURN  _count;
END;//
//
-- PROCEDURES

CREATE PROCEDURE `Capricore_test`.`Tags_Of`(IN _cid INT)
BEGIN
-- get all tags of content
    SELECT tag
        FROM Tags
        RIGHT JOIN Contents_has_Tags
            ON (Tags.tagID = Contents_has_Tags.Tags_tagID)
        WHERE Contents_has_Tags.Contents_contentID = _cid;
END;//
//
CREATE PROCEDURE `Capricore_test`.`Tags_assign`(IN _cid INT,IN _tags TEXT)
BEGIN
    DECLARE _tag VARCHAR(64);
    DECLARE _pos INT;
 
    START TRANSACTION;
    DELETE FROM Contents_has_Tags WHERE Contents_contentID = _cid;
    
    WHILE NOT (_tags = "") DO
        SET _pos = LOCATE(',', _tags);
        IF(_pos = 0) THEN
            SET _tags = "";
        ELSE
            SET _tag = TRIM(LEFT(_tags, _pos-1));
            SET _tags = SUBSTR(_tags, _pos+1);
            IF (NOT ISNULL(_tag)) THEN
                REPLACE INTO Tags (tag) VALUES (_tags);
                INSERT INTO Contents_has_Tags
                    (Contents_contentID, Tags_tagID) 
                    VALUES ((SELECT tag FROM Tags WHERE tag = _tag LIMIT 1), _cid);
            END IF;
        END IF;
    END WHILE;
    
    COMMIT;
END;//
//
CREATE PROCEDURE `Capricore_test`.`Content_basicAttributes`(IN _cid INT, IN _public_only TINYINT) 
BEGIN 

END;//
//
CREATE PROCEDURE `Capricore_test`.`Content_list`(IN _offset INT, IN _items INT, IN _sortDirection TINYINT, IN _tagFilterCSV TEXT, IN _textFilter TEXT)
BEGIN 

END;//
//
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

CREATE PROCEDURE `Capricore_test`.`Content_updateLog`(IN _cid INT) 
BEGIN 

END;//
//
CREATE FUNCTION `Capricore_test`.`Content_isPublic`(_cid INT) RETURNS TINYINT
BEGIN 
    DECLARE _yn TINYINT;
    SELECT COUNT(*) 
        FROM Contents 
        WHERE contentID = _cid 
        AND pubDate <= NOW()
        AND pubDate > 0
        INTO _yn;
    RETURN _yn;
END;//
//
CREATE PROCEDURE Content_ManagerAndID(IN _cid INT)
BEGIN
    SELECT Contents.managerContentID, Managers.manager 
        FROM Contents 
        LEFT JOIN Managers ON (Contents.Managers_managerID = Managers.managerID)
        WHERE 
            Contents.contentID = _cid
        LIMIT 1 
END;//

DELIMITER ;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
