<?php
/**
 * @package Bambus
 * @subpackage Navigators
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 30.04.2008
 * @license GNU General Public License 3
 */
class NListNavigation 
    extends 
        BNavigation 
    implements 
        IShareable, 
        ITemplateSupporter, 
        IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.navigation.listnavigation';
    
    public function getGUID()
    {
        return self::GUID;
    }
    
    public static function navigateWith($tagstring)
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
		
		$DB = DSQL::alloc()->init();
		try
		{
			foreach ($tags as $tag) 
			{
				$tagfilter .= "AND ContentIndex.contentID IN (SELECT relContentTags.contentREL FROM relContentTags LEFT JOIN Tags ON (Tags.tagID = relContentTags.tagREL) ".
					"WHERE Tags.tag = '".$DB->escape($tag)."') ";
			}
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
			$res = $DB->query($sql, DSQL::NUM);
				
			$html .= '<ul class="NListNavigation">';
			$html .= '<span class="NavigationItemCount">'.$res->getRowCount().'</span>';
			
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
			$res->free();
		}
		catch(Exception $e)
		{
			$html = '';
		}
		return $html;
	}
	
	
	
	
	
	//IShareable
	const CLASS_NAME = 'NListNavigation';
	public static $sharedInstance = NULL;
	/**
	 * @return NListNavigation
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
	 * @return NListNavigation
	 */
	function init()
    {
    	return $this;
    }
	//end IShareable


/////////////////////////////////

    /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function TemplateProvidedFunctions()
    {
        return array('embed' => array('description' => 'all parameter values will be combined to the tag filter'));
    }
    
    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function TemplateProvidedAttributes()
    {
        return array();
    }

    /**
	 * @param string $function
	 * @return boolean
	 */
	public function TemplateCallable($function)
	{
	    return $function == 'embed';
	}
	
	/**
	 * @param string $function
	 * @param array $namedParameters
	 * @return string in utf-8
	 */
	public function TemplateCall($function, array $namedParameters)
	{
	    if(!$this->TemplateCallable($function))
	    {
	        throw new XTemplateException('called undefined function');
	    }
	    $tags = implode(',', $namedParameters);
	    
        return self::navigatieWith($tags);
	}
	
	/**
	 * @param string $property
	 * @return string in utf-8
	 */
	public function TemplateGet($property)
	{
	    return '';
	}
}
?>