<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-07-21
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Query-MySQL
 */
class QDPersistentDictionary extends BQuery
{
    public static function createDictIfNotExists($alias, $dictName)
    {
        $sql = "SELECT persistentDictionaryID 
            			FROM PersistentDictionary 
            			LEFT JOIN Aliases USING(contentREL) 
        			WHERE PersistentDictionary.name = '%s' 
        				AND Aliases.alias = '%s'";
    }
    
    public static function fetchData($dictID)
    {
        $sql = "SELECT key,value 
        			FROM PDRef 
        			LEFT JOIN PDKeys ON (PDRef.keyREL = PDKeys.keyID) 
        			LEFT JOIN PDValues ON (PDRef.valREL = PDValues.valID)
        			WHERE PDRef.dictREL = %d";
    }
    
    public static function setData($dictID, $data)
    {
        //dump keys
        //dump values 
        //link dict=>key=>value
    }
    
    public static function removeKeys($dictID, $keys)
    {
        //unlink dict=>key
    }
}
?>