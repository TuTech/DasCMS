<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2008-12-03
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage _Import_HTTP
 */
class Import_HTTP_AtomFeed extends _Import_HTTP 
{
    protected function hadUpdate(XML_Atom_Text $guidNode, XML_Atom_Date $update)
    {
        $res = QImportHTTPAtomFeed::updateMatches($guidNode->getText(), $update->getTimestamp());
        list($updateInDB) = $res->fetch();
        return !$updateInDB;
    }
    
    /**
     * @return BContent
     */
    protected function loadContentForGUID(XML_Atom_Text $guidNode)
    {
        //open or create content
        $res = QImportHTTPAtomFeed::getContentAlias($guidNode->getText());
        if($res->getRowCount())
        {
            list($alias) = $res->fetch();
            $content = CPage::Open($alias);
        }
        else
        {
            $content = CPage::Create($guidNode->getText());
        }
        return $content;
    }
    
    protected function importEntry($feedID, XML_Atom_Entry $e)
    {
        $e_content = $e->getContent(); 
        if($e_content != null && $e_content instanceof Interface_XML_Atom_TextContent)
        {
            if($this->hadUpdate($e->getId(), $e->getUpdated()))
            {
                $DB = DSQL::getSharedInstance();
                $DB->beginTransaction();
                $content = $this->loadContentForGUID($e->getId());
                $content->setTitle($e->getTitle()->getText());
                $summary = $e->getSummary();
                if($summary != null)
                {
                    $content->setDescription($summary->getText());
                }
                $pubDate = $e->getUpdated();
                if($pubDate)
                {
                    $content->setPubDate($pubDate->getTimestamp());
                }
                $categories = $e->getCategories();
                $cats = array();
                for($categories->startIteration();$categories->valid(); $categories->next())
                {
                    $cats[] = $categories->get()->getTerm();
                }
                $content->setTags($cats);
                $content->setContent($e_content->getText());
                QImportHTTPAtomFeed::setImport($feedID, $e->getId()->getText(), $e->getUpdated()->getTimestamp(), $content->getId());
                $content->Save();
                $DB->commit();
            }
        }
    }
    
    /**
     * returns number of failed imports
     * @return int
     */
    public function import($id, $uri)
    {
        $data = $this->httpGet($uri);
        $feed = XML_AtomParser::ParseString($data);
        $failed = 0;
        if($feed->getType() == 'entry')
        {
            try{
                $this->importEntry($id, $feed->getEntryTree());
            }catch (Exception $e){$failed++; echo '<h3>',$e->getMessage(),'</h3><p><b>', $e->getCode(), ' in ', $e->getFile(), ' at ', $e->getLine(), '</b>', '<br /><code>',$e->getTraceAsString(), '</code></p>';}
        }
        else
        {
            $tree = $feed->getFeedTree();
            $entries = $tree->getEntries();
            for($entries->startIteration(); $entries->valid(); $entries->next())
            {
                try{
                    $this->importEntry($id, $entries->get());
                }catch (Exception $e){$failed++; echo '<h3>',$e->getMessage(),'</h3><p><b>', $e->getCode(), ' in ', $e->getFile(), ' at ', $e->getLine(), '</b>', '<br /><code>',$e->getTraceAsString(), '</code></p>';}
            }
        }
        return $failed;
    }
}
?>