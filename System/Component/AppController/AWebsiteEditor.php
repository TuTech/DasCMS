<?php
class AWebsiteEditor 
    extends 
        BAppController 
    implements 
        IACProviderOpenDialogData,
        IGlobalUniqueId  
{
    const GUID = 'org.bambuscms.applications.websiteeditor';
    
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    /**
     * returns all data necessary for the open dialog
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $SCI = SContentIndex::alloc()->init();
        $IDindex = CPage::Index();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array(CPage::defaultIcon()->asSize(WIcon::LARGE)->getPath()),
            'smallIconMap' => array(CPage::defaultIcon()->asSize(WIcon::EXTRA_SMALL)->getPath()),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items,
            'captions' => array(
                'detail' => SLocalization::get('detail'),
                'icon' => SLocalization::get('icon'),
                'list' => SLocalization::get('list'),
                'asc' => SLocalization::get('asc'),
                'desc' => SLocalization::get('desc'),
                'searchByTitle' => SLocalization::get('search_by_title'),
                'pubDate' => SLocalization::get('pubDate'),
                'notPublished' => SLocalization::get('not_published'),
                'title' => SLocalization::get('title'),
                'type' => SLocalization::get('type'),
            )
        );
        return $data;
    }
    
    /**
     * delete a bunch of items
     */
    public function delete(array $items)
    {
        
    }
    
    /**
     * create a new item
     */
    public function create($title, array $options)
    {
        
    }
    
    /**
     * set all kinds of possible meta attributes
     */
    public function setAttributes(array $attributes)
    {
        
    }
    
    
    
    
}
?>