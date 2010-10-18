<?php
/**
 * external content
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-11-28
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _XML_Atom_Content
 */
class XML_Atom_Content_OutOfLine extends _XML_Atom_Content implements Interface_XML_Atom_ToDOMXML
{
    protected $src = null; 
    protected static $_attributes = array(
        'type' => _XML::NONE_OR_MORE,
        'src' => _XML::NONE_OR_MORE
    );

    protected function getAttributeDefinition()
    {
        return self::$_attributes;
    }
    
    protected function isDataNode()
    {
        return false;
    }
    
    protected function getNodeData()
    {
        return null;
    }
    
    /**
     * create a XML_Atom_Content_Other by node
     *
     * @param DOMNode $node
     * @return XML_Atom_Content_Other
     */
    public static function fromNode(DOMNode $node)
    {
        $content = new XML_Atom_Content_OutOfLine();
        $content->parseNodeAttributes($node, self::$_attributes);
        return $content;
    }
    
    public function getSource()
    {
        return $this->src;
    } 
    
    public function __toString()
    {
        return '';
    }
    
    /**
     * @return XML_Atom_Content_OutOfLine
     */
    public static function create($mimetype, $srcURI)
    {
        $o = new XML_Atom_Content_OutOfLine();
        $o->src = $srcURI;
        $o->type = $mimetype;
        return $o;
    }
}
?>