<?php /*BambusDocumentFile1*/ if(!class_exists("Bambus"))exit();?><?php

error_reporting(4095);

$etitle = "[ReMaT] online registration";
$email_to_bcc="kilian@tutech.de";
$email_to="kilian@tutech.de";
$nextstep = (empty($post['nextstep'])) ? '' : $post['nextstep'];
$submitoff = (empty($post['submitoff'])) ? '' : $post['submitoff'];

// notes to be shown on the top of the page
$note = (empty($post['note'])) ? '' : $post['note'];


// form fields
$surname = (empty($post['surname'])) ? '' : $post['surname'];
$street = (empty($post['street'])) ? '' : $post['street'];
$fon = (empty($post['fon'])) ? '' : $post['fon'];
$institution = (empty($post['institution'])) ? '' : $post['institution'];
$department2 = (empty($post['department2'])) ? '' : $post['department2'];
$supervisoraddress = (empty($post['supervisoraddress'])) ? '' : $post['supervisoraddress'];
$supervisor = (empty($post['supervisor'])) ? '' : $post['supervisor'];
$travelstipend = (empty($post['travelstipend'])) ? '' : $post['travelstipend'];

// <Start> Template settings
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
// </Start> Templatevorarbeiten

// DEBUGGING <start>
print "<table border=1><tr><td colspan=2><b>Debug Information (".$submitoff.")</b></td></tr>\n";
print "<tr><td colspan=2>".$html."</td></tr>";
print "<tr><td colspan=2><pre>".$emailtmpl."</pre></td></tr>";
foreach(array_keys($post) as $pstkey)
{
    $post[$pstkey] = htmlspecialchars(($post[$pstkey]));
    print "<tr><td>$pstkey</td><td>".utf8_encode($post[$pstkey])."</td></tr>\n";
}
print "</table>";

if (!empty($nextstep))
{
	$note = (empty($note)) ? '' : $note;
	echo $note;
	echo "<p>DEBUG: <pre>",utf8_encode($nextstep),"</pre></p>";
}




if (!empty($post['email']))
{
  	$email = ($post['email']);
	//
	// email checker: is the given address valid?
	//


    $pattern = "/^[-_.[:alnum:]]+@((([[:alnum:]]|[[:alnum:]][[:alnum:]-]*[[:alnum:]])\.)+";
    $pattern .= "(ad|ae|aero|af|ag|ai|al|am|an|ao|aq|ar|arpa|as|at|au|aw|az|ba|bb|";
    $pattern .= "bd|be|bf|bg|bh|bi|biz|bj|bm|bn|bo|br|bs|bt|bv|bw|by|";
    $pattern .= "bz|ca|cc|cd|cf|cg|ch|ci|ck|cl|cm|cn|co|com|coop|cr|";
    $pattern .= "cs|cu|cv|cx|cy|cz|de|dj|dk|dm|do|dz|ec|edu|ee|eg|eh|";
    $pattern .= "er|es|et|eu|fi|fj|fk|fm|fo|fr|ga|gb|gd|ge|gf|gh|gi|";
    $pattern .= "gl|gm|gn|gov|gp|gq|gr|gs|gt|gu|gw|gy|hk|hm|hn|hr|ht|";
    $pattern .= "hu|id|ie|il|in|info|int|io|iq|ir|is|it|jm|jo|jp|ke|";
    $pattern .= "kg|kh|ki|km|kn|kp|kr|kw|ky|kz|la|lb|lc|li|lk|lr|ls|";
    $pattern .= "lt|lu|lv|ly|ma|mc|md|mg|mh|mil|mk|ml|mm|mn|mo|mobi|mp|mq|";
    $pattern .= "mr|ms|mt|mu|museum|mv|mw|mx|my|mz|na|name|nc|ne|net|";
    $pattern .= "nf|ng|ni|nl|no|np|nr|nt|nu|nz|om|org|pa|pe|pf|pg|ph|";
    $pattern .= "pk|pl|pm|pn|pr|pro|ps|pt|pw|py|qa|re|ro|ru|rw|sa|sb|";
    $pattern .= "sc|sd|se|sg|sh|si|sj|sk|sl|sm|sn|so|sr|st|su|sv|sy|";
    $pattern .= "sz|tc|td|tf|tg|th|tj|tk|tm|tn|to|tp|tr|tt|tv|tw|tz|";
    $pattern .= "ua|ug|uk|um|us|uy|uz|va|vc|ve|vg|vi|vn|vu|wf|ws|ye|";
    $pattern .= "yt|yu|za|zm|zw)$";
    $pattern .= "|(([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5])\.){3}([0-9][0-9]?|[0-1][0-9][0-9]|[2][0-4][0-9]|[2][5][0-5]))$/i";

	if(!preg_match($pattern, $email))
	{
	    $note .= "Please check your e-mail address. Is it really your correct address?";
	    $emailcheck1="email";
		// don't allow to submit the form:
	    $submitoff="On";
        print "<span style='color:#FF0000;'>ERROR: $email is not a valid address.</span>";
	}
}
else
{
	$submitoff="On";
}

