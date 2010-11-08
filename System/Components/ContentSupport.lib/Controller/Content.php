<?php
class Controller_Content implements Interface_Singleton
{
    //Interface_Singleton

    /**
     * @var Controller_Content
     */
	private static $sharedInstance = null;

	/**
     * @return Controller_Content
     */
	public static function getInstance()
	{
		if(self::$sharedInstance == null)
		{
			self::$sharedInstance = new Controller_Content();
		}
		return self::$sharedInstance;
	}

	//end Interface_Singleton

	protected function className($objectOrClass){
		return is_object($objectOrClass) ? get_class($objectOrClass) : strval($objectOrClass);
	}

	/**
	 * @param string $alias
	 * @param string $ifIsType
	 * @return Interface_Content
	 */
	public function openContent($alias, $ifIsType = null)
    {
	    if(empty($alias))
	    {
	        throw new XUndefinedException('no alias');
	    }
        $class = Core::Database()
			->createQueryForClass($this)
			->call('getClass')
			->withParameters($alias)
			->fetchSingleValue();
        if(class_exists($class, true) && ($ifIsType == null || $class == $ifIsType))
        {
            return new $class($alias);
        }
        else
        {
            throw new XInvalidDataException($class.' not found');
        }
    }

    /**
     * always returns a content
     * no exceptions
     * @param $alias
     * @return Interface_Content
     */
    public function tryOpenContent($alias, $ifIsType = null)
    {
	    try
	    {
	        return $this->openContent($alias, $ifIsType);
	    }
	    catch(XUndefinedException $e)
	    {
            return new CError(404);
	    }
	    catch(XInvalidDataException $e)
	    {
            return new CError(500);
	    }
	    catch(Exception $e)
	    {
            return new CError(404);
	    }
    }

    public function accessContent($alias, $opener, $failIfReplaced = false)
    {
        $content = $this->tryOpenContent($alias);
        $e = new Event_WillAccessContent($opener, $content);
        if($e->hasContentBeenSubstituted() && $failIfReplaced)
        {
            throw new XInvalidDataException('content replaced but exact open requested');
        }
        $content = $e->Content;
        $e = new Event_ContentAccess($opener, $content);
        return $content;
    }

    /*public function createContent($title, $class)
    {

    }*/

    public function deleteContent($alias)
    {
        try
	    {
			$succ = Core::Database()
				->createQueryForClass($this)
				->call('delete')
				->withParameters($alias)
				->execute();
	    }
	    catch (XDatabaseException $d)
	    {
	        SNotificationCenter::report('warning', 'element_is_used_by_the_system_and_cannot_be_deleted');
	        $succ = false;
	    }
	    catch (Exception $e)
	    {
	        SNotificationCenter::report('warning', 'delete_failed');
	        $succ = false;
	    }
	    return $succ;
    }

    public function contentExists($alias)
    {
		$c = Core::Database()
			->createQueryForClass($this)
			->call('exists')
			->withParameters($alias)
			->fetchSingleValue();
	    return $c == 1;
    }

    public function contentIndex($class, $titleOnly = false)
    {
		$class = $this->className($class);
		try
		{
			$res = Core::Database()
				->createQueryForClass($this)
				->call('index')
				->withParameters($class);
			$index = array();
			while ($arr = $res->fetchResult())
			{
			    list($title, $pubdate, $alias, $type, $id) = $arr;
				$index[$alias] = $titleOnly ? $title : array($title, $pubdate, $type, $id);
			}
			$res->free();
		}
		catch (Exception $e)
		{
			$index = array();
		}
		return $index;
    }

	/**
	 * guid => title
	 * @return array
	 */
	public function contentGUIDIndex($ofClass)
	{
		$ofClass = $this->className($ofClass);
	    $index = array();
		$res = Core::Database()
			->createQueryForClass($this)
			->call('contentsForClassGuid')
			->withParameters($ofClass);
	    while ($row = $res->fetchResult())
	    {
	        $index[$row[0]] = $row[1];
	    }
		$res->free();
	    return $index;
	}

	public function getContentInformationBulk(array $aliases)
	{
		$infos = array();
		$Db =  Core::Database()->createQueryForClass($this);
		foreach ($aliases as $alias){
			$res = $Db->call('getPri')
				->withParameters($alias);
			if($row = $res->fetchResult()){
				$infos[$alias] = array(
					$row[0],//title
					$row[1],//primary alias
					$row[2]//ispublic
				);
			}
			$res->free();
		}
		return $infos;
	}

	//content chains

	public function chainContentsToClass($class, array $aliases)
	{
		$class = $this->className($class);
		$i = 0;
		$Db =  Core::Database()->createQueryForClass($this);
		foreach ($aliases as $alias){
			$i += $Db->call('chainToClass')
				->withParameters($class, $alias)
				->execute();
		}
		return $i;
	}

	public function getContentsChainedToClass($class)
	{
		$class = $this->className($class);
		$list = Core::Database()
			->createQueryForClass($this)
			->call('getChainedToClass')
			->withParameters($class)
			->fetchList();
		return $list;
	}

	public function releaseContentChainsToClass($class, $aliases = null)
	{
		$class = $this->className($class);
		$Db =  Core::Database()->createQueryForClass($this);
	    if(is_array($aliases))
		{
			foreach ($aliases as $alias){
				$Db->call('unlinkContent')
					->withParameters($class, $alias)
					->execute();
			}
			return true;
		}
		if($aliases == null)
	    {
			$Db->call('unlinkClass')
				->withParameters($class)
				->execute();
	        return true;
	    }
	    return false;
	}
}
?>