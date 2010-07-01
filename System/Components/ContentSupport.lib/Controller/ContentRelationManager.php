<?php
class Controller_ContentRelationManager
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
		QControllerContentRelationManager::createChain($this->resolveClass($byClass), $forOwnerContent, $content);
		return $this->hasContent($content, $forOwnerContent, $byClass);
	}

	/**
	 * get a list of aliases of all contents retained for this class
	 * @param mixed> $class
	 */
	public function getAllRetainedByClass($class){
		return $this->fetchList(QControllerContentRelationManager::getAllChainedToClass($this->resolveClass($class)));
	}

	/**
	 * get a list of aliases of all contents retained by this pair of content and class
	 * @param string $ownerContent
	 * @param mixed $class
	 */
	public function getAllRetainedByContentAndClass($ownerContent, $class){
		return $this->fetchList(QControllerContentRelationManager::getAllChainedToClassAndContent($this->resolveClass($class), $ownerContent));
	}

	/**
	 * get a list of all classes retaining this content
	 * @param string $content
	 */
	public function getClassesRetaining($content){
		return $this->fetchList(QControllerContentRelationManager::getClassesChaining($content));
	}

	/**
	 * get a list of all contents retaining this content
	 * @param string $content
	 */
	public function getRetainees($content){
		return $this->fetchList(QControllerContentRelationManager::getContentsChaining($content));
	}

	/**
	 * get the retain count for this content
	 * @param string $content 
	 */
	public function getRetainCount($content){
		return $this->fetchSingleValue(QControllerContentRelationManager::getRetainCount($content));
	}

	/**
	 * check if content is retained
	 * optional parameters to specify owning content and class
	 * @param string $content
	 * @param string $forOwner
	 * @param mixed $andClass
	 */
	public function hasContent($content, $forOwner = null, $andClass = null){
		return !!$this->fetchSingleValue(QControllerContentRelationManager::isRetained($content, $forOwner, $this->resolveClass($andClass)));
	}

	/**
	 * remove all retains requested by this class
	 * @param mixed $class
	 */
	public function releaseAllRetainedByClass($class){
		return QControllerContentRelationManager::deleteClassChains($this->resolveClass($class));
	}

	/**
	 * release all contents for this class and owner pair
	 * @param mixed $class
	 * @param string $owner
	 */
	public function releaseAllRetainedByContentAndClass($class, $owner){
		return QControllerContentRelationManager::deleteClassChainsForOwner($this->resolveClass($class), $owner);
	}

	/**
	 * release all contents retained by any class for the owner content
	 * @param string $ownerContent
	 */
	public function releaseAllRetainedByContent($ownerContent){
		return QControllerContentRelationManager::deleteOwnerChains($ownerContent);
	}

	/**
	 * release a specific retain
	 *
	 * @param string $content
	 * @param string $forOwner
	 * @param mixed $andClass
	 */
	public function release($content, $forOwner, $andClass){
		return QControllerContentRelationManager::deleteChain($this->resolveClass($andClass), $forOwner, $content);
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

	protected function fetchList(DSQLResult $res){
		$list = array();
		while ($row = $res->fetch()){
			$list[] = $row[0];
		}
		$res->free();
		return $list;
	}

	protected function fetchSingleValue(DSQLResult $res){
		$ret = null;
		if($res->getRowCount() > 0){
			list($ret) = $res->fetch();
		}
		return $ret;
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
