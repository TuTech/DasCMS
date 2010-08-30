<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-12
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage System
 */
class STagPermissions 
    extends 
        BObject
    implements 
        HContentAccessEventHandler ,
        HWillAccessContentEventHandler  
{
	//IShareable
	const CLASS_NAME = 'STagPermissions';
	/**
	 * @var STagPermissions
	 */
	public static $sharedInstance = NULL;
	
	private static $_permCache = array();
	
	/**
	 * @return STagPermissions
	 */
	public static function getSharedInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end IShareable
    
    private function isProtected(Interface_Content $content)
    {
		$id = $content->getId();
		//check if content is protected
		if(!isset(self::$_permCache[$id]))
	    {
			//fill cache 
			self::$_permCache[$id] = array(
				//c: the count of protecting tags
				'c' => Core::Database()
					->createQueryForClass($this)
					->call('isProtected')
					->withParameters($content->getId())
					->fetchSingleValue(),
				//p: protected flag
				'p' => null
			);
		}
		//if it is protected check if the user has permission to access it
		if(self::$_permCache[$id]['p'] == null){
			if(self::$_permCache[$id]['c'] == 0){
				//not protected
				self::$_permCache[$id]['p'] = false;
			}
			else{
				//check permission for this user
				self::$_permCache[$id]['p'] =  0 < Core::Database()
						->createQueryForClass($this)
						->call('check')
						->withParameters($id, PAuthentication::getUserID(),PAuthentication::getUserID())
						->fetchSingleValue();
			}
		}
        return self::$_permCache[$id]['p'];
    }
	
	/**
	 * before accessing content this event happens
	 * we can substitute content here 
	 *
	 * @param EWillAccessContentEvent $e
	 */
	public function HandleWillAccessContentEvent(EWillAccessContentEvent $e)
	{
	    if($this->isProtected($e->Content))
	    {
	        $e->substitute(new CError(401));
	    }
	}

	/**
	 * content that will be displayed
	 * last opportunity to stop protected content 
	 *
	 * @param EContentAccessEvent $e
	 */
	public function HandleContentAccessEvent(EContentAccessEvent $e)
	{
	    if($this->isProtected($e->Content))
	    {
	        $e->Cancel();
	        throw new XPermissionDeniedException('access to content not allowed');
	    }
	}
	
	/**
	 * @return array
	 */
	public static function getProtectedTags()
	{
		return Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call('getTags')
			->withoutParameters()
			->fetchList();
	}
	
	/**
	 * @return void
	 */
	public static function setProtectedTags(array $tags)
	{
		STag::getSharedInstance()->addTags($tags);
		DSQL::getSharedInstance()->beginTransaction();
		try{
			Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('clear')
				->withoutParameters()
				->execute();
			foreach ($tags as $tag){
				Core::Database()
					->createQueryForClass(self::CLASS_NAME)
					->call('set')
					->withParameters($tag)
					->execute();
			}
			DSQL::getSharedInstance()->commit();
		}
		catch (Exception $e){
			DSQL::getSharedInstance()->rollback();
			throw $e;
		}
	}
	
	/////////
	
	/**
	 * @return void
	 */
	public static function setUserPermissions($name, array $tags)
	{
		self::dbSet('User', $name, $tags);
	}
	
	/**
	 * @return array
	 */
	public static function getUserPermissionTags($name)
	{
		return self::dbGet('getUserTags', $name);
	}
	
	/**
	 * @return void
	 */
	public static function setGroupPermissions($name, array $tags)
	{
		self::dbSet('Group', $name, $tags);
	}
	
	/**
	 * @return array
	 */
	public static function getGroupPermissionTags($name)
	{
	    return self::dbGet('getGroupTags', $name);
	}

	private static function dbGet($fx, $name){
		return Core::Database()
			->createQueryForClass(self::CLASS_NAME)
			->call($fx)
			->withParameters($name)
			->fetchList();
	}
	
	private static function dbSet($type, $name, $tags){
		DSQL::getSharedInstance()->beginTransaction();
		try{
			Core::Database()
				->createQueryForClass(self::CLASS_NAME)
				->call('clear'.$type)
				->withParameters($name)
				->execute();
			foreach ($tags as $tag){
				Core::Database()
					->createQueryForClass(self::CLASS_NAME)
					->call('set'.$type)
					->withParameters($name, $tag)
					->execute();
			}
			DSQL::getSharedInstance()->commit();
		}
		catch (Exception $e){
			DSQL::getSharedInstance()->rollback();
			throw $e;
		}
	}


}
?>