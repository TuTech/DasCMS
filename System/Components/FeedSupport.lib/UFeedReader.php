<?php
/************************************************
* Bambus CMS
* Created:     30.07.2007
* License:     GNU GPL Version 2 or later (http://www.gnu.org/copyleft/gpl.html)
* Copyright:   Lutz Selke/TuTech Innovation GmbH
* Description: FeedReader.php
* PHP Version: 5
*************************************************/
class UFeedReader extends BPlugin implements IShareable, ITemplateSupporter, IGlobalUniqueId
{
    //IGUID
    const GUID = 'org.bambuscms.plugins.feedreader';
    public function getClassGUID()
    {
        return self::GUID;
    }
    //ItemplateSupporter
        /**
     * return an array with function => array(0..n => parameters [, 'description' =>  desc])
     *
     * @return array
     */
    public function templateProvidedFunctions()
    {
        return array(
        	'embed' => array(
        			 'feed'
        			,'rows'
        			,'update'
        			,'titleTag'
        			,'textTag'
        			,'linkTarget'
        			,'textLength'
        			,'link'
        			,'alert'
    		)
		);
    }

    /**
     * return an array with attributeName => description
     *
     * @return array
     */
    public function templateProvidedAttributes()
    {return array();}

	/**
	 * check function availability and permissions
	 *
	 * @param string $function
	 * @return boolean
	 */
	public function templateCallable($function)
	{
	    return $function == 'embed';
	}

	/**
	 * Call a function from this object
	 *
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 *
	 * @param string $function
	 * @param array $namedParameters
	 * @return string
	 */
	public function templateCall($function, array $namedParameters)
	{
	    SErrorAndExceptionHandler::muteErrors();
	    $val = $this->embed($namedParameters);
	    SErrorAndExceptionHandler::reportErrors();
	    return $val;
	}

	/**
	 * Get a property from an object
	 * return in proper format (e.g format date as set in config)
	 *
	 * USE UTF-8 ENCODING FOR RETURN VALUES
	 *
	 * @param string $property
	 * @return string
	 */
	public function templateGet($property)
	{
	    return '';
	}

	//shareable
	const Plugin_Name = 'UFeedReader';
	public static $sharedInstance = NULL;
	public static function getInstance()
	{
		$class = self::Plugin_Name;
		if(self::$sharedInstance == NULL && $class != NULL)
		{
			self::$sharedInstance = new $class();
		}
		return self::$sharedInstance;
	}
	//end shareable

	const FEED_MAX_SIZE = 4000000;
	private $alertOnError = false;
	private $errorMessage = '';
	private $emptyXML = '<?xml version="1.0" ?><error>Could not load feed</error>';

	private function log($msg, $_ = '')
	{
	    //$args = func_get_args();
	    //$format = array_shift($args);
	    //vprintf($format, $args);
	}

	//get feed from server or use cached version
	private function loadResource($url, $minutesToLive = 30)
	{
		$temp = SPath::TEMP;
		$feedId = 'FeedReader_'.md5($url);
		if(file_exists($temp.$feedId) && filemtime($temp.$feedId) > (time() - $minutesToLive*60))
		{
			//cached version exists
			$data = DFileSystem::load($temp.$feedId);
			$this->log("\n<!--Feed Reader: using cached file '%s' for '%s' -->\n",$feedId,$url);
		}
		else
		{
			//load data from url...
			$st = microtime(true);
			$readData = 0;
			SErrorAndExceptionHandler::muteErrors();
			try
			{
				$data = '';
				if($fd = fopen($url, 'r'))
				{
					while($buf = fread($fd, 1024))
					{
						//don't flood the ram
						$readData += 1024;
						if($readData >= self::FEED_MAX_SIZE)
						{
							$this->errorMessage = sprintf(
								"Feed '%s' exceeded max feed size: %s \n" .
									"Bambus install: %s\n"
								,$url
								,$this->maxFeedSize
								,SLink::base()
							);
							$data = null;
							break;
						}
						$data .= $buf;
					}
					if($data !== null){
						DFileSystem::save($temp.$feedId, $data);
					}
				}
				else
				{
					$data = null;
				}
				$et = microtime(true);
				$rt = $et-$st;
				$this->log("\n<!--Feed Reader: downloaded data from '%s' with %s in %1.2fsec  -->\n",$url, strlen($data), $rt);
			}
			catch(Exception $e)
			{
				$this->errorMessage = sprintf(
					"Feed '%s' reading raised exception: %s \n" .
						"Bambus install: %s\n"
					,$url
					,$e
					,SLink::base()
				);
				$data = null;
			}
		}
		SErrorAndExceptionHandler::reportErrors();
		//wherever it came from - we've got our data
		return empty($data) ? $this->emptyXML : $data;
	}

