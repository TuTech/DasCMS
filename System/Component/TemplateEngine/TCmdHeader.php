<?php
class TCmdHeader
    extends 
        BTemplate
    implements 
        ITemplateCommand 
{
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
        return htmlentities(mb_convert_encoding($val,'UTF-8','utf-8,iso-8859-1,auto'), ENT_QUOTES, 'UTF-8');
    }
    
    public function tearDown()
    {
    }

    public function __sleep()
    {
        //$this->data = array($this->request);
        return array();//'data'
    }
    
    public function __wakeup()
    {
        //$this->request = $this->data[0];
        $this->data = array();
    }
    
    public function __toString()
    {
        $events = SContentWatch::accessedContent();
        $descriptions = array();
        $titles = array();
        $tags = array();
        $feeds = array();
        $cs = array();
        foreach ($events as $cid => $event) 
        {
            $c = $event->Content;
            if(is_object($event->Sender) && (get_class($event->Sender) == 'QSpore' || $event->Sender instanceof BView))
            {
                //prepend dynamic content
                array_unshift($cs , $c);
                $titles[$c->getAlias()] = $c->getTitle();
                $descriptions[$c->getAlias()] = $c->getDescription();
                $tags = array_merge($tags, $c->getTags());
            }
            else
            {
                //append fix/static content
                $cs[] = $c; 
            }
        }
        foreach($cs as $c)
        {
            //prefer dynamic
        	if($c instanceof IGeneratesFeed)
        	{
        	    $feeds[$c->getAlias()] = $c->getTitle();
        	}
        }
        sort($tags, SORT_LOCALE_STRING);
        $title = LConfiguration::get('sitename').implode(', ', $titles);
        $description = implode(', ', $descriptions);
        $keywords = implode(', ', array_unique($tags));
        $copyright = LConfiguration::get('copyright');
        $publisher = LConfiguration::get('publisher');
        $generator = BAMBUS_VERSION;
        $feedTags = '';
        foreach ($feeds as $alias => $feedTitle) 
        {
        	$feedTags .= sprintf("            <link rel=\"alternate\" type=\"application/atom+xml\"".
        	                    "title=\"%s\" href=\"%s\" />\n"
				,htmlentities($feedTitle, ENT_QUOTES, 'UTF-8')
                ,BFeed::getURLForFeed($alias)
			);
        }
        
       
        
        return sprintf("
        <head>
            <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
            <title>%s</title>
            <meta name=\"DC.title\" content=\"%s\" />
            <meta name=\"DC.publisher\" content=\"%s\" />
            <meta name=\"DC.contributor\" content=\"%s\" />
            <meta name=\"description\" content=\"%s\" />
            <meta name=\"copyright\" content=\"%s\" />
            <meta name=\"generator\" content=\"%s\" />
            <meta name=\"keywords\" content=\"%s\" />
            <link rel=\"icon\" href=\"%s\" />
            <link rel=\"stylesheet\" href=\"./css.php?v=%d.css\" type=\"text/css\" />\n%s
        </head>"//<script type=\"text/javascript\" src=\"./js.php?v=%d.js\"></script>
			,$this->encode($title)
            ,$this->encode($title)
            ,$this->encode($publisher)
            ,$this->encode('')
            ,$this->encode($description)
            ,$this->encode($copyright)
            ,$this->encode($generator)
            ,$this->encode($keywords)
            ,'favicon.ico'
			,filemtime('Content/stylesheets/default.css')
            ,$feedTags
        );
    }
}
?>