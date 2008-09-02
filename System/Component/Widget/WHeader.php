<?php
/**
 * @package Bambus
 * @subpackage Widgets
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 06.05.2008
 * @license GNU General Public License 3
 */
class WHeader extends BWidget 
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
	
	public static function useStylesheet($style)
	{
		self::relate($style, 'stylesheet', 'text/css');
	} 
	
	public static function relate($link, $as, $type)
	{
		self::$relations[] = array($link, $as, $type);
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
		//@todo validate url
		self::$base = $to;
	}
	
	public static function setTitle($to)
	{
		self::$title = self::enc($to);
		self::meta("title", $to);
	}
	
	private static function enc($str)
	{
		return htmlspecialchars($str,ENT_QUOTES, 'UTF-8');
	}
	
	public function __toString()
	{
		$html = "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" ".
				"\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n".
				"<html xmlns=\"http://www.w3.org/1999/xhtml\">\n\t<head>\n";
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
		
		//embed custom stylesheets
		foreach (self::$relations as $relArr) 
		{
			//load general stylesheets (lowercase) or for active classes (e.g. WHeader)
			if(strpos($relArr[0],'/') === false)
			{
				//not a path
				if(strtolower($relArr[1]) == 'stylesheet')
				{
					//@todo determine if management | system and load appropriate
					$relArr[0] = SPath::SYSTEM_STYLESHEETS.$relArr[0]; 
				}
			}
			$html .= sprintf("\t\t<link href=\"%s\" rel=\"%s\" type=\"%s\" />\n"
				, self::enc($relArr[0])
				, self::enc($relArr[1])
				, self::enc($relArr[2]));
		}
		
		foreach (self::$scripts as $script => $autoloaded) 
		{
			if(strpos($script,'/') === false)
			{
				//@todo determine if management | system and load appropriate
				$script = SPath::SYSTEM_SCRIPTS.$script; 
			}			
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