<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 30.08.2006
 * @license GNU General Public License 3
 */
class RSS extends Bambus implements IShareable
{
	//IShareable
	const Class_Name = 'RSS';
	public static $sharedInstance = NULL;
	private $initializedInstance = false;
	public static function alloc()
	{
		$class = self::Class_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
    
    function init()
    {
    	if(!$this->initializedInstance)
    	{
    		if(defined('BAMBUS_DEBUG'))printf("\n<!--[%s init]-->\n", self::Class_Name);
	    	$this->initializedInstance = true;
    	}
    }
	//end IShareable

    var $get,$post,$session;
    var $notifications = array();
	var $template;
	var $feed;
	var $itemOpen = false;
    var $inItemTag;
    var $channelTag;
    
    var $recode = true;
    
//////////////////////////////////////////////////////////////
//// Functions required by Bambus
//////////////////////////////////////////////////////////////


    function __construct()
    {
        parent::Bambus();
        $type = 'rss2';
		$template = &$this->template;
		$feed = &$this->feed;
        $channelTag = &$this->channelTag;
        $inItemTag = &$this->inItemTag;

		$templates['rss2']['head'] ="<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<rss version=\"2.0\">\n\t<channel>\n";
		$templates['rss2']['footer'] = "\t</channel>\n</rss>";
		$templates['rss2']['openItem'] = "\t\t<item>\n";
		$templates['rss2']['closeItem'] = "\t\t</item>\n";
        $inItemTags['rss2'] = array('title', 'link', 'description', 'author', 'category', 'comments', 'enclosure', 'guid', 'pubDate', 'source');
        $channelTags['rss2'] = array('title', 'link', 'description', 'language', 'copyright', 'managingEditor', 'webMaster', 'pubDate', 'lastBuildDate', 'category', 'generator', 'docs', 'cloud', 'ttl', 'image', 'textInput', 'skipHours', 'skipDays');

		$template = $templates[$type];
		$feed = $template['head'];
        $channelTag = $channelTags[$type];
        $inItemTag = $inItemTags[$type];
    }

//////////////////////////////////////////////////////////////
//// Class functions
//////////////////////////////////////////////////////////////
    public function __sleep()
    {
    	//do not serialize
    	return array();
    }
    	
	function tagCheck($tag)
	{
        if($this->itemOpen)
        {
            $tags = $this->inItemTag;
        }
        else
        {
            $tags = $this->channelTag;
        }
        return in_array($tag, $tags);
    }
    
	function encodeString($string, $recode = true)
	{
		$recode = $recode && $this->recode;
        return $recode ? utf8_encode(htmlspecialchars($string)) : htmlspecialchars($string, ENT_NOQUOTES, 'UTF-8');
    }

	function addTag($tag, $string, $attributes = array())
	{
		$feed = &$this->feed;
		$atts = '';
		foreach($attributes as $key => $value)
		{
			$atts .= ' '.$this->encodeString($key).'="'.$this->encodeString($value).'"';
		}
		
		if($this->tagCheck($tag))
		{
			if($string === false)
			{
				$feed .= (($this->itemOpen) ? "\t" : "").
					"\t\t<".$tag.$atts." />\n";
			}
			else
			{
				$feed .= (($this->itemOpen) ? "\t" : "").
					"\t\t<".$tag.$atts.'>'.$this->encodeString($string).'</'.$tag.">\n";
			}
		}
	}

	function newItem()
	{
		$feed = &$this->feed;
		$template = &$this->template;
		if($this->itemOpen)
		{
			$feed .= $template['closeItem'];
		}
		$feed .= $template['openItem'];
		$this->itemOpen = true;
	}

	function show()
	{
		$feed = &$this->feed;
		$template = &$this->template;
		if($this->itemOpen)
		{
			$feed .= $template['closeItem'];
		}
		$feed .= $template['footer'];
		return $feed;
	}
}
?>