	//display a feed specified by url
	public function embed($args)
	{
		//init
		$defaults = array(
			 'feed' => ''
			,'rows' => 5//0 for all items
			,'update' => 30 //0 for allways
			,'titleTag' => 'h3'
			,'textTag' => 'p'
			,'linkTarget' => '_self'
			,'textLength' => 30 //0 for complete text
			,'link' => 'title'// title|all|none|text
			,'alert' => '' //mail addr to alert on error - this will create massive spam
		);
		$options = array();
		$keys = array_keys($defaults);
		$html = '';
		//add error notification
		if(!empty($args['alert']))
			$this->alertOnError = $args['alert'];

		//validate input
		if(empty($args['feed']))
			return ' <b>ERROR IN: '.__CLASS__.'::'.__FUNCTION__.'() - no feed specified</b> ';
		for($i = 0; $i < count($keys); $i++)
		{
			if(!isset($args[$keys[$i]]))
			{
				$options[$keys[$i]] = $defaults[$keys[$i]];
				continue;
			}
			switch($keys[$i])
			{
				case 'rows':
				case 'update':
				case 'textLength':
					$options[$keys[$i]] = (is_numeric($args[$keys[$i]])) ? $args[$keys[$i]] : $defaults[$keys[$i]];
					break;
				case 'link':
					$options[$keys[$i]] = (in_array($args[$keys[$i]], array('title','all','none','text'))) ? $args[$keys[$i]] : $defaults[$keys[$i]];
					break;
				default:
					$options[$keys[$i]] = $args[$keys[$i]];
			}
		}

		//gereate a not feed but call independent id for the feed
		$feedId = 'FeedReader_'.md5(implode('#',$options)).'.html';
		$temp = SPath::TEMP;
		if(file_exists($temp.$feedId) && filemtime($temp.$feedId) > (time() -  $options['update']*60))
		{
			//cached version exists
			$html = DFileSystem::load($temp.$feedId);
			$this->log("\n<!--Feed Reader: using cached html '%s' for '%s' -->\n",$feedId,$options['feed']);

		}
		if(empty($html))
		{
			$st = microtime(true);
			//get feed file
			try
			{
				$dom = new DomDocument();
				if(!@$dom->loadXML($this->loadResource($options['feed'], $options['update'])))
				{
					//the feed xml is broken - use the last generated version from cache or return an empty string
					if(file_exists($temp.$feedId))
					{
						//cached version exists
						$html = DFileSystem::load($temp.$feedId);
						$this->log("\n<!--Feed Reader: could not read feed - using cached html '%s' for '%s' -->\n",$feedId,$options['feed']);

					}
					return $html;
				}
				//printf('<h1>%s</h1>', $dom->encoding);

				//feed for atom / rss for er... rss / rdf for .. you know
				$rootElement = $dom->documentElement->tagName;

				$readItems = 0;

				$outArr = array();
				if($rootElement == 'feed')
				{
					//atom
					$feedname = '';
					$feedlink = '';
					$sureWithLink = false;
					foreach($dom->documentElement->childNodes as $childNode)
					{
						//get feed title
						if($childNode->nodeName == 'title')
							$feedname = $childNode->nodeValue;
						//get feed link
						if($childNode->nodeName == 'link' && $childNode->hasAttribute('href') && !$sureWithLink)
							$feedlink = $childNode->getAttribute('href');

						//atom feeds can have multiple links - use any link until all these attributes match
						if($childNode->nodeName == 'link'
							&& $childNode->hasAttribute('href')
							&& $childNode->hasAttribute('rel')
							&& $childNode->hasAttribute('type')
							&& $childNode->getAttribute('rel') == 'alternate'
							&& $childNode->getAttribute('type') == 'text/html'
						)
							$sureWithLink = true;

						//exit foreach if everything looks good
						if(!empty($feedname) && !empty($feedlink) && $sureWithLink)
							break;
					}

					//walk through entries
					$entries = $dom->getElementsByTagname('entry');
					foreach($entries as $entry)
					{
						if(!is_object($entry))
							continue;
						$title = '';
						$link = '';
						$text = '';
						$sureWithLink = false;

						$nodeList = $entry->getElementsByTagname('title');
						$title = $nodeList->item(0)->nodeValue;

						//find link(s)
						$nodeList = $entry->getElementsByTagname('link');
						foreach($nodeList as $node)
						{
							if($node->hasAttribute('href') && !$sureWithLink)
							{
								$link = $node->getAttribute('href');
								if($node->hasAttribute('rel')
									&& $node->hasAttribute('type')
									&& $node->getAttribute('rel') == 'alternate'
									&& $node->getAttribute('type') == 'text/html'
								)
									$sureWithLink = true;
							}
						}

						//take text from summary
						$nodeList = $entry->getElementsByTagname('summary');
						if($nodeList->length > 0)
							$text = $nodeList->item(0)->nodeValue;

						//overwrite summary text if there is some content
						$nodeList = $entry->getElementsByTagname('content');
						if($nodeList->length > 0)
							$text = $nodeList->item(0)->nodeValue;

						$outArr[] = array($title, $link, $text);

						//i've heard enough
						if($readItems++ >= (int)$options['rows'])
							break;
					}
				}
				else
				{
					//rss/rdf
					$feedname = '';
					$feedlink = '';
					$channel = $dom->documentElement->getElementsByTagname('channel')->item(0);
					if(!empty($channel) && !empty($channel->childNodes))
					{
						foreach($channel->childNodes as $childNode)
						{
							if($childNode->nodeName == 'title')
								$feedname = $childNode->nodeValue;
							if($childNode->nodeName == 'link')
								$feedlink = $childNode->nodeValue;
							if(!empty($feedname) && !empty($feedlink))
								break;
						}
					}
					$entries = $dom->getElementsByTagname('item');
					if(!empty($entries))
					{
						foreach($entries as $entry)
						{
							$title = '';
							$link = '';
							$text = '';

							$nodeList = $entry->getElementsByTagname('title');
							if($nodeList->length > 0)
								$title = $nodeList->item(0)->nodeValue;

							$nodeList = $entry->getElementsByTagname('link');
							if($nodeList->length > 0)
								$link = $nodeList->item(0)->nodeValue;

							$nodeList = $entry->getElementsByTagname('description');
							if($nodeList->length > 0)
								$text = $nodeList->item(0)->nodeValue;
							$outArr[] = array($title, $link, $text);

							//i've heard enough
							if($readItems++ >= (int)$options['rows'])
								break;
						}
					}
				}

				//build html
				$dom->encoding = 'UTF-8';
				$encode = (strtoupper($dom->encoding) != 'UTF-8');
				$html = '<div class="FeedReader">';//.$dom->encoding.($encode ? ' recoding' : ' is ok');
				if(!empty($feedname))
					$html .= sprintf(
						"\n\t<a href=\"%s\" target=\"%s\">\n\t\t<%s class=\"FeedReaderTitle\">%s</%s>\n\t</a>"
						,$feedlink
						,$options['linkTarget']
						,$options['titleTag']
						,$feedname
						,$options['titleTag']);
				$html .= "\n\t<div class=\"FeedReaderItems\">";
				for($e = 0; $e < count($outArr) && $e < $options['rows']; $e++)
				{
					$html .= sprintf(
						"\n\t\t<div class=\"FeedReaderItem\">\n\t\t\t<a class=\"FeedReaderItemLink\" href=\"%s\" target=\"%s\">%s</a>"
						,$outArr[$e][1]/*($encode) ? ($outArr[$e][1]) : */
						,$options['linkTarget']
						,$outArr[$e][0]/*($encode) ? ($outArr[$e][0]) : */
					);
					if(!empty($outArr[$e][2]))
						$html .= sprintf(
							"\n\t\t\t<%s class=\"FeedReaderItemText\">%s</%s>"
							,$options['textTag']
							,$outArr[$e][2]/*($encode) ? ($outArr[$e][2]) : */
							,$options['textTag']
						);
					$html .= "\n\t\t</div>";
				}
				$html .= "\n\t</div>\n</div>\n";
				DFileSystem::save($temp.$feedId, $html);
			}
			catch(Exception $e)
			{
				$this->errorMessage = sprintf(
					"generating Feed '%s' raised exception: %s \n" .
						"Bambus install: %s\n"
					,$args['feed']
					,$e
					,SLink::base()
				);
				return '';
			}
			$et = microtime(true);
			$rt = $et-$st;
			$this->log("\n<!--Feed Reader: rendered '%s' (%s) in %1.2fsec  -->\n",$args['feed'], $rootElement, $rt);
		}
		return  $html;
	}
}
?>