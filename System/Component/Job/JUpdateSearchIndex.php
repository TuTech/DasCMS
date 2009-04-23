<?php
/**
 * @copyright Lutz Selke/TuTech Innovation GmbH
 * @author Lutz Selke <selke@tutech.de>
 * @since 2009-04-22
 * @license GNU General Public License 3
 */
/**
 * @package Bambus
 * @subpackage Job
 */
class JUpdateSearchIndex extends BJob 
{
    private $message = 'OK';
    private $code = 0;
    
    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return BJob::SECOND*20;
    }
    
    /**
     * @return void
     */
    public function run()
    {
        $content = SSearchIndexer::nextToUpdate();
        $stat = 'ok';
        if($content instanceof BContent)
        {
            SSearchIndexer::updateFeatures($content);
            $this->message = 'updated '.$content->getId();
            $stat = 'new';
        }
        else
        {
            $this->message = 'nothing to do';
        }
        return $stat;
    }
    
    /**
     * get status text for the processed result
     * @return string (max length 64)
     */
    public function getStatusMessage()
    {
        return $this->message;
    }
    
    /**
     * get status code
     * @return int status const
     */
    public function getStatusCode()
    {
        return $this->code;
    }
}
?>