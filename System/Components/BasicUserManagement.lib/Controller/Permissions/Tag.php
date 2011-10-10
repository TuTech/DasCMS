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
class Controller_Permissions_Tag
    implements 
        Event_Handler_ContentAccess ,
        Event_Handler_WillAccessContent,
		Interface_Singleton
{
	//Interface_Singleton
	const CLASS_NAME = 'Controller_Permissions_Tag';
	/**
	 * @var Controller_Permissions_Tag
	 */
	public static $sharedInstance = NULL;
	
	private static $_permCache = array();
	
	/**
	 * @return Controller_Permissions_Tag
	 */
	public static function getInstance()
	{
		$class = self::CLASS_NAME;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end Interface_Singleton
    
    private function isProtected(Interface_Content $content)
    {
		//CError is not in the database and would break this
		if($content instanceof CError)return false;

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
	 * @param Event_WillAccessContent $e
	 */
	public function handleEventWillAccessContent(Event_WillAccessContent $e)
	{
	    if($this->isProtected($e->getContent()))
	    {
	        $e->substitute(new CError(401));
	    }
	}

	/**
	 * content that will be displayed
	 * last opportunity to stop protected content 
	 *
	 * @param Event_ContentAccess $e
	 */
	public function handleEventContentAccess(Event_ContentAccess $e)
	{
	    if($this->isProtected($e->getContent()))
	    {
	        $e->cancel();
	        throw new AccessDeniedException('access to content not allowed');
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
		Controller_Tags::getInstance()->addTags($tags);
		$Db = Core::Database()->createQueryForClass(self::CLASS_NAME);
		$Db->beginTransaction();
		try{
			$Db->call('clear')
				->withoutParameters()
				->execute();
			foreach ($tags as $tag){
				$Db->call('set')
					->withParameters($tag)
					->execute();
			}
			$Db->commitTransaction();
			$e = new Event_PermissionsChanged(Controller_Permissions_Tag::getInstance());
		}
		catch (Exception $e){
			$Db->rollbackTransaction();
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
		$Db = Core::Database()->createQueryForClass(self::CLASS_NAME);
		$Db->beginTransaction();
		try{
			$Db->call('clear'.$type)
				->withParameters($name)
				->execute();
			foreach ($tags as $tag){
				$Db->call('set'.$type)
					->withParameters($name, $tag)
					->execute();
			}
			$Db->commitTransaction();
			$e = new Event_PermissionsChanged(Controller_Permissions_Tag::getInstance());
		}
		catch (Exception $e){
			$Db->rollbackTransaction();
			throw $e;
		}
	}
}
?>