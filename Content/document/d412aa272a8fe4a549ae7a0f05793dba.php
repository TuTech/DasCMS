<?php /*BambusDocumentFile1*/ if(!class_exists("Bambus"))exit();?>c/o Helmholtz Association Brussels Office<br />
Nina Baumeister<br />
Rue du Trône 98<br />
B-1050 Brussels, Belgium<br />
Tel.: +32 2 5000-970<br />
Fax: +32 2 5000-980<br />
E-Mail: remat-at-tutech.de<br /><br />

<?php
if (isset($_POST["website"])){echo "<p><b>Sorry,</b><br />this page is under construction and is without any functionality until now.</p>";}
?>
<br /><br />
<form action="?page=Project_contact_details" method="post">
    <table bgcolor="#ffffff" border="0" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
                                
            <tr>
                <td align="right">
                         Title:
                    
                </td>
                <td>
                    <select name="title" size="1">
                        <option>
                             Ms.
                        </option>
                        <option>
                             Mrs.
                        </option>
                        <option>
                             Mr.
                        </option>
                        <option>
                             Dr.
                        </option>
                        <option>
                             Prof.
                        </option>
                    </select>
                    <!-- <input name="gender" value="female" type="radio">
                        <font face="helvetica, arial, geneva, sans-serif" size="2">
                             female
                        </font>
                        <input name="gender" value="male" checked="checked" type="radio">
                            <font face="helvetica, arial, geneva, sans-serif" size="2">
                                 male
                            </font> -->
                        </td>
                    </tr>
                    <tr>
                        <td align="right">
                            <font face="helvetica, arial, geneva, sans-serif" size="2">
                                 Family-Name:
                            </font>
                        </td>
                        <td>
                            <input name="surname" size="40" maxlength="40" type="text">
                            </td>
                        </tr>
                        <tr>
                            <td align="right">
                                <font face="helvetica, arial, geneva, sans-serif" size="2">
                                     First-Name:
                                </font>
                            </td>
                            <td>
                                <input name="firstname" size="40" maxlength="40" type="text">
                                </td>
                            </tr>
                            <tr>
                                <td align="right">
                                    <font face="helvetica, arial, geneva, sans-serif" size="2">
                                         Organisation:
                                    </font>
                                </td>
                                <td>
                                    <input name="institution" size="40" maxlength="40" type="text">
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right">
                                        <font face="helvetica, arial, geneva, sans-serif" size="2">
                                             Function/Job Title:
                                        </font>
                                    </td>
                                    <td>
                                        <input name="department" size="40" maxlength="40" type="text">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right">
                                            <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                 Street/P.O. Box:
                                            </font>
                                        </td>
                                        <td>
                                            <input name="street" size="40" maxlength="40" type="text">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td align="right">
                                                <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                     Postal Code:
                                                </font>
                                            </td>
                                            <td>
                                                <input name="postcode" size="10" maxlength="10" type="text">
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="right">
                                                    <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                         City:
                                                    </font>
                                                </td>
                                                <td>
                                                    <input name="city" size="30" maxlength="30" type="text">
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td align="right">
                                                        <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                             Country:
                                                        </font>
                                                    </td>
                                                    <td>
                                                        <input name="country" size="30" maxlength="30" type="text">
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td align="right">
                                                            <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                                 Phone:
                                                            </font>
                                                        </td>
                                                        <td>
                                                            <input name="fon" size="20" maxlength="20" type="text">
                                                            </td>
                                                        </tr>
                                                        <!-- <tr>
                                                            <td align="right">
                                                                <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                                     FAX:
                                                                </font>
                                                            </td>
                                                            <td>
                                                                <input name="fax" size="20" maxlength="20" type="text">
                                                                </td>
                                                            </tr> -->
                                                            <tr>
                                                                <td align="right">
                                                                    <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                                         E-Mail <b>(required)</b>:
                                                                    </font>
                                                                </td>
                                                                <td>
                                                                    <input name="email" size="40" maxlength="40" type="text">
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td align="right">
                                                                        <font face="helvetica, arial, geneva, sans-serif" size="2">
                                                                             Web-site:
                                                                        </font>
                                                                    </td>
                                                                    <td>
                                                                        <input name="website" size="40" maxlength="400" value="http://" type="text">
                                                                        </td>
                                                                    </tr>
                                                                    <tr>
                                                                        
                                                                        <td colspan="2"><br /><br />
                                                                             <div class="news" align="center"><h3>Your Message:</h3><p>
                                                                            <textarea name="comment" cols="50" rows="4"></textarea>
                                                                        </p> </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>

<p align="center"><strong>Privacy statement: The data will only be used by the consortium for the purposes of the ReMaT project.</strong> <br />

 <input type="radio" name="contactopt" value=" Yes, please send me up to date information" checked> Yes, please send me up to date information<br />
    <input type="radio" name="contactopt" value=" No, only use my data once for contact purposes and then delete it"> No, only use my data once for contact purposes and then delete it<br />
 
<br /><br />
                                                            <input name="confirm" value="1" type="hidden">
                                                                 
                                                                
                                                                    <input value="clear form" type="reset">
                                                                        <input value="next&nbsp;&gt;&gt;" type="submit">
                                                                        </p>
                                                                    </form>
                                                                     
                                                                     