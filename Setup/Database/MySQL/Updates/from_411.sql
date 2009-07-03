CREATE TABLE IF NOT EXISTS 
relContentsClassesChainedContents(
    ownerContentREL 
        INTEGER 
        NOT NULL,
    chainingClassREL 
        INTEGER 
        NOT NULL,    
    chainedContentREL 
        INTEGER 
        NOT NULL,
    UNIQUE (ownerContentREL, chainingClassREL, chainedContentREL),
    INDEX(chainingClassREL),
    INDEX(chainedContentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
relClassesChainedContents(
    chainingClassREL 
        INTEGER 
        NOT NULL,    
    chainedContentREL 
        INTEGER 
        NOT NULL,
    UNIQUE (chainingClassREL, chainedContentREL),
    INDEX(chainedContentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- Foreign keys for relContentsClassesChainedContents
ALTER TABLE 
relContentsClassesChainedContents
    ADD CONSTRAINT owner FOREIGN KEY (ownerContentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT chainingClass FOREIGN KEY (chainingClassREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD CONSTRAINT chained FOREIGN KEY (chainedContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;

-- Foreign keys for relClassesChainedContents
ALTER TABLE 
relClassesChainedContents
    ADD FOREIGN KEY (chainingClassREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (chainedContentREL)
        REFERENCES Contents(contentID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;