CMS.Notification = ({
	show:function(msg, priority){
		
	},
	
	init:function(serverMsgSrc){
		$(function(){
			$(serverMsgSrc).delay(1000).fadeOut(1000);
		});
		return this;
	}
}).init("#notifier");