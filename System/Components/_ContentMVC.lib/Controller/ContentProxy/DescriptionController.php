<?php
class ContentProxy_DescriptionController
{
	protected $proxy;

	public function __construct(ContentProxyController $proxy) {
		$this->proxy = $proxy;
	}
}
?>