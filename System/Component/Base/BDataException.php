<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
abstract class BDataException extends Exception 
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