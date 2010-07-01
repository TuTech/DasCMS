ALTER TABLE relContentsFormatters
	ADD COLUMN classREL
		INTEGER NULL
		AFTER formatterREL,
	ADD UNIQUE(classREL, contentREL),
	ADD FOREIGN KEY (classREL)
        REFERENCES Classes(classID)
        ON DELETE CASCADE
        ON UPDATE NO ACTION;