<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-04
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface Interface_XML_Atom_ToDOMXML
{
    public function toXML(DOMDocument $doc, $elementName);
}
?>