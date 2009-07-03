<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-05
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QVSpore extends BQuery 
{
    public static function loadSpores()
    {
        $sql = 
            "SELECT SporeViews.viewName, SporeViews.active, DefAliases.alias, ErrAliases.alias
            	FROM SporeViews
            	LEFT JOIN Contents AS DefContent ON (SporeViews.defaultContentREL = DefContent.contentID) 
            	LEFT JOIN Contents AS ErrContent ON (SporeViews.errorContentREL = ErrContent.contentID)
            	LEFT JOIN Aliases  AS DefAliases ON (DefContent.primaryAlias = DefAliases.aliasID)
            	LEFT JOIN Aliases  AS ErrAliases ON (ErrContent.primaryAlias = ErrAliases.aliasID) 
            	ORDER BY SporeViews.viewName ASC";
        $DB = BQuery::Database();
        return $DB->query($sql, DSQL::NUM);
    }
    
    public static function deleteSpores(array $spores)
    {
        if(count($spores) == 0)
        {
            return;
        }
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $sporeSql = array();
        try
        {
            $sql = "DELETE FROM SporeViews WHERE (viewName = \"%s\")";
            foreach ($spores as $name)
            {
                $sporeSql[] = $DB->escape($name);
            }
            $sql = sprintf($sql,implode('" OR viewName = "', $sporeSql));
            $DB->queryExecute($sql);
            $DB->commit();
        }
        catch (Exception $e)
        {
            $DB->rollback();
            throw $e;
        }
    } 
    
    public static function saveSpores(array $sporeData)
    {
        $DB = BQuery::Database();
        $DB->beginTransaction();
        $sqlTPL = "INSERT
        				INTO SporeViews (viewName, active, defaultContentREL, errorContentREL) 
        				VALUES ('%s', '%s', %s, %s)
                        ON DUPLICATE KEY UPDATE
                        	active = '%s',
                        	defaultContentREL = %s,
                        	errorContentREL = %s";
        $sqlContentTPL = "(SELECT contentREL FROM Aliases WHERE alias = '%s' LIMIT 1)";
        try
        {
            foreach ($sporeData as $name => $data)
            {
                $def = empty($data[VSpore::INIT_CONTENT]) ? 'NULL' : sprintf($sqlContentTPL, $DB->escape($data[VSpore::INIT_CONTENT]));
                $err = empty($data[VSpore::ERROR_CONTENT]) ? 'NULL' : sprintf($sqlContentTPL, $DB->escape($data[VSpore::ERROR_CONTENT]));
                $sql = sprintf(
                    $sqlTPL
                    ,$DB->escape($name)
                    ,$data[VSpore::ACTIVE] ? 'Y' : 'N'
                    ,$def
                    ,$err
                    ,$data[VSpore::ACTIVE] ? 'Y' : 'N'
                    ,$def
                    ,$err
                );
                $DB->queryExecute($sql);
            }
            $DB->commit();
        }
        catch (Exception $e)
        {
            $DB->rollback();
            throw $e;
        }
    }
}
?>