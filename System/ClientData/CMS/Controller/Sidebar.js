CMS.Sidebar = ({
	_getSidebarForSelector: function(element){
		return $('#' + element.id.replace(/WSidebar-selector-/, "WSidebar-child-"));
	},
	
	switchTab:function(event){
		var oldElm, newElm;
		newElm = event.currentTarget;
		oldElm = $("#WSidebar-select .selectedWidget")[0];

		if(newElm.id == oldElm.id)return;

		oldElm.className = "";
		newElm.className = "selectedWidget";

		this._getSidebarForSelector(oldElm).css("display", 'none');
		this._getSidebarForSelector(newElm).css("display", 'block');
	},
	
	//static handler function
	switch_tab: function(event){
		return CMS.Sidebar.switchTab(event);
	},
	
	init:function(){
		$(function(){
			$('#WSidebar-select span').click(CMS.Sidebar.switch_tab);
		});
		return this;
	}
}).init();