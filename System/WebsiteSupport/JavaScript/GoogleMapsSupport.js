//FIXME all the code needed to generate on the fly google-maps here

org.bambuscms.googlemaps = {"maps":{}};
org.bambuscms.googlemaps.initmaps = function(){
	for(map_id in org.bambuscms.googlemaps.maps){
		if (GBrowserIsCompatible()) {
			org.bambuscms.googlemaps.maps[map_id]['map'] = new GMap2($(map_id));
			org.bambuscms.googlemaps.maps[map_id]['point'] = new GLatLng(
				org.bambuscms.googlemaps.maps[map_id]['lat'],
				org.bambuscms.googlemaps.maps[map_id]['long']
			);
			org.bambuscms.googlemaps.maps[map_id]['map'].setCenter(org.bambuscms.googlemaps.maps[map_id]['point'], 13);
			org.bambuscms.googlemaps.maps[map_id]['map'].addOverlay(org.bambuscms.googlemaps.maps[map_id]['point']);
		}
	}
};
org.bambuscms.googlemaps.mkMap = function(id, la, lo){
	org.bambuscms.googlemaps.maps[id] = {'lat':la, 'long': lo};
};
org.bambuscms.addToBodyLoad(org.bambuscms.googlemaps.initmaps);

if(GUnload){
	org.bambuscms.addToBodyUnLoad(GUnload);
}


