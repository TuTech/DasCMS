<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 29.04.2008
 * @license GNU General Public License 3
 */
class WContentLookup extends BWidget implements ISidebarWidget , IUseSQLite 
{
	private $managers = array();
	/**
	 * get category of this widget
	 * @return string
	 */
	public function getCategory()
	{
		return 'Content';
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return 'Content Index';
	}
	/**
	 * get an array of string of all supported classes 
	 * if it supports BObject, it supports all cms classes
	 * @return array
	 */
	public function supportsObject($object)
	{
		return true;
	}
	
	public function __construct($target = null)
	{
		//build content list
//		$SCI = SComponentIndex::alloc()->init();
//		$this->managers = $SCI->ExtensionsOf('BContentManager');
	}
	
	public function __toString()
	{
		$html = '<strong>All Contents</strong>';
		$html .= '<div id="WCLSearchBox"><input type="text" id="WContentLookupFilter" onchange="WCLFilter();" onkeyup="WCLFilter();" /></div>';
		$connection = DSQLite::alloc()->init();
		$managers = SComponentIndex::alloc()->init()->ExtensionsOf('BContentManager');
		$sql = "SELECT ContentIndex.managerContentID, Managers.manager AS Manager,".
				"ContentIndex.title AS Title, ContentIndex.pubDate ".
				"FROM ContentIndex ".
				"LEFT JOIN Managers ON (ContentIndex.managerREL = Managers.managerID) ".
				"WHERE ContentIndex.pubDate > -1  ORDER BY Manager,Title ASC";
		$res = $connection->query($sql, SQLITE_NUM);
		$rows = $res->numRows()+count($managers);
			
		$html .= '<select id="WContentLookup" onclick="insertMedia(\'content\', this.options[this.selectedIndex].value, this.options[this.selectedIndex].text)" size="'.$rows.'">';
		
		$lastMan = null;
		while($erg = $res->fetch())
		{
			list($cid, $man, $ttl, $pub) = $erg;
			if($man != $lastMan)
			{
				if($lastMan != null)  $html .= '</optgroup>';
				$html .= '<optgroup label="'. substr($man,1).'">';
				$lastMan = $man;
			}
			$class = 'unpublished';
			$title = 'Not public';
			if($pub > 0){
				$class = 'published';
				$title = 'Published: '.date('r',$pub);
			}
			if($pub > time()){
				$class = 'publicationScheduled';
				$title = 'Not yet public ('.date('r',$pub).')';
			}
			$html .= '<option value="'.$man.':'.$cid.'" class="'.$class.'" title="'.$title.'">'.
							htmlentities($ttl, ENT_QUOTES, 'UTF-8')
					.'</option>';
		}
		if($lastMan != null)  $html .= '</optgroup>';
		
		$html .= '</select>';
		return $html;
	}
}
?>