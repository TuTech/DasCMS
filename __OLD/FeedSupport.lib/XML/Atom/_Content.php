<?php
/**
 * Atom content 
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom
 */
abstract class _XML_Atom_Content extends _XML_Atom 
{
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
    );
    protected $type = 'text'; 
    protected $data;
    
    public function getType()
    {
        return $this->type;
    }
    
    public function __toString()
    {
        return strval($this->data);
    }

    protected function getElementDefinition()
    {
        return array();
    }
    
    protected function getAttributeDefinition()
    {
        return self::$_attributes;
    }
    
    protected function isDataNode()
    {
        return true;
    }
    
    protected function getNodeData()
    {
        return $this->data;
    }
    
}
?>