if (!empty($post['supervisoremail']))
{
  	$supervisoremail = ($post['supervisoremail']);
  	if(!ereg("^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@([a-zA-Z0-9-]+\.)+([a-zA-Z]{2,4})$", $supervisoremail))
  	{
	//
	// email checker: is the given address valid?
	//
    $note .= "<p>Please check supervisor's e-mail address. Is it really the correct address?</p>";
    $emailcheck2="supervisoremail";
  	}
}



////////////// E-Mail construction ///////////



// emailbody (template e-mail.tpl)

if ($travelstipend == "on"){
$travelstipend = "I wish to be considered for a travel stipend";
}

// Header
$REMOTE_ADDR = GetEnv("REMOTE_ADDR");
$ccto = (empty($post['email'])) ? '' : $post['email'];
$email_betreff = "$etitle";
$header = "From:$surname<DO-NOT-REPLY@remat-project.eu>\r\n";
$header .= "Reply-To: DO-NOT-REPLY@remat-project.eu\r\n";
$header .= "cc: $ccto\r\n";
$header .= "Bcc: $email_to_bcc\r\n";
$header .= "X-Mailer: PHP/" . phpversion(). "\r\n";
$header .= "X-Sender-IP: $REMOTE_ADDR\r\n";
$header .= "Content-Type: text/plain\r\n";
$header .= "\r\n";
$bodydec = ($emailtmpl);





if($nextstep=="3")
{

///////////////////  Step 3 (finish, send e-mail, write log file) //////////////////// ---->


print "<h2>Debug E-Mail command</h2><pre>".$email_to."\n".$email_betreff."\n".$bodydec."\n".$header."</pre>";
// mail($email_to,$email_betreff,$bodydec,$header);
///// mail($email_to,test,test,$header);

print "Dear" . $surname . ",<br>Your registration was successfully sent. You will receive a copy.<br />"; 

///
///
// Log file
///
///
$fp = fopen("Content/logs/logfile.txt","a+");
$datum = date("d.m.y", time());
$zeit = strftime("%H:%M:%S", time());
$logitem = "".$datum." ".$zeit.",".$bodydec.",".$header."\n\n\n\n";
fputs($fp, $logitem);
fclose($fp);

$fp = fopen("Content/logs/export.csv","a+");
$datum = date("d.m.y", time());
$zeit = strftime("%H:%M:%S", time());
$logitem = "".$datum." ".$zeit."|".$surname."|".$csvorganisation."|".$csvjob."|".$csvstr."|".$csvpcode."|".$csvcity."|".$csvcountry."|".$csvfon."|".$csvfax."|".$scvemail."|".$csvweb."|".$csvok."\n";
fputs($fp, $logitem);
fclose($fp);

echo "<pre>$logitem</pre>";
    
}
///////////////////  Step 2 (confirm) //////////////////// 

if($nextstep=="2")
{
 $backbutton = "<input type='button' value='&lt;&lt;&nbsp;back' onClick='history.back()'>";

 if(!isset($submitoff))
  {
  $oksendbutton = "<input type='submit' value='OK, send E-Mail &nbsp;&gt;&gt;'>";
  }
 else
 {
  $oksendbutton = "<input value='next&nbsp;&gt;&gt;' type='submit'>";
 }

}
///////////////////  Step 1 ////////////////////

if($nextstep=="")
{
?>
<p align="right">
<input name="note" value="<?php echo $note;?>" type="hidden">
<?php

if (!isset($submitoff))
{
	if ($nextstep=="") {
		?>
		<input name="nextstep" value="2" type="hidden">
		<input value="next step (2/3)&nbsp;&gt;&gt;" type="submit">
		<?php
	}
	if ($nextstep=="2") {
		?>
		<input name="nextstep" value="3" type="hidden">
		<input value="last step (3/3)&nbsp;&gt;&gt;" type="submit">
		<?php
	}
}
else
{
	?>
	<input value="try again to send valid data &nbsp;&gt;&gt;" type="submit">
	<?php
}
?>
                                                   </p>
                                                        </form>
  <?php
}
else
{
	echo "<h2>wrong call of this page</h2>";
}
?>                                                      