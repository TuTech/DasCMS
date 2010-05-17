<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2007-11-19
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CError extends BContent implements IGlobalUniqueId, ISearchDirectives
{
    const GUID = 'org.bambuscms.content.cerror';
    const CLASS_NAME = 'CError';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
	public static function Create($title)
	{
	    throw new Exception('errors are fixed');
	}
	
	public static function errdesc($code)
	{
		$code = SHTTPStatus::validate($code);
		$code = $code == null ? 501 : $code; 
		return array($code => SHTTPStatus::byCode($code, false));
	}

	public function __construct($Id)	
	{
	    $Id = SHTTPStatus::validate($Id);
	    if($Id == 401)
	    {
	        $tpl = Core::settings()->get('login_template');
	        if(defined('BAMBUS_HTML_ACCESS') && !empty($tpl))
	        {
	            try 
	            {
	                //returns login form and ends function
	                return Controller_Content::getSharedInstance()->openContent($tpl);
	            }
	            catch (Exception $e)
	            {
	            	/* not returned the login tpl, send header auth instead */
	            }
	        }
            header("HTTP/1.1 401 Authorization Required");
            header("WWW-Authenticate: Basic realm=\"BambusCMS\"");
	    }
		$dat = self::errdesc($Id);
		$Ids = array_keys($dat);
		$this->Id = $Ids[0];
		$meta = array();
		$defaults = array(
			'CreateDate' => time(),
			'CreatedBy' => 'System',
			'ModifyDate' => time(),
			'ModifiedBy' => 'System',
			'PubDate' => time(),
			'Size' => 0,
			'Tags' => array(),
			'Title' => 'ERROR '.$this->Id.' - '.$dat[$this->Id],
			'Content' => sprintf('<div class="%s"><b>ERROR %d - %s</b></div>',get_class($this),$this->Id,$dat[$this->Id])
		);
		foreach ($defaults as $var => $default) 
		{
			$this->initPropertyValues($var, $meta, $default);
		}
	}

	public function __get($var)
	{
		return !empty($this->{$var}) ? $this->{$var} : '';
	}
	
	public function __set($var, $value){}
	
	public function __isset($var)
	{
		return isset($this->{$var});
	}
	
	public function Save(){}
	
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return false;
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array();
	}
    public function isSearchIndexingEditable()
    {
        return false;
    }
    public function changeSearchIndexingStatus($allow)
    {}
	
}
?>