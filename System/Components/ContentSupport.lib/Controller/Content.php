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
        $class = QBContent::getClass($alias);
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
	        $succ = QBContent::deleteContent($alias);
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
		    $res = QBContent::getBasicInformationForClass($class);
			$index = array();
			while ($arr = $res->fetch())
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
		$res->close();
	    return $index;
	}

	public function getContentInformationBulk(array $aliases)
	{
	    $res = QBContent::getPrimaryAliases($aliases);
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

	    $res = QBContent::getBasicInformation($map);
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

	//content chains

	public function chainContentsToClass($class, array $aliases)
	{
	    return QBContent::chainContensToClass(is_object($class) ? get_class($class) : $class, $aliases);
	}

	public function getContentsChainedToClass($class)
	{
	    $res = QBContent::getContentsChainedToClass(is_object($class) ? get_class($class) : $class);
	    $guids = array();
	    while($row = $res->fetch())
	    {
	        $guids[$row[0]] = $row[0];
	    }
	    $res->free();
	    return $guids;
	}

	public function releaseContentChainsToClass($class, $aliases = null)
	{
	    if(is_array($aliases) || $aliases == null)
	    {
	        QBContent::releaseContensChainedToClass(is_object($class) ? get_class($class) : $class, $aliases);
	        return true;
	    }
	    return false;
	}
}
?>