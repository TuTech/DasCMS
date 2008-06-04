<?php
/**
 * @package Bambus
 * @subpackage Deprecated 
 * @deprecated 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 13.08.2007
 * @license GNU General Public License 3
 */
class Pages_Page extends BObject implements ISupportsSidebar 
{
	//Property dummys - handled in __get & __set
	private $Id, $Title, $Title_ISO, $Content, $Content_ISO, $Tags = null,
			$Text, $Text_ISO, $Type, $Meta, $MetaUpdated, $Modified, $FileName;

	private $id = 0;
	private $title = '';
	private $content = '';
	private $type = 'HTML';
	private $meta = array();
	private $modflag = false;
	private $metamodflag = false;
	private $contentLoaded = false;
	
	private $bdfstring = '<?php /*BambusDocumentFile1*/ if(!class_exists("Bambus"))exit();?>';
	private $availableTypes = array('HTML', 'PHP');
	
	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('text', 'media', 'settings', 'information', 'search'));
	}
	
	public function __construct($id, $title = false, $content = NULL, $type = 'HTML', $meta = array())
	{
		if(empty($id))
		{
			//new doc - set title on create
			if(!$title)
			{
				//default name: new_page
				$tr = Translation::alloc();
				$tr->init();
				$this->title = $tr->sayThis('new_page');
			}
			else
			{
				$this->title = $title;
			}
			$this->modflag = true;
			$this->metamodflag = true;
			$this->content = ($content == NULL) ? '' : $content;
			$this->contentLoaded = true;
			$this->meta = $meta;
			$this->meta['creator'] = BAMBUS_USER;
			$this->meta['modifier'] = BAMBUS_USER;
			$this->meta['creationtime'] = time();
			$this->meta['modificationtime'] = time();
		}
		else
		{
			$this->id = $id;
			$this->title = $title;
			if($content == NULL)
			{
				$this->content = '';
			}
			else
			{
				$this->contentLoaded = true;
				$this->content = $content;
			}			
			$this->content = $content;
			$this->type = (in_array(strtoupper($type), $this->availableTypes)) ? strtoupper($type) : 'HTML';
			$this->meta = $meta;
			if(empty($this->meta['creationtime']))$this->meta['creationtime'] = time();
			if(empty($this->meta['modificationtime']))$this->meta['modificationtime'] = time();
		}
	}
	
	public function __toString()
	{
		return strval($this->content);
	}
	
	private function generateTeaser()
	{
		if(!$this->contentLoaded)
		{
			//load content
			$C = Configuration::alloc();
			$C->init();
			$file = $C->pathTo('document').$this->id.'.php';
			if(file_exists($file))
			{
				//read file
				$content = $this->FileSystem->read($file, true);
				if(substr($content,0,strlen($this->bdfstring)) == $this->bdfstring)
				{
					$content = substr($content,strlen($this->bdfstring));
				}
				$this->content = $content;
				$this->contentLoaded = true;
								
				//find strpos of ' id="BCMSTeaser"' 
				//and if true find strpos of tag begin
				//else if false find strpos of first tag
				
				//calculate end tag to our found beginning tag
				//return tag.innerHTML
			}
		}
		
		$begin = 0;
		//1. look for id="Teaser"
		$start = -1;
		$tag = '';
		
		$pos = mb_strpos($this->content, ' id="BCMSTeaser"',0, 'UTF-8');
		if($pos !== false)
		{
			$start = mb_strrpos(mb_substr($this->content,0,$pos, 'UTF-8'), '<','UTF-8');
			$stop = mb_strpos($this->content, ' ', $start, 'UTF-8');
			$tag = mb_substr($this->content,$start+1,$stop-$start-1, 'UTF-8');
//			echo '#id: ',$tag,'@',$start,'->',substr($this->content, $start, strlen($tag)+1), ">\n";
		}
		else
		//2. look for first tag
		{
			$hits = preg_match('/<([^\/>\s]+)[^\/>]{0,}>/', $this->content, $matches);
			if($hits > 0)
			{
				$start = mb_strpos($this->content, '<'.$matches[1], 0, 'UTF-8');
				$tag = $matches[1];
//				echo '#tag: ',$tag,'@',$start,'->',substr($this->content, $start, strlen($tag)+1), ">\n";
			}
		}
		
		if($start >= 0)
		{
			$teaser = '';
			$tag = mb_strtolower($tag, 'UTF-8');
			$text = mb_strtolower($this->content, 'UTF-8');
			$len = mb_strlen($text, 'UTF-8');
			$offset = $start;
			$sps = 1;
			while($sps > 0 && $offset < $len)
			{
				//find next end
				$possibleEnd = mb_strpos($text,'</'.$tag.'>',$offset, 'UTF-8');
				//find more starting tags between start and end
				$substr = mb_substr($text, $offset+1, $possibleEnd-$offset, 'UTF-8');
				$psps = preg_match('/<'.$tag.'[^\/>]{0,}>/', $substr);
				//$psps is the number of other start tags found 
				$sps += $psps;
				//decrease sps because of the fond end point
				$offset = $possibleEnd+1;
				//decrease start positions count
				$sps--;
			}
			$textStart = mb_strpos($text,'>', $start, 'UTF-8')+1;// find end of teaser opening tag
			$textLength = $possibleEnd+$tag-$textStart;
//			$textStart++;
//			echo 'erg: ';
			$res = mb_substr($this->content, $textStart, $textLength, 'UTF-8');
//			echo "\n\n\n\n\n\n", '<!------###########--------->' , "\n\n";
		}
		else
		{
		//3. use the first 1024 chars
			$res = strip_tags($this->content);
			if(mb_strlen($res, 'UTF-8') > 1024)
			{
				$searchRange = mb_substr($res, 990, 30);
				$pos = mb_strrpos($searchRange, ' ', 'UTF-8');
				$chopAt = ($pos !== false) ? 990 + $pos : 1020;
				$res = mb_substr($res, 0, $chopAt, 'UTF-8').'...';
			}
		}
		return $res;
	}
	
	public function __get($var)
	{
		switch($var)
		{
			case 'Id':
				return $this->id;
			case 'Title':
				return $this->title;
			case 'Title_ISO':
				return utf8_decode($this->title);
			case 'Content':
				return $this->content;
			case 'Content_ISO':
				return utf8_decode($this->content);
			case 'Text':
				return strip_tags($this->content);
			case 'Text_ISO':
				return strip_tags(utf8_decode($this->content));
			case 'Teaser':
				return $this->generateTeaser();;
			case 'Teaser_ISO':
				return utf8_decode($this->generateTeaser());
			case 'Type':
				return $this->type;
			case 'Meta':
				return $this->meta;
			case 'MetaUpdated':
				return $this->metamodflag;
			case 'Size':
				return strlen($this->content);
			case 'Modified':
				return $this->modflag;
			case 'AvailableTypes':
				return $this->availableTypes;
			case 'FileName':
				return $this->id.'.php';
			case 'pubDate':
			case 'publish':
			case 'PubDate':
				return (isset($this->meta['publish']) && is_numeric($this->meta['publish'])) ? $this->meta['publish'] : '';
			case 'Tags':
				if($this->Tags == null)
				{
					$this->Tags = array();
					//use new bambus
					$m = MPageManager::alloc()->init();
					if($m->Exists($this->id))
					{
						$this->Tags = $m->Open($this->id)->Tags;
					}
				}
				return $this->Tags;
			
		}
		//if not handled in switch it is treated as meta
		if(isset($this->meta[$var]))
		{
			//returned as UTF-8 - hopefully
			return $this->meta[$var];
		}
		if(isset($this->meta[strtolower($var)]))
		{
			//returned as UTF-8 - hopefully
			return $this->meta[strtolower($var)];
		}
		//nothing found but we are nice and return an empty string
		return '';
	}
	
	public function __set($var, $value)
	{
		$this->meta['modifier'] = BAMBUS_USER;
		$this->meta['modificationtime'] = time();
		switch($var)
		{
			case 'Title'://set utf8 encoded title
				return ($this->title = $value);
			case 'Title_ISO':
				return ($this->title = utf8_encode($value));
			case 'Content'://set utf8 encoded title
				if($this->content != $value)
				{
					$this->content = $value;
					$this->modflag = true;
				}
				return true;
			case 'Content_ISO':
				$value = utf8_encode($value);
				if($this->content != $value)
				{
					$this->content = $value;
					$this->modflag = true;
				}
				return true;
			case 'Type':
				if($value != $this->type && in_array($value, $this->availableTypes))
				{
					$this->metamodflag = true;
					$this->type = $value;
				}
				return true;
			case 'pubDate':
			case 'publish':
			case 'PubDate':
				$this->meta['publish'] =  (is_numeric($value)) ? $value : strtotime($value);
				$this->metamodflag = true;
				break;
			case 'Tags':
				if(is_array($value))
				{
					$this->Tags = $value;
				}
				else
				{
					$this->Tags = STag::parseTagStr($value);
				}
				//var_dump($this->Tags);
				$this->metamodflag = true;
				return true;
			//read only
			case 'Modified':
			case 'Meta':
			case 'Id':
			case 'Size':
			case 'Teaser':
			case 'Text':
			case 'Text_ISO':
				return false;
		}
		//if not handled in switch it is treated as meta
		//please save meta in UTF-8
		if(function_exists('mb_convert_variables'))
		{
			mb_convert_variables ('UTF-8', 'ISO-8859-1, UTF-8', $value);
		}
		if(empty($this->meta[$var]) || $this->meta[$var] != $value)
		{
			$this->metamodflag = true;
			$this->meta[$var] = $value;
		}
		return true;
	}
	
	public function __isset($var)
	{
		return (
			in_array($var, array('Id', 'Title', 'Title_ISO', 'Content', 'Content_ISO', 
								 'Text', 'Text_ISO', 'Type', 'Meta', 'MetaUpdated'))
			|| 
			in_array($var, $this->meta)
		);
	}
}
?>