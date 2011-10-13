CMS.Toolbar = ({
	_hotKeyLookup:{},
	
	init: function(){
		$(function(){
			$('.CommandBarPanelItem').each(function(nr, item){
				var elm = $(item),
					action = elm.data('action'),
					hotkey = elm.data('hotkey');
				elm.click(function(){
					CMS.Document[action]();
				});
				if(hotkey){
					CMS._hotKeyLookup[hotkey] = function(){
						elm.click();
					};
				}
			});
		});
	}
}).init();