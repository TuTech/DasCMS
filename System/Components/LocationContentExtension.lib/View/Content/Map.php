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
			  $zoom = 0,
			  $marker = true,
			  $sensor = 'false';

	public function toXHTML() {
		$val = '';
		$poi = null;
		if($this->content->hasComposite('Location')
				&& $this->shouldDisplay()
		){
			//load data
			$gpsData = false;
			$location = $this->content->getLocation();
			if($location instanceof View_UIElement_ContentGeoAttribute){
				$Locations = Controller_Locations::getInstance();
				$Place = $Locations->getLocation($location->getName());
				if($Place != null){
					if($Place->hasCoordinates()){
						$poi = $Place->getCoordinates();
						$gpsData = true;
					}
					else{
						$poi = $Place->getAddress();
					}
					$zoom = max($this->zoom, $Place->getZoom());
				}
			}

			//display data 
			if(!empty ($poi)){
					/*
					 * http://maps.google.com/maps/api/staticmap
					 * ?center=Brooklyn+Bridge,New+York,NY
					 * &zoom=14
					 * &size=512x512
					 * &maptype=roadmap
					 * &markers=color:blue|label:S|40.702147,-74.015794
					 * &markers=color:red|color:red|label:C|40.718217,-73.998284
					 * &sensor=false
					 */
				//map image
				$map = '<img src="http://maps.google.com/maps/api/staticmap?%s" alt="Map of %s" title="%s" style="width:%dpx;height:%dpx" />';
				$urldata = array(
					'center' => $poi,
					'zoom'   => $zoom,
					'size'   => sprintf('%dx%d', $this->mapWidth, $this->mapHeight),
					'maptype'=> $this->mapType,
					'sensor' => $this->sensor
				);
				if($this->marker){
					$urldata['markers'] = $poi;
				}

				$parts = array();
				foreach ($urldata as $key => $value){
					$parts[] = sprintf('%s=%s', $key, String::htmlEncode($value));
				}
				$epoi = String::htmlEncode($poi);
				$map = sprintf($map, implode('&', $parts), $epoi, $epoi, $this->mapWidth, $this->mapHeight);

				//map link
				if(!empty ($this->linkTragetFrame)){
					//http://maps.google.com/?ll=53.200000,9.600000&z=13&q=hier@53.200000,9.600000&t=m
					$mapCode = array("roadmap" => 'm', "mobile" => 'm', "satellite" => 'k', "terrain" => 'p', "hybrid" => 'h');
					
					$urlData = array(
						'z' => $zoom,
						't' => $mapCode[$this->mapType],
						'q' => $poi
					);
					if($gpsData){
						$urlData['ll'] = $poi;
					}
					
					$tok = '?';
					$url = 'http://maps.google.com/';
					foreach ($urlData as $k => $v){
			            $url .= sprintf('%s%s=%s', $tok, urlencode($k), urlencode($v));
			        	$tok = '&';
			        }
					
					$map = sprintf(
							'<a href="%s"%s>%s</a>',
							$url,
							sprintf(' target="%s"', $this->linkTragetFrame),
							$map
					);
				}
				$val = $this->wrapXHTML('Map', $map);
			}
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
		return $this->zoom;
	}

	public function setMapZoom($value){
		if(!is_numeric($value) || ($value < 1) || ($value > 20)){
			return;
		}
		$this->zoom = intval($value);
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