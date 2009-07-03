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
    
    //////////Person extended attributes
    
    /**
     * @param array $aliases
     * @return DSQLResult
     */
    public static function getBasicInformation()
    {
        $DB = BQuery::Database();
        $sql = "
            SELECT 
            		Contents.title AS Title,
            		Contents.pubDate AS PubDate,
            		Aliases.alias AS Alias,
					Mimetypes.mimetype,
					Contents.contentID,
					PersonPrimaryAttributes.company
            	FROM Contents 
            	LEFT JOIN Aliases ON (Contents.primaryAlias = Aliases.aliasID)
				LEFT JOIN Classes ON (Contents.type = Classes.classID)
				LEFT JOIN Mimetypes ON (Contents.mimetypeREL = Mimetypes.mimetypeID)
				LEFT JOIN PersonPrimaryAttributes ON (Contents.contentID = PersonPrimaryAttributes.contentREL)
            	WHERE 
					Classes.class = 'CPerson'
				ORDER BY Contents.title ASC";
       return $DB->query(sprintf($sql));
    }
    
    /**
     * @param int $pid
     * @return DSQLResult
     */
    public static function getXAttrs($pid)
    {
        $sql = "SELECT title, forename, surname, company FROM PersonPrimaryAttributes WHERE contentREL = %d";
        return BQuery::Database()->query(sprintf($sql, $pid), DSQL::NUM);
    }
    
    /**
     * @param int $pid
     * @param string $title
     * @param string $forename
     * @param string $surname
     * @param string $company
     * @return int
     */
    public static function setXAttrs($pid, $title, $forename, $surname, $company)
    {
        $sql = "INSERT INTO 
        			PersonPrimaryAttributes 
        				(contentREL, title, forename, surname, company)
        			VALUES 
        				(%d, '%s', '%s', '%s', '%s')
        			ON DUPLICATE KEY UPDATE 
                        title = '%s',
                        forename = '%s',
                        surname = '%s',
                        company = '%s'";
        $DB = BQuery::Database();
        $title = $DB->escape($title);
        $forename = $DB->escape($forename);
        $surname = $DB->escape($surname);
        $company = $DB->escape($company);
        return $DB->queryExecute(sprintf($sql, $pid, $title, $forename, $surname, $company, 
                                                     $title, $forename, $surname, $company), DSQL::NUM);
    }
    
    //////////Login IO
    
    /**
     * @param $pid
     * @param $user
     * @param $digestHA1
     * @param $digestRealm
     * @return int
     */
    public static function createCredentials($pid, $user, $digestHA1, $digestRealm)
    {
        $sql = "INSERT INTO PersonLogins
        			(contentREL, loginName, digestHA1, digestRealm)
        			VALUES(%d, '%s', '%s', '%s')";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $pid, $DB->escape($user), $DB->escape($digestHA1), $DB->escape($digestRealm));
        return $DB->queryExecute($sql);
    }
    
    /**
     * @param $pid
     * @param $digestHA1
     * @return int
     */
    public static function setNewPassword($pid, $digestHA1)
    {
        $sql = "UPDATE PersonLogins SET digestHA1 = '%s' WHERE contentREL = %d";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($digestHA1), $pid);
        return $DB->queryExecute($sql);
    }
    
    /**
     * @param $loginName
     * @return DSQLResult
     */
    public static function getAliasForUser($loginName)
    {
        $sql = "SELECT Aliases.alias 
        			FROM PersonLogins
        			LEFT JOIN Aliases USING (contentREL)
        			WHERE PersonLogins.loginName = '%s'
        			LIMIT 1";
        $DB = BQuery::Database();
        $sql = sprintf($sql, $DB->escape($loginName));
        return $DB->query($sql, DSQL::NUM);
    }
    
    /**
     * @param $pid
     * @return int
     */
    public static function removeLogin($pid)
    {
        $sql = "DELETE FROM PersonLogins WHERE contentREL = %d";
        return BQuery::Database()->queryExecute(sprintf($sql, $pid));
    }
    
    /**
     * @param $pid
     * @return DSQLResult
     */
    public static function getCredentials($pid)
    {
        $sql = "SELECT 
        			loginName, digestHA1, digestRealm
        			FROM PersonLogins
        			WHERE contentREL = %d";
        return BQuery::Database()->query(sprintf($sql, $pid), DSQL::NUM);
    }
    
    /**
     * @param string $username
     * @return DSQLResult
     */
    public static function getUser($username)
    {
        $sql = "SELECT contentREL FROM PersonLogins WHERE loginName = '%s'";
        $DB = BQuery::Database();
        return $DB->query(sprintf($sql, $DB->escape($username)), DSQL::NUM);
    }
    
    //////////Person IO
    
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