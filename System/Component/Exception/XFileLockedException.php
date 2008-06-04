<?php
/**
 * @package Bambus
 * @subpackage Exceptions
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
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