<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-15
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QNListNavigation extends BQuery 
{
    /**
     * @param array $tags
     * @return DSQLResult
     */
    public static function listTagged(array $tags)
    {
        if(count($tags) == 0)
        {
            //list all
            $tsql = '1';
        }
        else
        {
        $DB = BQuery::Database();
        $ts = array();
        //building list of tags
        foreach ($tags as $t) 
        {
        	$ts[] = sprintf("Tags.tag = '%s' ", $DB->escape($t));
        }
        $tf = implode('or ', $ts);
        
        //selecting items having all given tags
        $tsql = sprintf("
            Content.contentID IN 
            (
            	SELECT CID FROM 
            	(
                	SELECT Contents.contentID as CID, count(Tags.tag) AS Found 
                    	FROM relContentsTags
                    	LEFT JOIN Contents ON (relContentsTags.contentREL = Contents.contentID)
                    	LEFT JOIN Tags ON (relContentsTags.tagREL = Tags.tagID)
                    	WHERE
                    		($tf)
                    	GROUP BY CID
            	)
            	WHERE Found = %d
            )
            ",count($tags));
        }
        //selecting what we want of all/tagged
        $sql = sprintf("
            SELECT 
					Aliases.alias,
                    Contents.title,
                    Classes.class,
                    Contents.pubDate
            	FROM Contents
            	LEFT JOIN Classes ON (Contents.type = Classes.classID)
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
            	WHERE 
            		Contents.pubDate > 0
            		AND Contents.pubDate <= NOW()
            		AND %s
            		ORDER BY title ASC
            ", $tsql);
		return $DB->query($sql, DSQL::NUM);
    }
}
?>