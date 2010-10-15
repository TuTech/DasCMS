<?php
/**
 * Description of Content
 *
 * @author lse
 */
class View_Content_Map
	extends
		_View_Content_Base
	implements
		Interface_View_DisplayXHTML,
		Interface_View_Content
{
	//http://maps.google.com/staticmap?center=40.714728,-73.998672&zoom=14&size=512x512&maptype=mobile\
	//&markers=40.702147,-74.015794
	//&key=MAPS_API_KEY&sensor=false

	protected $mapWidth = 100,
			  $mapHeight = 100,
			  $mapType = 'roadmap',
			  $zoom = 13,
			  $marker = true,
			  $sensor = 'false';

	public function toXHTML() {
		$val = '';
		if(Core::classExists('UGoogleServices')
				&& $this->content->hasComposite('Location')
				&& $this->shouldDisplay()
		){
			$location = $this->content->Location;
			if($location instanceof View_UIElement_ContentGeoAttribute){
				$long = $location->getLongitude();
				$lat  = $location->getLatitude();

				if(strlen($lat) > 0 && strlen($long) > 0){
					if(Core::settings()->get('google_maps_key') != ''){
						$poi = sprintf('%f,%f', $lat, $long);
	
						//map image
						$map = '<img src="http://maps.google.com/staticmap?%s" alt="Map" style="width:%dpx;height:%dpx" />';
						$urldata = array(
							'center' => $poi,
							'zoom'   => $this->zoom,
							'size'   => sprintf('%dx%d', $this->mapWidth, $this->mapHeight),
							'maptype'=> $this->mapType,
							'key' => Core::settings()->get('google_maps_key'),
							'sensor' => $this->sensor
						);
						if($this->marker){
							$urldata['markers'] = $poi;
						}
						
						$parts = array();
						foreach ($urldata as $key => $value){
							$parts[] = sprintf('%s=%s', $key, $value);
						}
	
						$map = sprintf($map, implode('&', $parts), $this->mapWidth, $this->mapHeight);
	
						//map link
						if(!empty ($this->linkTragetFrame)){
							//http://maps.google.com/?ll=53.200000,9.600000&z=13&q=hier@53.200000,9.600000&t=m
							$mapCode = array("roadmap" => 'm', "mobile" => 'm', "satellite" => 'k', "terrain" => 'p', "hybrid" => 'h');
							$link = '<a href="http://maps.google.com/?ll=%s&z=%d%s%s&t=%s"%s>%s</a>';
							$map = sprintf(
									$link,
									$poi,
									$this->zoom,
									($this->marker ? '&q='.urlencode($this->content->getTitle()) : ''),
									($this->marker ? '@'.$poi : ''),
									$mapCode[$this->mapType],
									sprintf(' target="%s"', $this->linkTragetFrame),
									$map
							);
						}
					}
					else{
						$map = sprintf(
							'<div style="width:%dpx;height:%dpx;overflow:hidden;">Google Maps key not defined</div>', 
							$this->mapWidth, 
							$this->mapHeight
						);
					}
					$val = $this->wrapXHTML('Map', $map);
				}
			}
		}
		elseif(Core::classExists('UGoogleServices')
				&& $this->content->hasComposite('Location')
				&& $this->shouldDisplay()
		){
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array(
			'mapWidth',
			'mapHeight',
			'mapType',
			'zoom',
			'linkTragetFrame',
			'marker',
			'sensor'
		);
	}

	public function getDidUseSensorForPosition() {
		return $this->sensor == 'true';
	}

	public function setDidUseSensorForPosition($value) {
		$this->sensor = ($value ? 'true' : 'false');
	}

	public function getShowMarker() {
		return $this->marker;
	}

	public function setShowMarker($value) {
		$this->marker = $value == true;
	}

	public function getLinkTargetFrame() {
		return parent::getLinkTargetFrame();
	}

	public function setLinkTargetFrame($value) {
		parent::setLinkTargetFrame($value);
	}

	public function getMapWidth(){
		return $this->mapWidth;
	}

	public function setMapWidth($value){
		if(!is_numeric($value) || ($value < 1) || ($value >4096)){
			return;
		}
		$this->mapWidth = $value;
	}

	public function getMapHeight(){
		return $this->mapHeight;
	}

	public function setMapHeight($value){
		if(!is_numeric($value) || ($value < 1) || ($value >4096)){
			return;
		}
		$this->mapHeight = $value;
	}

	public function getMapZoom(){
		return $this->mapHeight;
	}

	public function setMapZoom($value){
		if(!is_numeric($value) || ($value < 1) || ($value > 20)){
			return;
		}
		$this->mapHeight = intval($value);
	}

	public function getMapType(){
		return $this->mapType;
	}

	public function setMapType($value){
		if(in_array($value, array("roadmap", "mobile", "satellite", "terrain", "hybrid"))){
			$this->mapType = $value;
		}
	}
}
?>