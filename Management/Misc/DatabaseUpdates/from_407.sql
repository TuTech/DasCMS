ALTER TABLE Changes
	ADD COLUMN latest
    	ENUM('Y', 'N') 
    	DEFAULT 'Y'
		NOT NULL;
		
UPDATE Changes SET latest = 'N';

-- flag the latest version
UPDATE Changes RIGHT JOIN (SELECT contentREL, MAX(changeDate) as changeDate FROM Changes GROUP BY contentREL) AS tmp
ON (Changes.changeDate = tmp.changeDate AND Changes.contentREL = tmp.contentREL)
SET latest = 'Y';

-- for conflicting updates create new entry
INSERT INTO Changes
SELECT contentREL, '@@system update@@' AS 'title', 0 AS 'size', NOW() AS 'changeDate', NULL AS userREL, 'Y' as 'latest' FROM 
((SELECT contentREL, count(latest) AS 'c' From Changes 
WHERE 
	latest = 'Y'  
GROUP BY contentREL
HAVING count(latest) > 1) as tmp);

-- remove duplicates
UPDATE Changes
RIGHT JOIN 
(SELECT contentREL, count(latest) AS 'c' From Changes 
WHERE 
	latest = 'Y'  
GROUP BY contentREL
HAVING count(latest) > 1) as tmp
USING(contentREL)
SET 
	latest = 'N',
	title = '-system update-'
WHERE title != '@@system update@@';