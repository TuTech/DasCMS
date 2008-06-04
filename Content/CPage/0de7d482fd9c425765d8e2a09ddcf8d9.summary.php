<?php exit(); ?>
 Template settings
// Initialisierung BCMS bedingt

// wird ausgegeben, wenn Template nicht existiert: 
$emailtmpl = "E-Mail-Template not found";


// dieses Template fuer die E-Mail verwenden: 

$tplFile = BAMBUS_CMS_ROOT.'/Content/templates/e-mail.tpl';
if(file_exists($tplFile))
{
    $template = $Bambus->FileSystem->read($tplFile);
    $emailtmpl = $Bambus->BCMSString->bsprintv($template, $post);
}

$html = "Template not found";


// dieses Template fuer das Formular verwenden: 

$tplFile = BAMBUS_CMS_ROOT.'/Content/templates/regformzagreb.tpl';
if(file_exists($tplFile))
{
    $template = $Bambus->FileSystem->read($tplFile);
    $html = $Bambus->BCMSString->bsprintv($template, $post);
}


// Definition/ Zuweisung der verfügbaren Variablen
// z.b. array_keys($post) as $pstkey
//$contentArray = array(
//    'was' => 'auch', 
//    'immer' => 'zeug');

// Pfad zum Template
//$tplFile = BAMBUS_CMS_ROOT.'/Content/templates/e-mail.tpl';

// Template lesen und Werte einfügen
//if(file_exists($tplFile))
//{
//    $template = $FS->read($tplFile);
//    $html = $S->bsprintv($template, $post);
//}
// 