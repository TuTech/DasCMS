<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-05-29
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage AppController
 */
class AScriptEditor
    extends 
        BAContentAppController
    implements 
        IGlobalUniqueId,
        ISupportsOpenDialog
{
    /**
     * required permission for class
     * @var string
     */
    protected $contentPermission = 'org.bambuscms.content.cscript';
    
    /**
     * content class
     * @var string
     */
    protected $contentClass = 'CScript';
        
    /**
     * content icon
     * @var string
     */
    protected $contentIcon = 'js';
    
    /**
	 * @var CScript
     */
    protected $target = null;
    
    const GUID = 'org.bambuscms.applications.scripteditor';
    
    /**
     * @return string
     * (non-PHPdoc)
     * @see System/Component/Interface/IGlobalUniqueId#getClassGUID()
     */
    public function getClassGUID()
    {
        return self::GUID;
    }
    
    public function save(array $param)
    {
        $this->checkPermission('change');
        if($this->target != null)
        {
            if(isset($param['content']))
            {
                $this->target->RAWContent = $param['content'];
            }
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
}
?>