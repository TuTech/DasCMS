<?php
/**
 * @package Bambus
 * @subpackage Feed
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-30
 * @license GNU General Public License 3
 */
class FRSS2 extends BFeed 
{
    private $headItems = array(
        BFeed::TITLE => 'title',
        BFeed::LINK => 'link',
        BFeed::DESCRIPTION => 'description',
        BFeed::LANGUAGE => 'language',
        BFeed::COPYRIGHT => 'copyright',
        BFeed::WEBMASTER => 'webmaster',
        BFeed::PUB_DATE => 'pubDate',
        BFeed::LAST_BUILD_DATE => 'lastBuildDate',
        BFeed::GENERATOR => 'generator',
        BFeed::TTL => 'ttl',
        BFeed::IMAGE => 'image'
    );
    private $items = array(
        BFeed::TITLE => 'title',
        BFeed::LINK => 'link',
        BFeed::DESCRIPTION => 'description',
        BFeed::AUTHOR => 'author',
        BFeed::CATEGORY => 'category',
        BFeed::ENCLOSURE => 'enclosure',
        BFeed::GUID => 'guid',
        BFeed::PUB_DATE => 'pubDate',
        BFeed::SOURCE => 'source',
    );
    /**
     * @var IGeneratesFeed
     */
    private $source;
    private $rendered = null;
    function __construct(BContent $datasource)
    {
        if (!$datasource instanceof IGeneratesFeed) 
        {
        	throw new XArgumentException('content does not provide feed data');
        }
        $this->source = $datasource;
    }

    function __toString()
	{
	    return $this->render();
	}

	private function render()
    {
        if ($this->rendered == null) 
        {
            $this->source->startFeedReading();
        	$xml = new SSimpleXMLWriter();
        	$xml->openTag('rss', array('version' => '2.0'));
        	$xml->openTag('channel');
        	//meta
        	$meta = $this->source->getFeedMetaData();
        	$meta[BFeed::GENERATOR] = BAMBUS_VERSION;
        	foreach ($meta as $key => $data) 
        	{
        		if(array_key_exists($key, $this->headItems))
        		{
        		    $data = (substr($this->headItems[$key],-4) == 'Date') 
        		        ? date('r', is_numeric($data) ? $data : strtotime($data)) 
        		        : $data;
        		    $xml->tag($this->headItems[$key],array(),$data);
        		}
        	}
        	//items
        	while($this->source->hasMoreFeedItems())
        	{
        	    $item = $this->source->getFeedItemData();
        	    $xml->openTag('item');
            	foreach ($item as $key => $data) 
            	{
            		if(array_key_exists($key, $this->items))
            		{
            		    $att = array();
            		    switch ($key) 
            		    {
            		    	case BFeed::PUB_DATE:
            		    		$data =  date('r', is_numeric($data) ? $data : strtotime($data)); 
            		    		break;
            		    	case BFeed::ENCLOSURE:
            		    	    list($d, $a) = $data;
            		    	    $data = $d;
            		    	    @$att['url'] = $a[BFeed::URL]; 
            		    	    @$att['type'] = $a[BFeed::TYPE]; 
            		    	    @$att['length'] = $a[BFeed::LENGTH];
            		    	    break; 
            		    	case BFeed::SOURCE:
            		    	    list($d, $a) = $data;
            		    	    $data = $d;
            		    	    @$att['url'] = $a[BFeed::URL]; 
            		    	default:
            		    	break;
            		    }
            		    $xml->tag($this->items[$key],$att,$data);
            		}
            	}
        	    $xml->closeTag();//item
        	}
        	$xml->closeTag();//channel
        	$xml->closeTag();//rss
        	$this->source->finishFeedReading();
        	$this->rendered = strval($xml);
        }
        return $this->rendered;
    }
}
?>