<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Exceptions
 */
class XFileLockedException extends BIOException 
{
	protected  $target;
    public function __construct($message, $file, $code = 0) 
    {
    	$this->target = $file;
        parent::__construct('['.$file.'] '.$message, $code);
    }

    public function __toString() 
    {
        return __CLASS__ . ": [".$this->code."]: ".$this->message."\n";
    }
}
?>