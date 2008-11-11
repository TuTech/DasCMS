<?php
class QSUsersAndGroups extends BQuery 
{
    public static function createUser($user, $name, $email, $primaryGroup)
    {
        $sql = 
            "INSERT INTO Users 
				(login, name, email, primaryGroup)
			VALUES
				('%s', '%s', '%s', (SELECT groupID FROM Groups WHERE groupName = '%s'))";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($user)
            ,$DB->escape($name)
            ,$DB->escape($email)
            ,$DB->escape($primaryGroup)
        ));
    }
    public static function createGroup($group, $description)
    {
        $sql = 
            "INSERT INTO Groups
				(groupName, description)
			VALUES
				('%s', '%s')";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($group)
            ,$DB->escape($description)
        ));
    }
    
    public static function setUserData($user, $name, $email)
    {
        $DB = BQuery::Database();
        if(!$name && !$email)
        {
            return ;
        }
        $s = array();
        if($name !== null) $s[] = sprintf("name = '%s'",$DB->escape($name));
        if($email !== null) $s[] = sprintf("email = '%s'",$DB->escape($email));
        $sql = 
            "UPDATE Users 
				SET 
					%s 
				WHERE login = '%s'";
        return $DB->queryExecute(sprintf(
            $sql
            ,implode(',', $s)
            ,$DB->escape($user)
        ));
    }
    public static function setPrimaryGroup($user, $primaryGroup)
    {
        $sql = 
            "UPDATE Users 
				SET primaryGroup = 
						(SELECT groupID FROM Groups WHERE groupName = '%s')
				WHERE login = '%s'";
        $DB = BQuery::Database();
        $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($primaryGroup)
            ,$DB->escape($user)
        ));
        list($exists) = $DB->query(sprintf(
            "SELECT COUNT(*) FROM Groups WHERE groupName = '%s'", 
            $DB->escape($primaryGroup)))->fetch();
        if($exists)
        {
            $sql = 
                "REPLACE INTO relUsersGroups 
    				(userREL, groupREL)
    			VALUES
    				((SELECT userID FROM Users WHERE login = '%s'),
    				(SELECT groupID FROM Groups WHERE groupName = '%s'))";
            return $DB->queryExecute(sprintf(
                $sql
                ,$DB->escape($user)
                ,$DB->escape($primaryGroup)
            ));
        }   
    }
            
    public static function setGroupDescription($group, $description)
    {
        $sql = 
            "UPDATE Groups 
				SET description = '%s'
				WHERE groupName = '%s'";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($group)
            ,$DB->escape($description)
        ));
            
    }
    
    public static function deleteUser($user)
    {
        $sql = 
            "DELETE FROM Users 
				WHERE login = '%s'";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($user)
        ));
            
    }
    public static function deleteGroup($group)
    {
        $sql = 
            "DELETE FROM Groups 
				WHERE groupName = '%s'";
        $DB = BQuery::Database();
        return $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($group)
        ));
            
    }
    
    public static function setGroups($user, array $groups, $primaryGroup)
    {
        self::setPrimaryGroup($user, $primaryGroup);
        $sql = 
            "DELETE FROM relUsersGroups 
				WHERE userREL = (SELECT userID FROM Users WHERE login = '%s')
					AND groupREL != (SELECT groupID FROM Groups WHERE groupName = '%s')";
        
        $DB = BQuery::Database();
        $DB->queryExecute(sprintf(
            $sql
            ,$DB->escape($user)
            ,$DB->escape($primaryGroup)
        ));
        if(count($groups))
        {
            $sql =  
                "REPLACE INTO relUsersGroups 
        				(userREL, groupREL)
        			VALUES ";
            $tok = '';
            foreach ($groups as $grp) 
            {
            	$sql .= sprintf("
					%s((SELECT userID FROM Users WHERE login = '%s'),
					(SELECT groupID FROM Groups WHERE groupName = '%s'))"
					,$tok
					,$DB->escape($user)
            	    ,$DB->escape($grp)
            	);
            	$tok = ', ';
            }
        return $DB->queryExecute($sql);
        }
    }
}
?>