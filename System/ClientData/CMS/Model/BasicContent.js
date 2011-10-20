CMS.Model.BasicContent = {
	_map:{},
	
	setAttrMap: function(map){
		this._map = map;
	},
	
	create: function(data){
		var pad = function(x){ return x > 9 ? x : '0' + x };
		return CMS.Model._createMappedModel(data, this._map, {
			pubDate: function(date){ 
				if(!date) return ' ';
				var d = new Date(1000 * date);
				return d.getFullYear() + "/" +
					   pad(d.getMonth()) + "/" +
					   pad(d.getDate()) + " " +
					   pad(d.getHours()) + ":" +
					   pad(d.getMinutes());
			}
		});
	}
};