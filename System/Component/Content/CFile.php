<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class CFile extends BContent implements ISupportsSidebar 
{
	const MANAGER = 'MFiles';
	private 
		$_created = false;
	
	/**
	 * constructor
	 *
	 * @param string $id
	 * @throws XFileNotFoundException
	 */
	public function __construct($Id)
	{
		$manager = MPageManager::alloc()->init(); 
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => BAMBUS_USER,
			'ModifyDate' => time(),
			'ModifiedBy' => BAMBUS_USER,
			'PubDate' => 0,
			'Size' => 0,
			'Title' => 'new CFile '.date('r'),
			'Description' => ' ',
		);		
		if(is_uploaded_file($Id))
		{
			$this->_created = true; //no further lookup in db
			$this->Id = $Id;
		}
		elseif(file_exists($this->StoragePath($Id)))
		{
			$this->Id = $Id;
		}
		else
		{
		//	throw XFileNotFoundException($Id);
		}
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
		$this->_origPubDate = $this->PubDate;
	}
	
	/**
	 * ISupportsSidebar function
	 *
	 * @param unknown_type $category
	 * @return unknown
	 */
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('settings', 'information', 'search'));
	}

	/**
	 * on serialize
	 * 
	 * @return array
	 */
	public function __sleep()
	{
		return array();
	}
	
	/**
	 * save changes
	 */
	public function Save()
	{
	}
	
	/**
	 * initialized MPageManager object
	 *
	 * @return MFiles
	 */
	public function getManager()
	{
		return MFiles::alloc()->init();
	}	
	public function getManagerName()
	{
		return self::MANAGER;
	}
}
?>