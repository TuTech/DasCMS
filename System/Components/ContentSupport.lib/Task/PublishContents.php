<?php
class Task_PublishContents implements Interface_SchedulerTask
{
    private $message = 'OK';
    private $code = 0;

    /**
     * offset for next run
     * @return int seconds
     */
    public function getInterval()
    {
        return Interface_SchedulerTask::MINUTE;
    }

    /**
     * @return void
     */
    public function run()
    {
		$updated = Controller_ContentPublication::getInstance()->updatePublications();
        $this->message = sprintf(
			'%d contents published',
			$updated
		);
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

	public function getEnd()
    {
        return null;
    }
}
?>