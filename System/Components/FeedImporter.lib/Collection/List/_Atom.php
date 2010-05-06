<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-01
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _Collection_List
 */
abstract class _Collection_List_Atom extends _Collection_List  
{
    protected function __construct(array $items = array())
    {
        $this->items = $this->assertArrayType($items);
    }
    
    abstract protected function typeMatch($element);
    
    protected function assertType($element)
    {
        if(!$this->typeMatch($element))
        {
            throw new Exception('type mismatch');
        }
    }
    
    protected function assertArrayType(array $items)
    {
        foreach ($items as $item) 
        {
        	$this->assertType($item);
        }
        return $items;
    }
}
?>