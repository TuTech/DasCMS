<?php
/**
 * File upload form as formatter
 *
 * @author lse
 */
class View_Content_FileUpload
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	const MODE_WHITELIST = true;
	const MODE_BLACKLIST = false;

	protected $maxSize = 10485760,//10 MB
			  $forceTags = array(),
			  $tagFilterList = array(),
			  $tagFilterMode = false, //self::MODE_BLACKLIST,
			  $autoPublish = true,
			  $infoText = '',
			  $submitCaption = 'Ok',
			  $okMessage = 'Uploaded',
			  $failedMessage = 'Upload failed',
			  $optionalTags = array();

	/**
	 * render html
	 * @return string
	 */
	public function toXHTML() {
		$val = '';
		if($this->shouldDisplay()){
			//read tags to assign from current content
			$tagsToAssign = $this->forceTags;

			$tags = $this->content->getTags();
			$prefix = '@upload:';
			$prefixLen = strlen($prefix);
			foreach ($tags as $tag){
				//compare to lower
				$lowerTag = strtolower($tag);
				
				//has assign prefix
				if(substr($lowerTag, 0, $prefixLen) == $prefix){

					//compare to white-/black-list
					$assign = in_array(substr($lowerTag, $prefixLen), $this->tagFilterList);
					
					//inverse for blacklisting
					if(!$this->tagFilterMode){
						$assign = !$assign;
					}

					//assign
					if($assign){
						$tagsToAssign[] = substr($tag, $prefixLen);
					}
				}
			}
			$tagsToAssign = array_unique($tagsToAssign);
			sort($tagsToAssign);

			//handle upload if exists
			$ucf = new UCFileUpload();

			//allow users to set more tags
			if(count($this->optionalTags)){
				$ucf->setOptionalTags($this->optionalTags);
			}

			$ucf->processUpload(array(
				'tags' => $tagsToAssign,
				'publish' => $this->autoPublish
			));

			//show message or reload page
			$val .= $ucf->uploadMessage(array(
				'okMessage' => $this->okMessage,
				'failedMessage' => $this->failedMessage
			));

			//display upload form
			$val .= $ucf->uploadForm(array(
				'maxSize' => $this->maxSize,
				'text' => $this->infoText,
				'submitText' => $this->submitCaption
			));


		}
		return $val;
	}

	/**
	 * max bytes allowed for upload
	 * @return int
	 */
	public function getMaxSize(){
		return $this->maxSize;
	}

	/**
	 * tags that are always assigned
	 * @return array
	 */
	public function getForceTags(){
		return $this->forceTags;
	}

	/**
	 * tags to decide if this should be displayed for that content
	 * @return array
	 */
	public function getTagFilterList(){
		return $this->tagFilterList;
	}

	/**
	 * blacklist or whitelist
	 * @return bool
	 */
	public function getTagFilterMode(){
		return $this->tagFilterMode;
	}

	/**
	 * publish uploaded contents
	 * @return bool
	 */
	public function getAutoPublish(){
		return $this->autoPublish;
	}

	/**
	 * info text paragraph
	 * @return string
	 */
	public function getInfoText(){
		return $this->infoText;
	}

	/**
	 * caption of the submit button
	 * @return string
	 */
	public function getSubmitCaption(){
		return $this->submitCaption;
	}

	/**
	 * message to display if upload was successful
	 * @return string
	 */
	public function getOkMessage(){
		return $this->okMessage;
	}

	/**
	 * message to display if upload failed
	 * @return string
	 */
	public function getFailedMessage(){
		return $this->failedMessage;
	}

	/**
	 * tags the uploader may assign
	 * @return array
	 */
	public function getOptionalTags(){
		return $this->optionalTags;
	}

	/**
	 * max bytes allowed for upload
	 * @param int $value
	 */
	public function setMaxSize($value){
		$this->maxSize = intval($value);
	}

	/**
	 * tags that are always assigned
	 * @param array $value
	 */
	public function setForceTags(array $value){
		$this->forceTags = $value;
	}

	/**
	 * tags to decide if this should be displayed for that content
	 * @param array $value
	 */
	public function setTagFilterList(array $value){
		$this->tagFilterList = $value;
	}

	/**
	 * blacklist or whitelist
	 * @param bool $value
	 */
	public function setTagFilterMode($value){
		$this->tagFilterMode = !!$value;
	}

	/**
	 * publish uploaded contents
	 * @param bool $value
	 */
	public function setAutoPublish($value){
		$this->autoPublish = !!$value;
	}

	/**
	 * info text paragraph
	 * @param string $value
	 */
	public function setInfoText($value){
		$this->infoText = strval($value);
	}

	/**
	 * caption of the submit button
	 * @param string $value
	 */
	public function setSubmitCaption($value){
		$this->submitCaption = strval($value);
	}

	/**
	 * message to display if upload was successful
	 * @param string $value
	 */
	public function setOkMessage($value){
		$this->okMessage = strval($value);
	}

	/**
	 * message to display if upload failed
	 * @param string $value 
	 */
	public function setFailedMessage($value){
		$this->failedMessage = strval($value);
	}

	/**
	 * tags the uploader may assign
	 * @param array $value
	 */
	public function setOptionalTags(array $value){
		$this->optionalTags = $value;
	}

	/**
	 * store this elements
	 * @return array
	 */
	protected function getPersistentAttributes() {
		return array(
			'maxSize',
			'forceTags',
			'tagFilterList',
			'tagFilterMode',
			'autoPublish',
			'infoText',
			'submitCaption',
			'okMessage',
			'failedMessage',
			'optionalTags'
		);
	}
}
?>