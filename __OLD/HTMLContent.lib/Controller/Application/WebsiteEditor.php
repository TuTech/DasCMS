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
class Controller_Application_WebsiteEditor 
    extends 
        _Controller_Application 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog,
		Application_Interface_AppController
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
                $this->target = Controller_Content::getInstance()->openContent($target, 'CPage');
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
        $success = false;
        if(!empty($param['create']))
        {
            try
            {
                $this->target = CPage::Create($param['create']);
                $success = true;
            }
            catch (Exception $e)
            {
                SNotificationCenter::report('warning', 'could_not_create_website');
            }
        }
        return $success;
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cpage.change');
        if($this->target != null && $this->target instanceof Interface_Content)
        {
            if(!empty($param['title']))
            {
                $this->target->setTitle($param['title']);
            }
            if(isset($param['subtitle']))
            {
                $this->target->setSubTitle($param['subtitle']);
            }
            if(isset($param['content']))
            {
                $this->target->setContent($param['content']);
            }
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.cpage.delete');
        if($this->target != null)
        {
            $alias = $this->target->getAlias();
            if(Controller_Content::getInstance()->deleteContent($alias))
            {
                $this->target = null;
            }
        }
    }
    
    public function commit()
    {
        if($this->target != null)
        {
            $this->target->save();
        }
    } 
    
    /**
     * array(Interface_Content|string file, [string mimetype])
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

	//begin Application_Interface_AppController
	public function getTitle(){
		return 'html_wysiwyg_editor';
	}
	public function getIcon(){
		return 'app-editor-html-wysiwyg';
	}
	public function getDescription(){
		return 'html_wysiwyg_editor';
	}
	public function getEditor(){
		return 'legacy:Websites_tinyMCE.bap';
	}
	public function getContentObjects() {
		$this->isPermitted('view');
        return array_keys(Controller_Content::getInstance()->contentIndex(CPage::CLASS_NAME, true));
	}
	//end Application_Interface_AppController
    
    /**
     * opened object 
     * @return string|null 
     */
    public function getOpenDialogTarget()
    {
        return empty($this->target) ? null : $this->target->getAlias();
    }
    
    
    /**
     * returns all data necessary for the open dialog
     * @param array $namedParameters
     * @return array
     * @throws AccessDeniedException
     */
    public function provideOpenDialogData(array $namedParameters)
    {
        if(!$this->isPermitted('view'))
        {
            throw new AccessDeniedException('view');
        }
        $idIndex = Controller_Content::getInstance()->contentIndex('CPage');
        $items = array();
        foreach ($idIndex as $alias => $data)
        {
        	list($title, $pubdate, $type, $id) = $data;
        	$items[] = array($title, $alias, 0, $pubdate, filesize('Content/CPage/'.$id.'.content.php'));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array(CPage::defaultIcon()->asSize(View_UIElement_Icon::LARGE)->getPath()),
            'smallIconMap' => array(CPage::defaultIcon()->asSize(View_UIElement_Icon::EXTRA_SMALL)->getPath()),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3, 'size' => 4),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>