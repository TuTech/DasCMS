<?php
header('Content-type: application/atom+xml');
require_once 'System/main.php';
error_reporting(4095);
PAuthentication::implied();
if(!empty($_SERVER['PATH_INFO']))
{
    $alias = substr($_SERVER['PATH_INFO'],1);
    if(Controller_Content::getSharedInstance()->contentExists($alias))
    {
        $self = Controller_Content::getSharedInstance()->openContent($alias);
        $content = Controller_Content::getSharedInstance()->accessContent($alias, $self);
        if($content instanceof IGeneratesFeed)
        {
            $allAliases = $content->getFeedItemAliases();
            $p = XML_Atom_Feed::fromContent($content);
            foreach ($allAliases as $entryAlias) 
            {
                try
                {
                    $entry = Controller_Content::getSharedInstance()->accessContent($entryAlias, $self);
                    if(!$entry instanceof CError)
                    {
                        $e = XML_Atom_Entry::fromContent($content, $entry);
                        $p->appendEntry($e);
                    }
                }
                catch (Exception $e){/*does not matter*/}
            }
            $doc = new DOMDocument('1.0', CHARSET);
            $doc->appendChild($p->toXML($doc, 'feed'));
            $doc->formatOutput = true;
            echo $doc->saveXML();
        }
    }
}
?>