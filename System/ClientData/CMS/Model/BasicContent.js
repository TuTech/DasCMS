CMS.Model.BasicContent = {
	_map:{},
	
	setAttrMap: function(map){
		this._map = map;
	},
	
	create: function(data){
		return CMS.Model._createMappedModel(data, this._map, {
			pubDate: function(date){ return (new Date(1000 * date)).toLocaleString(); }
		});
	}
};