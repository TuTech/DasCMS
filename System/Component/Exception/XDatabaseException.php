<?php
/**
 * @package Bambus
 * @subpackage Exceptions
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 03.07.2008
 * @license GNU General Public License 3
 */
class XDatabaseException extends BDataException 
{
    public function __construct($message, $code = 0) 
    {
        parent::__construct($message, $code);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
?>