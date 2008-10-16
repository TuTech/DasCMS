<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.11.2007
 * @license GNU General Public License 3
 */
class CError extends BContent
{
	const MANAGER = 'MError';
	
	public function __construct($Id)	
	{
		$dat = MError::errdesc($Id);
		$Ids = array_keys($dat);
		$this->Id = $Ids[0];
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => 'System',
			'ModifyDate' => time(),
			'ModifiedBy' => 'System',
			'PubDate' => 0,
			'Size' => 0,
			'Title' => 'ERROR '.$Id.' - '.$dat[$Id],
			'Content' => sprintf('<div class="%s"><h1>ERROR %d - %s</h1></div>',get_class($this),$this->Id,$dat[$this->Id])
		);
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
	}

	public function __get($var)
	{
		return !empty($this->{$var}) ? $this->{$var} : '';
	}
	
	public function __set($var, $value){}
	
	public function __isset($var)
	{
		return isset($this->{$var});
	}
	
	public function Save(){}
	
	/**
	 * responsible (initialized) manager object
	 * @return BContentManager
	 */
	public function getManager()
	{
		return MError::alloc()->init();
	}
}
?>