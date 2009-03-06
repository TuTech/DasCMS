<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-10-09
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Interface
 */
interface ITemplateCommand
{
    //on template save 
    //suck all information from dom node and save them in public conf array for serialize
    public function __construct(DOMNode $node);
    
    //do inits n stuff - setUp() your children
    public function setUp(array $enviornment);
    
    //do what you have to do - run() your children
    public function run(array $environment);
    
    //clean up - tearDown() your children
    public function tearDown();
}
?>