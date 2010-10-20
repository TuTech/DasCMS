<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2010-10-20
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Content
 */
class CSearch
    extends _Content
    implements
        ISupportsSidebar,
        IGlobalUniqueId,
        IGeneratesFeed,
        ISearchDirectives,
        IFileContent
{
    const CLASS_NAME = 'CSearch';
    const GUID = 'org.bambuscms.content.csearch';

	/**
	 * @var Interface_Search_Resultset
	 */
	private $result;

	private $queryString = '',
			$itemsPerPage = 10,
			$order = 1,
			$allowOverwriteOrder = false,
			$allowExtendQueryString = false;

	public function getClassGUID()
    {
        return self::GUID;
    }

	protected function composites()
	{
	    $composites = parent::composites();
	    $composites[] = 'ContentFormatter';
	    return $composites;
	}

	/**
	 * @return CSearch
	 */
	public static function Create($title)
	{
	    list($dbid, $alias) = _Content::createContent(self::CLASS_NAME, $title);
	    $tpl = new CSearch($alias);
	    new Event_ContentCreated($tpl, $tpl);
	    return $tpl;
	}

	/**
	 * @param string $id
	 * @throws XFileNotFoundException
	 * @throws XFileLockedException
	 * @throws XInvalidDataException
	 */
	public function __construct($alias)
	{
	    try
	    {
	        $this->initBasicMetaFromDB($alias, self::CLASS_NAME);
	    }
	    catch (XUndefinedIndexException $e)
	    {
	        throw new XArgumentException('content not found');
	    }
	    $dataFile = $this->StoragePath($this->Id);
	    if(file_exists($dataFile))
	    {
	        $this->_data = Core::FileSystem()->loadEncodedData($dataFile);
	    }
	}

	/**
	 * @return Interface_Search_Resultset
	 */
	private function getResult(){
		if(!$this->result){
			$SE = Controller_Search_Engine::getInstance();
			//se query
			//configure result
			//assign result
		}
		return $this->result;
	}

	/**
	 * list all aliases for feed use
	 * @return array
	 */
	public function getFeedItemAliases()
	{
		return $this->getResult()
				->fetch(15)
				->ordered($this->order)
				->resultsFromPage(1)
				->asAliases();
	}

	public function getLinkToFeed()
	{
	    return sprintf(
	    	'%s%s/%s',
	        SLink::base(),
	        IGeneratesFeed::FEED_ACCESSOR,
	        htmlentities($this->getAlias(), ENT_QUOTES, CHARSET)
        );
	}

	public function getFeedTargetView()
	{
	    return $this->option(CSearch::SETTINGS, 'TargetView');;
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		//not has formatter -> ul>li>a>[Title]

		/////////////
		// REWRITE //
		/////////////
		//se query
		//get result
		//

	}

	public function setContent($value){}

	protected function saveContentData()
	{
		/////////////
		// REWRITE //
		/////////////

		//save content
		Core::FileSystem()->storeDataEncoded($this->StoragePath($this->Id),$this->_data);
	}

	/**
	 * Icon for this filetype
	 * @return View_UIElement_Icon
	 */
	public static function defaultIcon()
	{
	    return new View_UIElement_Icon(self::CLASS_NAME, 'content', View_UIElement_Icon::LARGE, 'mimetype');
	}

	/**
	 * Icon for this object
	 * @return View_UIElement_Icon
	 */
	public function getIcon()
	{
	    return CSearch::defaultIcon();
	}

	//IFileContent
	public function getFileName()
	{
	    return $this->getTitle();
	}

    public function getType()
    {
        return 'xml';
    }

    public function getDownloadMetaData()
    {
        return array($this->getTitle().'.xml', 'application/xml', null);
    }

    public function sendFileContent()
    {
		/////////////
		// REWRITE //
		/////////////

        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $spore = $this->option(self::SETTINGS, 'TargetView');
        if(!empty($spore))
        {
            $base = SLink::base();
			$res = Core::Database()
				->createQueryForClass($this)
				->call('sitemapData')
				->withParameters($this->getId());
            while ($row = $res->fetchResult())
            {
                echo "\t<url>\n";
                echo "\t\t<loc>";
                echo $base, SLink::link(array($spore => $row[0]), '', true);
                echo "</loc>\n";
                echo "\t\t<lastmod>";
                echo date('c', strtotime($row[1]));
                echo "</lastmod>\n";
                echo "\t</url>\n";
            }
			$res->free();
        }
        echo "</urlset>";
    }

    public function getRawDataPath()
    {
        return null;
    }

	//ISupportsSidebar
	public function wantsWidgetsOfCategory($category)
	{
		return in_array(strtolower($category), array('settings', 'information', 'search'));
	}
	//ISearchDirectives
	public function allowSearchIndex()
	{
	    return _Content::isIndexingAllowed($this->getId());
	}
	public function excludeAttributesFromSearchIndex()
	{
	    return array('Content');
	}
	public function isSearchIndexingEditable()
    {
        return true;
    }
    public function changeSearchIndexingStatus($allow)
    {
        _Content::setIndexingAllowed($this->getId(), !empty($allow));
    }

	/**
	 * Return path to a given file or just the path for files
	 * if $file is not set or null
	 *
	 * @param string $file
	 * @return string file system path
	 */
	public function StoragePath($file = null, $addSuffix = true)
	{
		$path = sprintf(
			"./Content/%s/"
			,get_class($this)
		);
		if($file != null)
		{
			$path .= ($addSuffix) ? $file.'.php' : $file;
		}
		return $path;
	}
}
?>