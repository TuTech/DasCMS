CMS Template Commands
=====================

All commands have to be assigned to the template namespace to be executed
    http://www.bambuscms.org/2008/TemplateXML
*Via direct xmlns
    <foo xmlns="http://www.bambuscms.org/2008/TemplateXML" />
*Via indirect xmlns
    <bar xmlns="http://www.bambuscms.org/2008/TemplateXML">
        <foo />
    </bar>
*Via prefix
    <cms:bar xmlns:cms="http://www.bambuscms.org/2008/TemplateXML">
        <cms:foo />
    </cms:bar>


Content
-------
Show one property of one specific content
    <content alias="demo-2011-02-25" property="Title" />
*_alias_ the content's id or alias
*_property_ the content's property such as "Title"


Controller
----------
Call a function from from an template-supporting object referenced by a guid
    <controller guid="de.tutech.events" call="test">
        <param name="namedParameter" value="testing" /> 
    </controller>
*_guid_ references the class by it's GUID constant
*_call_ call a function if the object permits it


Env
---
Get a value from the template env
    <env get="stuff" />
*_get_ the name of the value to get


Head
----
** deprecated **
Render the html-head element 
    <head />


Html
----
** deprecated **
Render the html-wrapping tags with proper doctype declaration
    <html lang="en" doctype="html4"></html>
*_lang_ the language code for this page
*_doctype the name of the doctype to use


Runtime
-------
Get a value from the current runtime array
    <runtime get="stuff" />
*_get_ the name of the value to get


Scheduler
---------
Let the cms run tasks async the e.g. (de-)publicise contents
    <scheduler interval="10000" />
*_interval the time between calls in milli seconds


Site
----
Render html, head and body tags with proper doctype declaration, 
automatic css and script loading.
Recommended template root element
    <site>...</site>


Stats
-----
Displays run-time, mem-usage and ip address in hex
    <stats />


Text
----
Displays the given text.
Can be used to generate create tags with dynamic attributes
    <text value="text" />

    <text value="&lt;div class=&quot;" />
        dynamic element
    <text value="&quot;&gt;" />
        stuff in dynamic div
    <text value="&lt/div;&gt;" />
*_value_ the text to display


Treenavigation
--------------
Embed a Treenavigation
    <treenavigation show="navi" />
*_show_ the name of the navaigation to show here



URL
---
Get a URL parameter 
E.g. in a call like _index.php?page=foo_ this would return foo
    <url get="page" />
*_get_ the name of the parameter to show here



View
----
The main element to display cms content
    <view for="page" show="content" />
    
    <view for="page" show="previewimage" width="100" height="50" scale="1c" />

    <view for="page" show="formatter" use="my_content_formatter" />
*_for_ the name of the url to get the alias from
*_show_ what property of the content we want to display [1]
*_width_ if the content is sizeable like an image set the width to render it
*_height_ if the content is sizeable like an image set the height to render it
*_scale_ set the scale method for scaleable content [2]
*_color_ set the background color if the content is stainable like some scaled images 
*_use_ use a formatter with the name here
*_fixcontent_ force this field to diaplay always the same content identified by an alias

1. Properties
    - content (view)
    - description (view)
    - title (view)
    - subtitle (view)
    - pubdate (view)
      the pubdate in the format set in the config
    - author (view)
    - tags (view)
      the tags as csv-string
    - previewimage (view, width, height, scale, color)
    - type (view)
      the contents class name
    - property (view, use)
      any named property of the content (title, subTitle, createdBy, ...)
    - formatter (view, use)
      use a formatter to display content here
2. Scale options
the scale option consists of two chars:
first the number 0 or 1 to "scale to fit in" or "force size"
followed by one of the chars s,f,c for "Strech", "Fill" or "Crop"
Examples:
    - "0s" the largest image fitting in the defined width*height box
    - "1s" stretch the image to fill the boy exactly
    - "1c" stretch the image proportionally to fill the box completely an cut the rest 
