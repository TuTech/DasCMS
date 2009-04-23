<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-07-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Exceptions
 */
class XDatabaseException extends BDataException 
{
    protected $sql;
    
    public function __construct($message, $code = 0, $sql = null) 
    {
        $this->sql = $sql;
        parent::__construct($message, $code);
    }
    
    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
    
    public function rollback()
    {
        return DSQL::alloc()->init()->rollback();
    }
    
    public function getSQL()
    {
        return $this->sql;
    }
}
?>