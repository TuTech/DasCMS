<?php
class Controller_Content
{
    //IShareable

    /**
     * @var Controller_Content
     */
	private static $sharedInstance = null;

	/**
     * @return Controller_Content
     */
	public static function getSharedInstance()
	{
		if(self::$sharedInstance == null)
		{
			self::$sharedInstance = new Controller_Content();
		}
		return self::$sharedInstance;
	}

	//end IShareable

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

    public function accessContent($alias, BObject $opener, $failIfReplaced = false)
    {
        $content = $this->tryOpenContent($alias);
        $e = new EWillAccessContentEvent($opener, $content);
        if($e->hasContentBeenSubstituted() && $failIfReplaced)
        {
            throw new XInvalidDataException('content replaced but exact open requested');
        }
        $content = $e->Content;
        $e = new EContentAccessEvent($opener, $content);
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
		foreach ($aliases as $alias){
			$res = Core::Database()
				->createQueryForClass($this)
				->call('getPri')
				->withParameters($alias);
			if($row = $res->fetchResult()){
				$infos[$alias] = array(
					'Title' => $row[0],
					'Alias' => $row[1],//primary alias
					'PubDate' => strtotime($row[2])
				);
			}
			$res->free();
		}
		return $infos;
	}

	//content chains

	public function chainContentsToClass($class, array $aliases)
	{
		$i = 0;
		foreach ($aliases as $alias){
			$i += Core::Database()
				->createQueryForClass($this)
				->call('chainToClass')
				->withParameters($class, $alias)
				->execute();
		}
		return $i;
	}

	public function getContentsChainedToClass($class)
	{
		$list = Core::Database()
			->createQueryForClass($this)
			->call('getChainedToClass')
			->withParameters($class)
			->fetchList();
		return $list;
	}

	public function releaseContentChainsToClass($class, $aliases = null)
	{
	    if(is_array($aliases))
		{
			foreach ($aliases as $alias){
				Core::Database()
					->createQueryForClass($this)
					->call('unlinkContent')
					->withParameters($class, $alias)
					->execute();
			}
			return true;
		}
		if($aliases == null)
	    {
			Core::Database()
				->createQueryForClass($this)
				->call('unlinkClass')
				->withParameters($class)
				->execute();
	        return true;
	    }
	    return false;
	}
}
?>