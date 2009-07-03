<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-17
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class ATextBricks
    extends 
        BAppController 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    const GUID = 'org.bambuscms.applications.textbricks';

    /**
	 * @var CTextBrick
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = CTextBrick::Open($target);
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctextbricks.create');
        $success = false;
        if(!empty($param['create']))
        {
            try
            {
                $this->target = CTextBrick::Create($param['create']);
                $success = true;
            }
            catch (Exception $e)
            {
                SNotificationCenter::report('warning', 'could_not_create_text_brick');
            }
        }
        return $success;
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctextbricks.change');
        if($this->target != null
            && isset($param['content']))
        {
            $this->target->RAWContent = $param['content'];
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctextbricks.delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
            if(CTextBrick::Delete($alias))
            {
                $this->target = null;
            }
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
        $IDindex = CTextBrick::Index();
        $items = array();
        foreach ($IDindex as $alias => $data) 
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/CTextBrick.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/CTextBrick.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>