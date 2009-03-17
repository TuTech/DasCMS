<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-02
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AWebsiteEditor 
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog  
{
    const GUID = 'org.bambuscms.applications.websiteeditor';
        
    /**
	 * @var CPage
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = CPage::Open($target);
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cpage.create');
        if(!empty($param['create']))
        {
            $this->target = CPage::Create($param['create']);
        }
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cpage.change');
        if($this->target != null
            && isset($param['content']))
        {
            $this->target->Content = $param['content'];
            if(!empty($param['filename']))
            {
                $this->target->Title = $param['filename'];
            }
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cpage.delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
            $this->target = null;
            CPage::Delete($alias);
        }
    }
    
    public function commit()
    {
        if($this->target != null && $this->target->isModified())
        {
            $this->target->Save();
        }
    } 
    
    /**
     * array(BContent|string file, [string mimetype])
     * 
     * @return array
     */
    public function getSideBarTarget()
    {
        $ret = array();
        if($this->target)
        {
            $ret = array($this->target);
        }
        return $ret;
    }

    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    /**
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return empty($this->target) ? null : $this->target->Alias;
    }
    
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws XPermissionDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new XPermissionDeniedException('view');
        }
        $IDindex = CPage::Index();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate, $type, $id) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate), filesize('Content/CPage/'.$id.'.content.php'));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array(CPage::defaultIcon()->asSize(WIcon::LARGE)->getPath()),
            'smallIconMap' => array(CPage::defaultIcon()->asSize(WIcon::EXTRA_SMALL)->getPath()),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3, 'size' => 4),//, 'tags' => 4
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
                'size' => SLocalization::get('size'),
            )
        );
        return $data;
    }
}
?>