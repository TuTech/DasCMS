<?php
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