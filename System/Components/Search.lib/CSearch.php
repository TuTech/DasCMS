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
		Interface_Content_HasScope,
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
			$order = Interface_Search_ConfiguredResultset::ASC,
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
	    $composites[] = 'TargetView';
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
			$file = sprintf('Content/CSearch/%d.php', $this->Id);
			if(file_exists($file)){
				list(
					$this->queryString,
					$this->allowExtendQueryString,

					$this->order,
					$this->allowOverwriteOrder,

					$this->itemsPerPage
				) = Core::FileSystem()->loadEncodedData($file);
			}
	    }
	    catch (XUndefinedIndexException $e)
	    {
	        throw new XArgumentException('content not found');
	    }
	}

	/**
     * @return Interface_Content_FiniteScope
     */
    public function getScope(){
		return new Controller_Search_ResultScope($this->getResult(), $this->getParentView());
	}

	public function setQueryString($value){
		$this->queryString = strval($value);
	}

	public function getQueryString(){
		return $this->queryString;
	}

	public function setAllowExtendQueryString($value){
		$this->allowExtendQueryString = !!$value;
	}

	public function getAllowExtendQueryString(){
		return $this->allowExtendQueryString;
	}

	public function setOrder($value){
		$this->order = ($value == Interface_Search_ConfiguredResultset::ASC)
				? Interface_Search_ConfiguredResultset::ASC
				: Interface_Search_ConfiguredResultset::DESC;
	}

	public function getOrder(){
		return $this->order;
	}

	public function setAllowOverwriteOrder($value){
		$this->allowOverwriteOrder = !!$value;
	}

	public function getAllowOverwriteOrder(){
		return $this->allowOverwriteOrder;
	}

	public function setItemsPerPage($value){
		$this->itemsPerPage = max(1, intval($value));
	}

	public function getItemsPerPage(){
		return $this->itemsPerPage;
	}

	/**
	 * @return Interface_Search_ResultPage
	 */
	private function getResult(){
		if(!$this->result){
			//predefined data
			$res = Controller_Search_Engine::getInstance()
					->query($this->queryString)//allow qs input?
					->fetch($this->itemsPerPage)
					->ordered($this->order);//allow order input?
					
			//input data
			$pageNo = 1;
			$pv = $this->getParentView();
			if($pv instanceof Controller_View_Content){
				$pageNo = intval($pv->GetParameter('page'));
				$pageNo = max(1, $pageNo);//pageNo >= 1
				$pageNo = min($res->getLastPageNumber(), $pageNo); // pageNo <= last page
			}
			$this->result = $res->resultsFromPage($pageNo);
		}
		return $this->result;
	}

	/**
	 * list all aliases for feed use
	 * @return array
	 */
	public function getFeedItemAliases()
	{
		return Controller_Search_Engine::getInstance()
				->query($this->queryString)
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

	/**
	 * @return string
	 */
	public function getContent()
	{
		$contents = $this->getResult()->asContents();
		$out = '';
		try{
			foreach ($contents as $content){
				//display with formatter
				$out .= $this->formatChildContent($content);
			}
		}
		catch(Exception $e){
			$out = sprintf('<b>%s</b>', $e->getMessage());
			//TODO no formatter default to  "ul>li>a>[Title]"
		}
		return $out;
	}

	public function setContent($value){}

	protected function saveContentData()
	{
		$data = array(
			$this->queryString,
			$this->allowExtendQueryString,

			$this->order,
			$this->allowOverwriteOrder,

			$this->itemsPerPage
		);
		Core::FileSystem()->storeDataEncoded(
				sprintf('Content/CSearch/%d.php', $this->Id),
				$data
		);
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

	public function getFeedTargetView() {
		return $this->getTargetView();
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
        echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
        echo "<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">\n";
        $spore = $this->getTargetView();
        if(!empty($spore))
        {
            $base = SLink::base();
			$res = Controller_Search_Engine::getInstance()
				->query($this->queryString);
			$c = $res->fetch($res->getResultCount())
				->ordered($this->order)
				->resultsFromPage(1)
				->asContents();
			foreach ($c as $content)
            {
				printf(
					"<url><loc>%s%s</loc><lastmod>%s</lastmod></url>\n"
					,$base
					,SLink::link(array($spore => $content->getAlias()), '', true)
					,date('c', $content->getModifyDate())
				);
            }
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
    {
    }
}
?>