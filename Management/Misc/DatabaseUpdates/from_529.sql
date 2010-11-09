-- TABLES

-- aggregators table
CREATE TABLE IF NOT EXISTS  
ContentAggregators(
    contentAggregatorID 
        INTEGER 
        PRIMARY KEY 
        AUTO_INCREMENT 
        NOT NULL,
    name 
        VARCHAR(32) 
        UNIQUE 
        NOT NULL,
    aggregatorClassREL
    	INTEGER 
        NOT NULL,
    aggregatorData
    	TEXT
    	NOT NULL,
	INDEX(aggregatorClassREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- contents that needs to be reaggregated
CREATE TABLE IF NOT EXISTS 
ReaggregateContents(
    contentAggregatorREL 
        INTEGER 
        NOT NULL,
    contentREL 
        INTEGER 
        NOT NULL,   
    INDEX(contentAggregatorREL),   
    UNIQUE(contentREL, contentAggregatorREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- RELATIONS

-- contents assigned to this aggregator
CREATE TABLE IF NOT EXISTS 
relAggregatorsContents(
    contentAggregatorREL 
        INTEGER 
        NOT NULL,
    contentREL 
        INTEGER 
        NOT NULL,
    INDEX(contentAggregatorREL),
    UNIQUE(contentREL, contentAggregatorREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;

-- contents using an aggregator
CREATE TABLE IF NOT EXISTS 
relContentsAggregator(
    contentREL 
        INTEGER 
        NOT NULL,
    contentAggregatorREL 
        INTEGER 
        NOT NULL,
    INDEX(contentAggregatorREL),
    UNIQUE(contentREL)
)
ENGINE = InnoDB 
CHARACTER SET utf8 
COLLATE utf8_unicode_ci;


-- REFERENCES

ALTER TABLE 
ReaggregateContents
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

ALTER TABLE 
relAggregatorsContents
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

ALTER TABLE 
relContentsAggregator
    ADD FOREIGN KEY (contentAggregatorREL)
        REFERENCES ContentAggregators(contentAggregatorID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION,
    ADD FOREIGN KEY (contentREL)
        REFERENCES Contents(contentID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;

        
