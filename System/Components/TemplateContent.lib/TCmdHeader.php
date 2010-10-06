<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-31
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdHeader
    extends 
        BTemplate
    implements 
        ITemplateCommand,
        IHeaderAPI
{
    private $MetaTags = array();
    private $ScriptTags = array();
    private $LinkTags = array();
    private $title;
    
    private $scriptEmbedded = false;
    
    private $request;
    private $val;
    public $data = array();
    
    public function __construct(DOMNode $node)
    {
    }
    
    public function setUp(array $environment)
    {
    }
    
    public function run(array $environment)
    {
        return $this;
    }
    
    private function encode($val)
    {
        return htmlspecialchars(mb_convert_encoding($val,CHARSET,'utf-8,iso-8859-1,auto'), ENT_QUOTES, CHARSET);
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        return array();
    }
    
    public function __wakeup()
    {
        $this->data = array();
    }
    
    public function __toString()
    {
        try{
            $events = SContentWatch::accessedContent();
            
            $wellformed_urls = Core::settings()->get('wellformed_urls');
            $baseURI = '';
            if(!empty($wellformed_urls))
            {
                $baseURI = sprintf("\n            <base href=\"%s\" />", SLink::base());
            }
           
            $e = new Event_WillSendHeaders($this);
            
            $title = empty($this->title) ? Core::settings()->get('sitename') : $this->title;
            $glue = "\n            ";
            return sprintf("	<head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />%s
            <title>%s</title>%s
            %s
            %s
            %s
        </head>"
    			,$baseURI
                ,$title
    			//,filemtime('Content/stylesheets/default.css')
                ,file_exists('favicon.ico') ? "\n            <link rel=\"icon\" href=\"favicon.ico\" />": ''
    			,implode($glue, $this->MetaTags)
                ,implode($glue, $this->LinkTags)
                ,implode($glue, $this->ScriptTags)
            );
        }
        catch (Exception $e)
        {
            SErrorAndExceptionHandler::reportException($e);
            return '<head></head>';
        }
        
    }
    
    private function buildAttributes(array $attributes)
    {
        $str = '';
        foreach ($attributes as $key => $value)
        {
            if($value != null)
            {
                $str .= sprintf(' %s="%s"', $key, $this->encode($value));
            }
        }
        return $str;
    }
    
    //IHeaderAPI
    public function setTitle($title)
    {
        $this->title = $this->encode($title);
        $this->addMeta($title, 'DC.title');
    }
    
    public function addLink($charset = null, $href = null, $hreflang = null, $type = null, $title = null, $rel = null, $rev = null, $media = null)
    {
        $data = array('rel' => $rel, 'type' => $type, 'charset' => $charset, 'href' => $href, 'hreflang' => $hreflang, 
        				'title' => $title, 'rev' => $rev, 'media' => $media);
        $atts = $this->buildAttributes($data);
        if($atts != '')
        {
            $this->LinkTags[] = sprintf('<link%s />', $atts);
        }
    }
    
    public function addMeta($content, $name = null, $httpEquiv = null, $scheme = null)
    {
        if($httpEquiv != null && trim(strtolower($httpEquiv)) == 'content-type')
        {
            //not allowed;
            return;
        }
        $data = array('name' => $name, 'http-equiv' => $httpEquiv, 'content' => $content, 'scheme' => $scheme);
        $atts = $this->buildAttributes($data);
        if($atts != '')
        {
            $this->MetaTags[] = sprintf('<meta%s />', $atts);
        }
    }
    
    public function addScript($type, $src = null, $script = null)
    {
        if(count($this->ScriptTags) == 0)
        {
            $this->ScriptTags[] = '<script type="text/javascript" src="System/WebsiteSupport/JavaScript/bambus.js"></script>';
        }
        $type = $this->encode($type);
        $src = ($src == null) ? '' : ' src="'.$this->encode($src).'"';
	    $script = ($script == null) ? '' : $this->encode($script);
	    $this->ScriptTags[] = sprintf('<script type="%s"%s>%s</script>', $type, $src, $script);
    }
}
?>