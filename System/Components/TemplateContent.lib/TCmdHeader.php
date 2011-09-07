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
    private $metaTags = array();
    private $scriptTags = array();
    private $linkTags = array();
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
        return String::htmlEncode(mb_convert_encoding($val,CHARSET,'utf-8,iso-8859-1,auto'));
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
            
            $wellformed_urls = Core::Settings()->get('wellformed_urls');
            $baseURI = '';
            if(!empty($wellformed_urls))
            {
                $baseURI = sprintf("\n            <base href=\"%s\" />", SLink::base());
            }
           
            $e = new Event_WillSendHeaders($this);
            
            $title = empty($this->title) ? Core::Settings()->get('sitename') : $this->title;
			$favicon = View_UIElement_Icon::pathFor('dummy','mimetypes',  View_UIElement_Icon::MEDIUM);
			$fitype = 'png';
			foreach(array('ico','png') as $itype){
				if(file_exists('favicon.'.$itype)){
					$favicon = 'favicon.'.$itype;
					$fitype = $itype;
				}
			}
			$favicon = sprintf('<link rel="icon" type="image/%s" href="%s">', $fitype, $favicon);
			foreach(array(114, 72, 57) as $size){
				$apple_icon = sprintf("apple-touch-icon-%dx%d.png", $size, $size);
				if(file_exists($apple_icon)){
					$favicon .= sprintf("\n<link rel=\"apple-touch-icon\" sizes=\"%dx%d\" href=\"%s\" />", $size, $size, $apple_icon);
				}
			}
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
                ,$favicon
    			,implode($glue, $this->metaTags)
                ,implode($glue, $this->linkTags)
                ,implode($glue, $this->scriptTags)
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
            $this->linkTags[] = sprintf('<link%s />', $atts);
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
            $this->metaTags[] = sprintf('<meta%s />', $atts);
        }
    }
    
    public function addScript($type, $src = null, $script = null)
    {
        $type = $this->encode($type);
        $src = ($src == null) ? '' : ' src="'.$this->encode($src).'"';
	    $script = ($script == null) ? '' : $this->encode($script);
	    $this->scriptTags[] = sprintf('<script type="%s"%s>%s</script>', $type, $src, $script);
    }
}
?>
