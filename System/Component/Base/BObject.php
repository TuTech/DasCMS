<?php
/**
 * @package Bambus
 * @subpackage BaseClasses
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 28.11.2007
 * @license GNU General Public License 3
 */
abstract class BObject
{
	/**
	 * load a controller for given app id
	 *
	 * @param string $ID
	 * @return BObject
	 * @throws XPermissionDeniedException
	 * @throws XUndefinedException
	 */
	public static function InvokeObjectByID($ID)
	{
	    //FIXME use some kind of config file
	    switch($ID)
	    {
	        case 'org.bambuscms.applications.websiteeditor':
	            return new AWebsiteEditor();
	        case 'org.bambuscms.applications.templateeditor':
	            return new ATemplateEditor();
	        case 'org.bambuscms.applications.stylesheeteditor':
	            return new AStylesheetEditor();
	        case 'org.bambuscms.applications.treenavigationeditor':
	            return new ATreeNavigationEditor();
	        case 'org.bambuscms.applications.usereditor':
	            return new AUserEditor();
	        case 'org.bambuscms.applications.groupmanager':
	            return new AGroupManager();
	        case 'org.bambuscms.view.spore':
	            return new VSpore();
	        case 'org.bambuscms.navigation.treenavigation':
	            return NTreeNavigation::alloc()->init();
	        case 'org.bambuscms.navigation.listnavigation':
	            return NListNavigation::alloc()->init();
	        case 'org.bambuscms.applications.templates':
	            return new ATemplates();
            default:
	            throw new XUndefinedException('controller not found');
	    }
	}

	//do path resolve
	//local id --> cms id path
	//cms id path --> local id
	/**
	 * initialite property in $var with $dataArray[$var] if it exists or use a default value
	 * only works if the property is null
	 *
	 * @param string $var
	 * @param array $dataArray
	 * @param mixed $defaultValue
	 */
	protected function initPropertyValues($var,array &$dataArray, $defaultValue)
	{
		if($this->{$var} == null)
		{
			$this->{$var} = (array_key_exists($var, $dataArray))
				? $dataArray[$var]
				: $defaultValue;
		}
	}
	
	
	public final static function InvokeObjectByDynClass($class)
	{
		$object = null;
		if(class_exists($class, true))
		{
			$temp = new $class();
			if($temp instanceof IShareable)
			{
				$object = $temp->alloc()->init();
			}
			else
			{
				$object = $temp;
			}
		}
		return $object;
	}
	
	//objects are not serializeable by default
	public function __sleep()
	{
		return array();
	}
	/**
	 * Return path to a given file or just the path for files 
	 * if $file is not set or null 
	 *
	 * @param string $file
	 * @return string file system path
	 */
	public function StoragePath($file = null, $addSuffix = true)
	{
		$path = sprintf(
			"./Content/%s/"
			,get_class($this)
		);
		if($file != null)
		{
			$path .= ($addSuffix) ? $file.'.php' : $file;
		}
		return $path;
	}
}
?>