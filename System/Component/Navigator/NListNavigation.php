<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 30.04.2008
 * @license GNU General Public License 3
 */
class NListNavigation extends BNavigation implements IShareable, IUseSQLite 
{
	public function navigateWith($tagstring)
	{
		$tags = STag::parseTagStr($tagstring);
		$html = '';
		$spore = null;
		if(count($tags) > 0)
		{
			$sporename = array_shift($tags);
			if(QSpore::exists($sporename))
			{
				$spore = QSpore::byName($sporename);
			}
		}
		$tagfilter = '';
		foreach ($tags as $tag) 
		{
			$tagfilter .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
				"WHERE Tags.tag LIKE '".sqlite_escape_string($tag)."') ";
		}
		$connection = DSQLite::alloc()->init();
		$managers = SComponentIndex::alloc()->init()->ExtensionsOf('BContentManager');
		$sql = "SELECT Aliases.alias, ContentIndex.title, Managers.manager, ContentIndex.pubDate AS PubDate ".
				"FROM Aliases ".
				"LEFT JOIN ContentIndex ON (ContentIndex.contentID = Aliases.contentREL)".
				"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
				"WHERE Aliases.active = 1 ".
				"AND PubDate > 0 ".
				"AND PubDate <= ".time().
				$tagfilter.
				" ORDER BY Title ASC";
		$res = $connection->query($sql, SQLITE_NUM);
		$rows = $res->numRows();
			
		$html .= '<ul class="NListNavigation">';
		$html .= '<span class="NavigationItemCount">'.$rows.'</span>';
		
		$lastMan = null;
		while($erg = $res->fetch())
		{
			list($alias, $ttl, $man, $pub) = $erg;
			$html .= '<li class="NavigationObject">';
			if($spore !== null) $html .= "\n<a href=\"".$spore->LinkTo($alias)."\">";
			$html .= '<span class="NListNavigation-Manager-'.htmlentities($man, ENT_QUOTES, 'UTF-8')
					.'">'.htmlentities($ttl, ENT_QUOTES, 'UTF-8').'</span>';
			if($spore !== null) $html .= "</a>\n";
			$html .= "</li>\n";
		}
		$html .= '</ul>';
		return $html;
	}
	
	
	
	
	
	//IShareable
	const Class_Name = 'NListNavigation';
	public static $sharedInstance = NULL;
	/**
	 * @return NListNavigator
	 */
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
	/**
	 * @return NListNavigator
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable
}
?>