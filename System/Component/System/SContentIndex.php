<?php
/**
 * @package Bambus
 * @subpackage System
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 24.03.2008
 * @license GNU General Public License 3
 */
class SContentIndex 
	extends 
	    BSystem 
	implements 	
	    IShareable
{
	//IShareable
	const CLASS_NAME = 'SContentIndex';
	/**
	 * @var SContentIndex
	 */
	public static $sharedInstance = NULL;
	private static $initializedInstance = false;
	/**
	 * @return SContentIndex
	 */
	public static function alloc()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return SContentIndex
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
	
	/**
	 * @return array [dbid,alias]
	 */
	public function createContent($class, $title)
	{
	    return QSContentIndex::create($class, $title);
	}
	
	public function deleteContent($alias, $from)
	{
	    if($this->exists($alias, $from) && !empty($from))
	    {
	        $res = QSContentIndex::getDBID($alias);
	        if($res->getRowCount() != 1)
	        {
	            throw new XUndefinedException();
	        }
	        list($id) = $res->fetch();
	        return QSContentIndex::deleteContent($id);
	    }
	    else
	    {
	        throw new XUndefinedException();
	    }
	}
	
	public static function exists($alias, $asType = null)
	{
	    if($asType)
	    {
	        $erg = QSContentIndex::exists($alias, $asType);
	    }
	    else
	    {
	        $res = QSContentIndex::getDBID($alias);
	        $erg = $res->getRowCount();
	        $res->free();
	    }
	    return $erg == 1;
	}

	public static function getContentInformationBulk(array $aliases)
	{
	    $res = QSContentIndex::getPrimaryAliases($aliases);
	    $map = array();
	    $revmap = array();
	    $infos = array();
	    while ($erg = $res->fetch())
		{
		    list($reqest, $primary) = $erg;
		    $map[] = $primary;
		    $revmap[$primary] = $reqest;
		}
	    $res->free();
	    
	    $res = QSContentIndex::getBasicInformation($map);
	    while ($erg = $res->fetch())
		{
		    list($title, $pubdate, $alias) = $erg;
		    $infos[$revmap[$alias]] = array(
		        'Title' => $title, 
				'Alias' => $alias,
				'PubDate' => strtotime($pubdate)
			);
		}
		$res->free();
		return $infos;
	}
	
	public static function getTitleAndAlias($alias)
	{
	    $ar =  $this->getContentInformationBulk(array($alias));
	    if(count($ar) == 1)
	    {
	        return array_pop($ar);
	    }
	    else
	    {
        	return array(
    			'Title' 	=> 'Error 404', 
    			'Alias' 	=> 'CError:404',
    			'PubDate' 	=> 1
			); 
	    }
	}	
	
	/**
	 * @return array (alias => Title)
	 */
	public function getIndex($class, $simple = true)
	{
	    if(is_object($class))
	    {
	        $class = get_class($class);
	    }
		try
		{
		    $res = QSContentIndex::getBasicInformationForClass($class);
			$index = array();
			while ($arr = $res->fetch())
			{
			    list($title, $pubdate, $alias, $type, $id) = $arr; 
				$index[$alias] = $simple ? $title : array($title, $pubdate, $type, $id);
			}
			$res->free();
		}
		catch (Exception $e)
		{
			echo $e->getMessage();
			$index = array();
		}
		return $index;
	}
}
?>