<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-27
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QUHeaderServices extends BQuery 
{
    public static function getServicesToEmbed($class)
    {
        $sql = "SELECT 
        				Classes.class,
        				Aliases.alias
        			 FROM relClassesChainedContents
        			 	LEFT JOIN Contents ON (relClassesChainedContents.chainedContentREL = Contents.contentID)
        			 	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
        			 	LEFT JOIN Classes ON (Contents.type = Classes.classID)
        			 	LEFT JOIN Classes AS ClassHelper ON (relClassesChainedContents.chainingClassREL = ClassHelper.classID)
    			 	WHERE 
    			 		ClassHelper.class = '%s'
    			 		AND Contents.pubDate > '0000-00-00 00:00:00'
    			 		AND Contents.pubDate <= NOW()
    			 	ORDER BY Classes.class, Aliases.alias ASC";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($class)), DSQL::NUM);
    }
}
?>