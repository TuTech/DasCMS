<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-03-31
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QCPerson extends BQuery 
{
    public static function begin()
    {
        BQuery::Database()->beginTransaction();
    }
    
    public static function save()
    {
        BQuery::Database()->commit();
    }
    
    public static function resetPersonData($personID)
    {
        $sql = sprintf("DELETE FROM PersonData WHERE contentREL = %d", $personID);
        return BQuery::Database()->queryExecute($sql);
    }
        
    public static function availableContexts(array $contexts = array())
    {    
        $DB = BQuery::Database();
        $sql = "SELECT personContext FROM PersonContexts WHERE ";
        if(count($contexts))
        {
            $ctxts = array();
            foreach($contexts as $ctx)
            {
                $ctxts[] = 'personContext = "'.$DB->escape($ctx).'"';
            }
            $sql .= implode(' OR ',$ctxts);
        }
        else
        {
            $sql .= '0';
        }
        return $DB->query($sql, DSQL::NUM); 
    }
    
    public static function addContexts(array $contexts)
    {
        if(!count($contexts))
        {
            return;
        }
        $DB = BQuery::Database();
        $sql = "INSERT IGNORE INTO PersonContexts (personContext) VALUES ";
        $ctxts = array();
        foreach($contexts as $ctx)
        {
            $ctxts[] = '("'.$DB->escape($ctx).'")';
        }
        $sql .= implode(',',$ctxts);
        return $DB->queryExecute($sql);
    }   
     
    /**
     * @return DSQLResult
     */
    public static function getAttributesWithType()
    {
        $DB = BQuery::Database();
        $sql = "SELECT 
        				PersonAttributes.personAttribute,
        				PersonAttributeTypes.personAttributeType
        			FROM PersonAttributes
    				LEFT JOIN PersonAttributeTypes
    					ON (PersonAttributes.personAttributeTypeREL = PersonAttributeTypes.personAttributeTypeID)";
        return $DB->query($sql, DSQL::NUM);
    }

    /**
     * @return DSQLResult
     */
    public static function getAttributeContexts($attribute)
    {
        $DB = BQuery::Database();
        $sql = sprintf(
        	"SELECT PersonContexts.personContext
        			FROM PersonAttributes
        			LEFT JOIN PersonAttributeContexts 
        				ON (PersonAttributes.personAttributeID = PersonAttributeContexts.personAttributeREL)
    				LEFT JOIN PersonContexts
    					ON (PersonContexts.personContextID = PersonAttributeContexts.personContextREL)
					WHERE PersonAttributes.personAttribute = '%s'"
            ,$DB->escape($attribute)
        );
        return $DB->query($sql, DSQL::NUM);
    }
    
    /**
     * @return DSQLResult
     */
    public static function getEntriesForPerson($personID)
    {
        //select $att , $ctx, $val
        $DB = BQuery::Database();
        $sql = sprintf(
        	"SELECT 
        				PersonAttributes.personAttribute,
        				PersonContexts.personContext,
        				PersonData.personData
                    FROM PersonAttributes
                    LEFT JOIN PersonAttributeContexts 
        				ON (PersonAttributes.personAttributeID = PersonAttributeContexts.personAttributeREL)
    				LEFT JOIN PersonContexts
    					ON (PersonContexts.personContextID = PersonAttributeContexts.personContextREL)
					LEFT JOIN PersonData
						ON (PersonData.personAttributeContextREL = PersonAttributeContexts.personAttributeContextID)
					WHERE PersonData.contentREL = %d
					ORDER BY 
						PersonAttributes.personAttribute,
        				PersonContexts.personContext,
        				PersonData.personData"
            ,$personID
        );
        return $DB->query($sql, DSQL::NUM);
    }
    
    public static function assignPersonAttributeContextValue($personID, $attribute, $context, $value)
    {
        $DB = BQuery::Database();
        $sql = "SELECT personAttributeContextID 
        			FROM PersonAttributeContexts 
        			LEFT JOIN PersonAttributes 
        				ON (PersonAttributes.personAttributeID = PersonAttributeContexts.personAttributeREL)
        			LEFT JOIN PersonContexts 
        				ON (PersonContexts.personContextID = PersonAttributeContexts.personContextREL)
    				WHERE
    					PersonAttributes.personAttribute = '%s'
    					AND
        				PersonContexts.personContext = '%s'";
        $sql = sprintf($sql, $DB->escape($attribute), $DB->escape($context));
        $res = $DB->query($sql, DSQL::NUM);
        if($res->getRowCount() == 1)
        {
            list($id) = $res->fetch();
            $id = intval($id);
            $res->free();
        }
        else
        {
            $res->free();
            $sql = "INSERT INTO PersonAttributeContexts (personAttributeREL, personContextREL)
            			VALUES (
            				(SELECT personAttributeID FROM PersonAttributes WHERE personAttribute = '%s'),
            				(SELECT personContextID FROM PersonContexts WHERE personContext = '%s')
        				)";
            $DB->queryExecute(sprintf($sql, $DB->escape($attribute), $DB->escape($context)));
            $id = 'LAST_INSERT_ID()';
        }
        $sql = sprintf(
        	"INSERT INTO PersonData (contentREL, personAttributeContextREL, personData)
    			VALUES(%d, %s, '%s')"
			,$personID
			,$id
			,$DB->escape($value)
        );
        return $DB->queryExecute($sql);
    }
}
?>