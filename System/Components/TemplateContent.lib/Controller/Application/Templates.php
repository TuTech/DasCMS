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
class Controller_Application_Templates
    extends 
        _Controller_Application 
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    const GUID = 'org.bambuscms.applications.templates';
        
	public function  __construct() {
		if(file_exists('System/External/Bespin/BespinEmbedded.js')){
			View_UIElement_Header::relate('System/External/Bespin/', null, null, 'bespin_base');
			View_UIElement_Header::useScript('System/External/Bespin/BespinEmbedded.js');
		}
	}
	
    /**
	 * @var CTemplate
     */
    protected $target = null;
    
    public function setTarget($target)
    {
        try
        {
            if(!empty($target))
            {
                $this->target = Controller_Content::getInstance()->openContent($target, 'CTemplate');
            }
        }
        catch (Exception $e)
        {
            $this->target = null;
        }
    }
    
    public function create(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctemplate.create');
        $success = false;
        if(!empty($param['create']))
        {
            try
            {
                $this->target = CTemplate::Create($param['create']);
                $success = true;
            }
            catch (Exception $e)
            {
                SNotificationCenter::report('warning', 'could_not_create_template');
            }
        }
        return $success;
    }
    
    public function save(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctemplate.change');
        if($this->target != null) 
        {
            if(!empty($param['title']))
            {
                $this->target->Title = $param['title'];
            }
            if(isset($param['subtitle']))
            {
                $this->target->SubTitle = $param['subtitle'];
            }
            if(isset($param['content']))
            {
                $this->target->RAWContent = $param['content'];
            }
        }
    }
    
    public function delete(array $param)
    {
        parent::requirePermission('org.bambuscms.content.ctemplate.delete');
        if($this->target != null)
        {
            $alias = $this->target->Alias;
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
            try
            {
                SErrorAndExceptionHandler::muteErrors();
                $this->target->save();
                SErrorAndExceptionHandler::reportErrors();
            }
            catch (Exception $e)
            {
            	SNotificationCenter::report('warning', 'invalid_template_not_executeable');
            }
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
        $idIndex = Controller_Content::getInstance()->contentIndex('CTemplate');
        $items = array();
        foreach ($idIndex as $alias => $data)
        {
        	list($title, $pubdate) = $data;
        	$items[] = array($title, $alias, 0, strtotime($pubdate));
        }
        $data = array(
            'title' => SLocalization::get('open'),
            'nrOfItems' => count($items),
            'iconMap' => array('System/ClientData/Icons/tango/large/mimetypes/CTemplate.png'),
            'smallIconMap' => array('System/ClientData/Icons/tango/extra-small/mimetypes/CTemplate.png'),
            'itemMap' => array('title' => 0, 'alias' => 1, 'icon' => 2, 'pubDate' => 3),//, 'tags' => 4
            'sortable' => array('title' => 'title', 'pubDate' => 'pubDate'),
            'items' => $items
        );
        return $data;
    }
}
?>