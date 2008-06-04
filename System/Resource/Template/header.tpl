<?xml version="1.0" encoding="UTF-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
       "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    	<base href="{Linker:myBase}" />
{ClientData}
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Bambus CMS: {ApplicationTitle} - {configuration:sitename}</title>
    </head>
    <body onload="body_load();" onunload="BCMSDestroy();">
    	<div id="BambusHeader">
			<!-- 
			<img id="BambusLogo"src="{cms:BambusLogo}" alt="Bambus CMS" title="{cms:version}" />
			<img id="Logout" src="{logoutImage}" alt="{translate:logout}" title="{translate:logout}" onclick="BCMSLogout('{translate:exit_management_area}', '{configuration:confirm_for_exit}', '{configuration:logout_on_exit}')" />
			<br class="clear" />
			-->
{WApplications}
{TaskBar}
		</div>
