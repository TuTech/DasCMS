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
    
   /**
     * @param int $dbid
     * @param string $newAlias
     * @return boolean success
     */
    public static function insertAndCheckAlias($dbid, $newAlias)
    {
        $DB = BQuery::Database();
        $affected = $DB->insert('Aliases',array('alias', 'contentREL'), array($newAlias, $dbid), true);
        if(!$affected)
        {
            //insert failed 
            //perhaps its already ours
            $res = $DB->query(sprintf("
                SELECT contentREL 	
                	FROM Aliases 
                	WHERE 
                		alias = '%s' 
                		AND contentREL = %d"
    			,$newAlias
                ,$dbid
            ));
            $affected = $res->getRowCount();
        }
        return ($affected == 1);
    }
    
    /**
     * @param string $alias
     * @return void
     */
    public static function setActive($alias)
    {
        $DB = BQuery::Database();
        $DB->query(sprintf("
            UPDATE Contents 
            	SET primaryAlias = (SELECT aliasID FROM Aliases WHERE alias = '%s')
            	WHERE contentID = (SELECT contentREL FROM Aliases WHERE alias = '%s')"
			,$DB->escape($alias)
			,$DB->escape($alias)
        ));
    }
    
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
    
    /**
     * @param string $alias
     * @return DSQLResult
     */
    public static function reloveAliasToID($alias)
    {
        $DB = BQuery::Database();
        $sql = "SELECT contentREL FROM Aliases WHERE alias = '%s'";
        $res = $DB->query(sprintf(
            $sql,
            $DB->escape($alias)
        ));
        if($res->getRowCount() == 0)
        {
            return null;
        }
        else
        {
            list($c) = $res->fetch();
            return $c;
        }
    }
}
?>