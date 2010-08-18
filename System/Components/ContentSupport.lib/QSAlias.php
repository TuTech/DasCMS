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
class QSAlias extends BQuery 
{
    public static function getMatching($alias, array $aliases)
    {
        $DB = BQuery::Database();
        $escaped = array();
        foreach ($aliases as $a)
        {
            $escaped[] = $DB->escape($a);
        }
        $eAlias = $DB->escape($alias);
        $sql = 
        	"SELECT alias 
        		FROM Aliases 
        		WHERE 
        			(alias = '".implode("' OR alias = '", $escaped)."') 
        			AND contentREL = (SELECT contentREL FROM Aliases WHERE alias = '".$eAlias."')
        			ORDER BY alias = '".$eAlias."' DESC
        			LIMIT 1";
        return $DB->query($sql, DSQL::NUM);
    }
}
?>