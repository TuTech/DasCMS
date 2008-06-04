<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
class SResolve extends BSystem implements IShareable
{
	private static $_ContentManagers = null;
	
	public static function Path($path)
	{
		$parts = explode('>', $path);
		$pcount = count($parts);
		$object = null;
		if($pcount > 1)
		{
			//cms internal path
			//TODO: du real parsing
			switch($pcount)
			{
				case 1: //PageManager
					$fc = substr($parts[0],0,1); //first char
					$sc = substr($parts[0],1,1); //second char
					if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
					{						  //use the content class as default
						$fc = 'M';
						$parts[0] = 'M'.$parts[0];
					}
//					echo '<i>'.$parts[1].'</i>';
//					if(in_array($parts[1], self::ContentManagers()))
					{
						$object = call_user_func(array($parts[0],'alloc'));
						$object->init();
					}
					break;
				case 2:	//PageManager>3cf363f695d4c6045c1dc25200aca335
					$fc = substr($parts[0],0,1); //first char
					$sc = substr($parts[0],1,1); //second char
					if($sc == strtolower($sc))//valid class name begins with to uppercase chars 
					{						  //use the content class as default
						$fc = 'M';
						$parts[0] = 'M'.$parts[0];
					}
//					echo '<b>'.$parts[1].' ';
//					echo ''.$parts[2].' </b>';
					//if(in_array($parts[1], self::ContentManagers()))
					{
						$parentObject = call_user_func(array($parts[0],'alloc'));
						if(!is_subclass_of($parentObject, 'BContentManager'))
						{
							return null;
						}
						$parentObject->init();
//						echo 'resolved ';
						$object= $parentObject->Open($parts[1]);
						if(!$object instanceof BContent)
						{
							$object = null;
						}
					}
					break;
			}
		}
		return $object;
	}
	
	public static function PathCompleteOptions($incompletePath)
	{
		$allparts = explode('>', $incompletePath);
		$parts = array();
		foreach($allparts as $apart)
		{
			if(!empty($apart))
				$parts[] = $apart;
		}
		$pcount = count($parts);
		//cms internal path
		//TODO: do real parsing
		//TODO: add N* classes to options
		switch($pcount)
		{
			case 0://content manager classes <ContentManager>
				$cmngrs = self::ContentManagers();
				sort($cmngrs, SORT_STRING);
				$res = array();
				foreach ($cmngrs as $cm) 
				{
					$res[$cm] = $cm;
				}
				return $cm;
			case 1: //items of a content manager class <Content>
				$parentObject = call_user_func($parts[1].'::alloc');
				if(is_subclass_of($parentObject, 'BContentManager'))
				{
					$parentObject->init();
					return $parentObject->Index;
				}
			default: 
				return array();
		}
		return $object;
	}
	
	public static function IsValidPath($path)
	{
		
	}
	
	private static function ContentManagers()
	{
		if(self::$_ContentManagers == null)
		{
			$classes = get_declared_classes();
			$options = array();
			foreach($classes as $class)
			{
				if(is_subclass_of($class, 'ContentManager'))
					$options[] = $class;
			}
			self::$_ContentManagers = $options;
		}
		return self::$_ContentManagers;
	}
	
	//IShareable
	const Class_Name = 'SResolve';
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	return $this;
    }
	//end IShareable
	
}

?>