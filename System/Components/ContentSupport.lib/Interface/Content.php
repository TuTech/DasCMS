<?php
/**
 *
 * @author lse
 */
interface Interface_Content {

	public function setParentView(VSpore $pv);

	/**
	 * @return VSpore
	 */
	public function getParentView();

	public function attachComposite(Interface_Composites_Attachable $composite);

	public function hasComposite($composite);

	/**
	 * @return string
	 */
	public function getId();

	/**
	 * @return string
	 */
	public function getGUID();

	/**
	 * Icon for this filetype
	 * @return WIcon
	 */
	public static function defaultIcon();

	/**
	 * Icon for this object
	 * @return WIcon
	 */
	public function getIcon();

	/**
	 * is this content accessible by the public
	 * @return bool
	 */
	public function isPublished();

	/**
	 * Icon for this object
	 * @return WImage
	 */
	public function getPreviewImage();

	public function setPreviewImage($previewAlias);

	/**
	 * @return string
	 */
	public function getTitle();

	/**
	 * @return string
	 */
	public function getSubTitle();

	/**
	 * @return string
	 */
	public function getMimeType();

	/**
	 * @param string $value
	 */
	public function setTitle($value);

	/**
	 * allowed html: <b><i><u><s><strong><sub><sup><small><br>
	 * @param string $value
	 */
	public function setSubTitle($value);

	/**
	 * @return array
	 */
	public function getTags();

	/**
	 * @param array|string $value
	 */
	public function setTags($value);

	/**
	 * @return string
	 */
	public function getAlias();

	/**
	 * @return int
	 */
	public function getSize();

	/**
	 * @return int
	 */
	public function getPubDate();

	/**
	 * @param int|string $value
	 */
	public function setPubDate($value);

	/**
	 * @return string
	 */
	public function getSource();

	/**
	 * @return string
	 */
	public function getContent();

	/**
	 * @param string $value
	 */
	public function setContent($value);

	/**
	 * @return string
	 */
	public function getDescription();

	/**
	 * @param string $value
	 */
	public function setDescription($value);

	/**
	 * @return string
	 */
	public function getText();

	public function __construct($Id);	//object should load its data here
}
?>
