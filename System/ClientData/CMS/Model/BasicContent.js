CMS.Model.BasicContent = {
	_map:{},
	
	setAttrMap: function(map){
		this._map = map;
	},
	
	create: function(data){
		return CMS.Model._createMappedModel(data, this._map, {
			pubDate: function(date){ 
				if(!date) return ' ';
				var d = new Date(1000 * date);
				return d.getFullYear() + "/" +
					   d.getMonth() + "/" +
					   d.getDate() + " " +
					   (d.getHours() > 9 ? d.getHours() : '0'+d.getHours()) + ":" +
					   (d.getMinutes() > 9 ? d.getMinutes() : '0'+d.getMinutes());
			}
		});
	}
};