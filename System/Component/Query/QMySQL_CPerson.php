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
}
?>