<?php
class Controller_ContentRelationManager implements IShareable
{
	/**
	 * singleton instance
	 * @var Controller_ContentRelationManager
	 */
	private static $instance = null;

	/**
	 * retain content
	 * a retained content can't be deleted until it is released
	 *
	 * @param string $content
	 * @param string $forOwnerContent
	 * @param mixed $byClass
	 * @throws Exception
	 * @return bool
	 */
	public function retain($content, $forOwnerContent, $byClass){
		if(SAlias::match($content, $forOwnerContent)){
			throw new Exception("a content can't retain itself");
		}
		Core::Database()
			->createQueryForClass($this)
			->call('createChain')
			->withParameters($this->resolveClass($byClass), $forOwnerContent, $content)
			->execute();
		return $this->hasContent($content, $forOwnerContent, $byClass);
	}

	/**
	 * get a list of aliases of all contents retained for this class
	 * @param mixed> $class
	 */
	public function getAllRetainedByClass($class){
		return Core::Database()
			->createQueryForClass($this)
			->call('getAllChainedToClass')
			->withParameters($this->resolveClass($class))
			->fetchList();
	}

	/**
	 * get a list of aliases of all contents retained by this pair of content and class
	 * @param string $ownerContent
	 * @param mixed $class
	 */
	public function getAllRetainedByContentAndClass($ownerContent, $class){
		return Core::Database()
			->createQueryForClass($this)
			->call('getAllChainedToClassAndContent')
			->withParameters($ownerContent, $this->resolveClass($class))
			->fetchList();
	}

	/**
	 * get a list of all classes retaining this content
	 * @param string $content
	 */
	public function getClassesRetaining($content){
		return Core::Database()
			->createQueryForClass($this)
			->call('getClassesChaining')
			->withParameters($content)
			->fetchList();
	}

	/**
	 * get a list of all contents retaining this content
	 * @param string $content
	 */
	public function getRetainees($content, $verbose = false){
		$res =  Core::Database()
			->createQueryForClass($this)
			->call('getContentsChaining')
			->withParameters($content);
		if($verbose){
			return $res->fetchList();
		}
		else{
			$ret = array();
			while ($row = $res->fetchResult()){
				$ret[] = $row[0];
			}
			return $ret;
		}
	}

	/**
	 * get the retain count for this content
	 * @param string $content 
	 */
	public function getRetainCount($content){
		return Core::Database()
			->createQueryForClass($this)
			->call('getRetainCount')
			->withParameters($content)
			->fetchSingleValue();
	}

	/**
	 * check if content is retained
	 * optional parameters to specify owning content and class
	 * @param string $content
	 * @param string $forOwner
	 * @param mixed $andClass
	 */
	public function hasContent($content, $forOwner = null, $andClass = null){
		$qry = Core::Database()
			->createQueryForClass($this);
		if($forOwner == null && $andClass == null){
			$qry = $qry->call('isRetained')
				->withParameters($content);
		}
		elseif($forOwner == null){
			$qry = $qry->call('isRetainedClass')
				->withParameters($content, $this->resolveClass($andClass));
		}
		elseif($andClass == null){
			$qry = $qry->call('isRetainedOwner')
				->withParameters($content, $forOwner);
		}
		else{
			$qry = $qry->call('isRetainedOwnerClass')
				->withParameters($content, $forOwner, $this->resolveClass($andClass));
		}
		//get count and convert it to bool
		return !!$qry->fetchSingleValue();
	}

	/**
	 * remove all retains requested by this class
	 * @param mixed $class
	 */
	public function releaseAllRetainedByClass($class){
		return Core::Database()
			->createQueryForClass($this)
			->call('deleteClassChains')
			->withParameters($this->resolveClass($class))
			->execute();
	}

	/**
	 * release all contents for this class and owner pair
	 * @param mixed $class
	 * @param string $owner
	 */
	public function releaseAllRetainedByContentAndClass($owner, $class){
		return Core::Database()
			->createQueryForClass($this)
			->call('deleteClassChainsForOwner')
			->withParameters($this->resolveClass($class), $owner)
			->execute();
	}

	/**
	 * release all contents retained by any class for the owner content
	 * @param string $ownerContent
	 */
	public function releaseAllRetainedByContent($ownerContent){
		return Core::Database()
			->createQueryForClass($this)
			->call('deleteOwnerChains')
			->withParameters($ownerContent)
			->execute();
	}

	/**
	 * release a specific retain
	 *
	 * @param string $content
	 * @param string $forOwner
	 * @param mixed $andClass
	 */
	public function release($content, $forOwner, $andClass){
		return Core::Database()
			->createQueryForClass($this)
			->call('deleteChain')
			->withParameters($this->resolveClass($andClass), $forOwner, $content)
			->execute();
	}

	/**
	 * resolve class-name or object to class-name
	 * @param mixed $class
	 * @return string
	 */
	protected function resolveClass($class){
		if(is_object($class)){
			$class = get_class($class);
		}
		if(!class_exists($class, true)){
			throw new XUndefinedException("class not found");
		}
		return $class;
	}

	//singleton functions

	/**
	 * @return Controller_ContentRelationManager
	 */
	public static function getInstance()
	{
		if(self::$instance === null){
			self::$instance = new Controller_ContentRelationManager();
		}
		return self::$instance;
	}
	private function  __construct() {}
	private function  __clone() {}
}
?>
