<?php
class API_Controller_ContentInfo implements API_Interface_PathComponent,API_Interface_AcceptsGet
{
	protected $content;

	public function  __construct(Interface_Content $content) {
		$this->content = $content;
	}

	public function getControllerName() {
		return $this->content->getAlias();
	}

	public function resolveSubPath(array $path) {
		return $this;
	}

	public function httpGet($queryString) {
		return array(
			'guid' => $this->content->getGUID(),
			'title' => $this->content->getTitle(),
			'subTitle' => $this->content->getSubTitle(),
			'description' => $this->content->getDescription(),
			'alias' => $this->content->getAlias(),
			'segments' => array(
				array('guid' => 'data'),
				array('guid' => 'history'),
				array('guid' => 'relations'),
			)
		);
	}
}
?>