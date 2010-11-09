-- formatters table
CREATE TABLE IF NOT EXISTS  
Formatters(
    formatterID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    name 
        VARCHAR(32) 
        UNIQUE 
        NOT NULL,
    formatterData 
    	TEXT
    	NOT NULL
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS 
relContentsFormatters(
    contentREL 
        INTEGER 
        UNIQUE
        NOT NULL,    
    formatterREL 
        INTEGER 
        NOT NULL,
    INDEX(formatterREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- foreign keys for relContentsFormatters
ALTER TABLE 
relContentsFormatters
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (formatterREL)
        REFERENCES Formatters(formatterID)
        ON DELETE RESTRICT
        ON UPDATE NO ACTION;
