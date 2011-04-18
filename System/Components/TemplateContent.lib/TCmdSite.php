<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2010-10-13
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Template
 */
class TCmdSite
    extends
        BTemplate
    implements
        ITemplateCommand,
		IHeaderAPI
{
    protected $parsed;
    public $data = array();

	private $metaTags = array();
    private $scriptTags = array();
    private $linkTags = array();
    private $title;

    public function __construct(DOMNode $node)
    {
        foreach ($node->childNodes as $childNode)
        {
            $this->analyze($childNode);
        }
    }

    public function setUp(array $environment)
    {
        foreach ($this->parsed as $object)
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $object->setUp($environment);
        	}
        }
    }

    public function run(array $environment)
    {
		$settings = Core::Settings();
		//doctype & xml namespace
		$doctype = $settings->getOrDefault('doctype', SResourceString::get('doctypes', 'html5'));
		$ns = '';
		if(strpos($doctype, 'xhtml')){
			$out .= "\n".'<?xml version="1.0" encoding="utf-8"?>';
			$ns = ' xmlns="http://www.w3.org/1999/xhtml"';
		}
		//language
		$lang = $settings->get('lang');
		if($lang){
			$lang = sprintf(' lang="%s"', $lang);
		}

		//header services
		$this->setTitle($settings->getOrDefault('sitename', 'DasCMS'));
		$e = new Event_WillSendHeaders($this);
		$niceURLS = $settings->get('wellformed_urls');

		//favicon
		$favicon = View_UIElement_Icon::pathFor('dummy','mimetypes',  View_UIElement_Icon::MEDIUM);
		$fitype = 'png';
		foreach(array('ico','png') as $itype){
			if(file_exists('favicon.'.$itype)){
				$favicon = 'favicon.'.$itype;
				$fitype = $itype;
			}
		}
		$favicon = sprintf('<link rel="icon" type="image/%s" href="%s">', $fitype, $favicon);

		//generate header
		$out = $doctype;
        $out .=	"\n<html".$lang.$ns."><head>".
				"<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />\n".
				"<title>".$this->title."</title>\n";
		if($niceURLS){
			$out .= sprintf("<base href=\"%s\" />\n", SLink::base());
		}
		$out .=	$favicon."\n";
		foreach(array($this->metaTags, $this->linkTags) as $tag){
			if(count($tag)){
				$out .= implode("\n", $tag)."\n";
			}
		}
		$out .= "</head>\n<body>\n";

		//generate body
        foreach ($this->parsed as $object)
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $out .= $object->run($environment);
        	}
        	else
        	{
        	    $out .= strval($object);
        	}
        }

		//embed javascripts
		if(count($this->scriptTags)){
			$out .= implode("\n", $this->scriptTags)."\n";
		}

		$out .= "<script type=\"text/javascript\">".
						"window.setTimeout(function(){".
							"var sched = new Image(1,1).src='scheduler.php';".
						"}, 250);".
				"</script>\n";

		//display cms health status
		$out .= sprintf(
				"<!-- %s/%s/%1.5f -->\n",
				memory_get_usage(true),
				memory_get_peak_usage(true),
				microtime(true) - CMS_START_TIME
			);

		//finish
        return $out."</body>\n</html>";
    }

    public function tearDown()
    {
        foreach ($this->parsed as $object)
        {
        	if(is_object($object) && $object instanceof ITemplateCommand)
        	{
    	        $object->tearDown();
        	}
        }
    }

    public function __sleep()
    {
        $this->data = array($this->parsed);
        return array('data');
    }

    public function __wakeup()
    {
        $this->parsed = $this->data[0];
        $this->data = array();
    }

	//IHeaderAPI
    private function encode($val)
    {
        return String::htmlEncode(mb_convert_encoding($val,CHARSET,'utf-8,iso-8859-1,auto'));
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