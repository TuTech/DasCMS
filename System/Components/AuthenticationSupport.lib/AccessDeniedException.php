<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-04-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Exceptions
 */
class AccessDeniedException extends Exception 
{
    public function __construct($message, $code = 0) 
    {
        parent::__construct($message, $code);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [".$this->code."]: ".$this->message."\n";
    }
}
?>