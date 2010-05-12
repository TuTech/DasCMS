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
			  $zoom = 13;

	public function toXHTML() {
		$val = '';
		if(Core::classExists('UGoogleServices')
				&& $this->content->hasComposite('Location')
				&& LConfiguration::get('google_maps_key') != ''
				&& $this->shouldDisplay()
		){
			$location = $this->content->Location;
			if($location instanceof WContentGeoAttribute){
				$long = $location->getLongitude();
				$lat  = $location->getLatitude();

				if(strlen($lat) > 0 && strlen($long) > 0){
					$poi = sprintf('%f,%f', $lat, $long);
					$map = '<img src="http://maps.google.com/staticmap?%s" alt="Map" style="width:%dpx;height:%dpx" />';
					$urldata = array(
						'center' => $poi,
						'zoom'   => $this->zoom,
						'size'   => sprintf('%dx%d', $this->mapWidth, $this->mapHeight),
						'maptype'=> $this->mapType,
						'markers'=> $poi,
						'key' => LConfiguration::get('google_maps_key'),
						'sensor' => 'false'
					);
					$parts = array();
					foreach ($urldata as $key => $value){
						$parts[] = sprintf('%s=%s', $key, $value);
					}

					$map = sprintf($map, implode('&', $parts), $this->mapWidth, $this->mapHeight);

					$val = $this->wrapXHTML('Map', $map);
				}
			}
		}
		return $val;
	}

	protected function getPersistentAttributes() {
		return array(
			'mapWidth',
			'mapHeight',
			'mapType',
			'zoom'
		);
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
		if(!is_numeric($value) || ($value < 1) || ($value >100)){
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