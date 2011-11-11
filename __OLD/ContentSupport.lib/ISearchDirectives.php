<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-29
 * @license GNU General Public License 3
 */
/**
 * Use this Interface to disable indexing for your object or its attributes
 * @package Bambus
 * @subpackage Interface
 */
interface ISearchDirectives
{
    /**
     * @return boolean
     */
    public function allowSearchIndex();

    /**
     * @return boolean
     */
    public function isSearchIndexingEditable();

    /**
     * @param boolean $allow
     * @return void
     */
    public function changeSearchIndexingStatus($allow);

    /**
     * e.g. [Content, Title]
     * @return array
     */
    public function excludeAttributesFromSearchIndex();
}
?>