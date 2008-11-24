<?php
/**
 * @package Bambus
 * @subpackage Contents
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 19.11.2007
 * @license GNU General Public License 3
 */
class CError extends BContent implements IGlobalUniqueId 
{
    const GUID = 'org.bambuscms.content.cerror';
    public function getGUID()
    {
        return self::GUID;
    }
    
	public static function Create($title)
	{
	    throw new Exception('errors are fixed');
	}
	
	public static function Delete($alias)
	{
	    throw new Exception('errors are fixed');
	}
	
	public static function Exists($alias)
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->exists($alias, 'CError');
	}
	
	/**
	 * [alias => [title, pubdate]]
	 * @return array
	 */
	public static function Index()
	{
	    $SCI = SContentIndex::alloc()->init();
	    return $SCI->getIndex('CError', false);;
	}
	
	public static function Open($alias)
	{
	    $alias = SHTTPStatus::validate($alias);
	    $SConf = SConfiguration::alloc()->init();
	    if($alias == 401)
	    {
	        if($SConf->Get('login_form_template') && defined('BAMBUS_HTML_ACCESS'))
	        {
	            //FIXME OPEN CONTENT
	        }
            else
            {
	            header("HTTP/1.0 401 Authorization Required");
	            header("WWW-Authenticate: Basic realm=\"BambusCMS\"");
	        }
	    }
        return new CError($alias == null ? 501 : $alias);
	}
	
	
	public static function errdesc($code)
	{
		$code = SHTTPStatus::validate($code);
		$code = $code == null ? 501 : $code; 
		return array($code => SHTTPStatus::byCode($code, false));
	}

	public function __construct($Id)	
	{
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
	
	public function isModified()
	{
	    return false;
	}
}
?>