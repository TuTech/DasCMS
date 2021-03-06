<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-05-06
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Widget
 */
class View_UIElement_Header extends _View_UIElement 
{
	//support all dc
	private static $purlTerms = array(
		"abstract", "accessRights", "accrualMethod", "accrualPeriodicity",
	 	"accrualPolicy", "alternative", "audience", "available", "bibliographicCitation",
	 	"conformsTo", "contributor", "coverage", "created", "creator", "date", "dateAccepted", 
		"dateCopyrighted", "dateSubmitted", "description", "educationLevel", "extent", "format", "hasFormat",
	 	"hasPart", "hasVersion", "identifier", "instructionalMethod", "isFormatOf", "isPartOf", 
		"isReferencedBy", "isReplacedBy", "isRequiredBy", "issued", "isVersionOf", 
		"language", "license", "mediator", "medium", "modified", "provenance", "publisher", "references",
	 	"relation", "replaces", "requires", "rights", "rightsHolder", "source", "spatial", "subject",
	 	"tableOfContents", "temporal", "title", "type", "valid"
	);
	private static $usedPurl = false;
	
	private static $scripts = array();//script => auto-assign
	private static $relations = array();
	private static $base = false;
	private static $title = '';
	private static $favicon = 'System/ClientData/Icons/tango/large/apps/utilities.png';
	private static $meta = array();
	private static $httpHeader = array();
	
	public function __construct($target = null)
	{
	}
	
	public static function activatePurl()
	{
		if(!self::$usedPurl)
		{
			self::relate('http://purl.org/dc/terms/', 'schema.DC', '');
		}
		self::$usedPurl = true;
	}
	
	public static function useScript($script)
	{
		if(strpos($script, '/') === false)
		{
			$parts = explode('.', $script);
			$tldscripts = '';
			while(count($parts) > 1)
			{
				$tldscripts .= array_shift($parts); 
				self::$scripts[$tldscripts.'.js'] = true;
			}
		}
		self::$scripts[$script] = false;
	} 
	
	public static function useStylesheet($style, $media = 'all')
	{
		self::relate($style, 'stylesheet', 'text/css', 'media="'.$media.'"');
	} 
	
	public static function relate($link, $as = null, $type = null, $id = null)
	{
		self::$relations[] = array('href' => $link, 'rel' => $as, 'type' => $type, 'id' => $id);
	} 
	
	public static function httpHeader($directive)
	{
		if(strpos($directive,':') !== false)
		{
			@header($directive);
			$parts = explode(':', $directive);
			$direct = array_shift($parts);
			$content = implode(':', $parts);
			self::$httpHeader[$direct] = $content;
		}
	}
	
	public static function meta($name, $content)
	{
		try
		{
			self::$meta[self::asPurl($name)] = $content;
		}
		catch(Exception $e)
		{
			self::$meta[$name] = $content;
		}
	}
	
	private static function asPurl($key)
	{
		$key = strtolower($key);
		foreach (self::$purlTerms as $purl) 
		{
			if($key == strtolower($purl))
				return 'DC.'.$purl;
		}
		throw new XUndefinedIndexException('not purl');
	}
	
	public static function setBase($to)
	{
		self::$base = $to;
	}

	public static function setIcon($to)
	{
		self::$favicon = $to;
	}

	public static function setTitle($to)
	{
		self::$title = self::enc($to);
		self::meta("title", $to);
	}
	
	private static function enc($str)
	{
		return String::htmlEncode($str);
	}
	
	public static function loadClientData($path = null)
	{
	    $path = ($path == null) ? (Core::PATH_SYSTEM_CLIENT_DATA) : $path;
	    $folders = array();
	    $files = array();
	    $oldPath = getcwd();
	    chdir($path);
	    $hdl = opendir('.');
	    while ($item = readdir($hdl))
	    {
	        if(is_dir($item) && $item != '.' && $item != '..')
	        {
	            $folders[] = $item;
	        }
	        elseif(is_file($item))
	        {
	            $files[strtoupper($item).$item] = $item;
	        }
	    }
	    chdir($oldPath);
	    ksort($files);
	    foreach ($files as $k => $f)
	    {
            $s = Core::FileSystem()->suffix($f);
            switch ($s) 
            {
            	case 'css':
            		self::useStylesheet($path.$f);
            		break;
            	case 'js':
            		self::useScript($path.$f);
            		break;
            	default:break;
            }
	    }
	    foreach ($folders as $f)
	    {
	        self::loadClientData($path.$f.'/');
	    }
	}
	
	public function __toString()
	{
		$html = "<!DOCTYPE html>\n".
				"<html ".
				(
					file_exists('Content/cache-manifest.php') && Core::Settings()->getOrDefault('enable_management_cache_manifest', false)
						? "manifest=\"".(self::$base ? self::enc(self::$base) : '')."Content/cache-manifest.php\" "
						: ''
				).
				"lang=\"".Core::Settings()->get('locale')."\">\n\t<head>\n";
				//"\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n";
		foreach (self::$httpHeader as $cmd => $content) 
		{
			$html .= sprintf("\t\t<meta http-equiv=\"%s\" content=\"%s\" />\n"
				, self::enc($cmd)
				, self::enc($content));
		}
		
		if(self::$base)//base set?
		{
			$html .= sprintf("\t\t<base href=\"%s\" />\n", self::enc(self::$base));
		}

		if(self::$favicon)
		{
			$html .= sprintf("\t\t<link rel=\"icon\" type=\"image/png\" href=\"%s\" />\n", self::enc(self::$favicon));
		}
		
		//$this->loadClientData();
		
		//embed custom stylesheets
		foreach (self::$relations as $relArr) 
		{
			//load general stylesheets (lowercase) or for active classes (e.g. View_UIElement_Header)
			$relTxt = '';
			foreach ($relArr as $name => $value){
				if($value !== null){
					$relTxt .= sprintf(' %s="%s"', $name, self::enc($value));
				}
			}
			$html .= sprintf("\t\t<link%s/>\n", $relTxt);
		}
		
		foreach (self::$scripts as $script => $autoloaded) 
		{
			$html .= sprintf("\t\t<script type=\"text/javascript\" src=\"%s\">%s</script>\n"
				, self::enc($script)
				, ($autoloaded) ? ' /* autoload */ ' : '');;
		}
		
		$html .= sprintf("\t\t<title>%s</title>\n", self::enc(self::$title));
		foreach (self::$meta as $name => $content) 
		{
			$html .= sprintf("\t\t<meta name=\"%s\" content=\"%s\" />\n"
				, self::enc($name)
				, self::enc($content));
		}
		$html .= "\t</head>\n";
		return $html;
	}
}

?>