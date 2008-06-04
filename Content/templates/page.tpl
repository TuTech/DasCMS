<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <base href="{Linker:myBase}" />
        <meta http-equiv=Content-Type content="text/html; charset=UTF-8" />
        <link rel="stylesheet" href="./Content/stylesheets/default.css" type="text/css" media="all" />
         {rssfeeds} 
         {stylesheets}
        <!--[if gte IE 7]>
         -->
        <link rel="stylesheet" href="./Content/stylesheets/All_but_IE6_and_lower.css" type="text/css" media="all" />
        <!--
        <![endif]-->
        <title>
             {Title}
        </title>
        <meta name="description" content="{meta_description}" />
        <meta name="generator" content="Bambus CMS {cms:version}" />
        <meta name="keywords" content="{meta_keywords}" />
        <link rel="icon" href="./favicon.ico" />
    </head>
    <body>
        <div id="head">
            <h1 id="invisibleheader">
                 {sitename}
            </h1>
            <div id="topline">
            </div>
        </div>
        <div id="main">
                 {TreeNavigation:sagu}
             
            <h2>
                 {Title}
            </h2>
            <div id="content">
                 {Content}
            </div>
<span>mem: {cms:memusage}</span>
<span>gen: {cms:gentime}s</span>
{ListNavigation:page,test}
        </body>
    </html>
     