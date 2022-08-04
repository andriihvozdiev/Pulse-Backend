<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>
<title>Reset Password</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;">
<style type="text/css">
body{margin:0px;padding:0px;text-align:left;}
html{width: 100%; }
img {border:0px;text-decoration:none;display:block; outline:none;}
a,a:hover{color:#FFF;text-decoration:none;}.ReadMsgBody{width: 100%; background-color: #ffffff;}.ExternalClass{width: 100%; background-color: #ffffff;}
table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }  
table[class=social]{ text-align:right;}
.contact-text{font:Bold 14px Arial, Helvetica, sans-serif; color:#FFF; padding-left:4px;}
.border-bg{ border-top:#2C3B63 solid 4px; background:#11BCFA;}
.borter-inner-bottom{ border-bottom:#e24851 solid 1px;}
.borter-inner-top{ border-top:#f2767d solid 1px;}
.borter-footer-bottom{ border-bottom:#ececec solid 1px; border-top:#eb5e66 solid 3px;}
.borte-footer-inner-borter{ border-bottom:#cf4850 solid 3px;}
.header-space{padding:0px 20px 0px 20px;}
@media only screen and (max-width:640px)
{
body{width:auto!important;}
.main{width:440px !important;margin:0px; padding:0px;}
.two-left{width:440px !important; text-align: center!important;}
.two-left-inner{width: 376px !important; text-align: center!important;}
.header-space{padding:30px 0px 30px 0px;}
}
@media only screen and (max-width:479px)
{
body{width:auto!important;}
.main{width:280px !important;margin:0px; padding:0px;}
.two-left{width: 280px !important; text-align: center!important;}
.two-left-inner{width: 216px !important; text-align: center!important;}
.space1{padding:35px 0px 35px 0px;}
table[class=social]{ width:100%; text-align:center; margin-top:20px;}
table[class=contact]{ width:100%; text-align:center; font:12px;}
.contact-space{padding:15px 0px 15px 0px;}
.header-space{padding:30px 0px 30px 0px;}
}
</style>

</head>
<body>
<table class="main-bg" cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
  <tbody>
   <tr>
      <td style="padding:50px 0px 50px 0px;" align="center" valign="top">
         <table class="main" cellpadding="0" cellspacing="0" align="center" border="0" width="600">
            <tbody>
               <tr>
                  <td align="left" valign="top">
                     <table class="main" cellpadding="0" cellspacing="0" align="center" border="0" width="600">
                        <tbody>
                           <tr>
                              <td style="padding:30px 20px 30px 20px;background:#2C3B63;" class="border-bg" align="left" valign="top">
                                 <table cellpadding="0" cellspacing="0" align="center" border="0" width="100%">
                                    <tbody>
                                       <tr>
                                          <td style="display: inline-flex; flex-direction: row; align-items: center; background:#2C3B63;" align="left" valign="middle" width="100%">   
                                                <img src="http://petropulse.azurewebsites.net/images/logo.png" style="width:50px; float:left;">&nbsp;&nbsp;&nbsp;
                                                <p style="color:#ffffff; font:normal 24px Arial, Helvetica, sans-serif;">  PULSE </p>
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td valign="top">
                     <table class="main" cellpadding="0" cellspacing="0" border="0" width="600">
                        <tbody>
                           <tr>
                              <td style="background-color:#f7f7f7; text-align:justify; font:normal 15px Arial, Helvetica, sans-serif;line-height:18px; padding:20px 25px 20px 25px;" valign="top">
                               Hi, <?=$name?> <br/> <br/> Please click on below link to verify your PULSE account.
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
               <tr>
                  <td align="left" valign="top">
                     <table class="main" cellpadding="0" cellspacing="0" border="0" width="600">
                        <tbody>
                           <tr>
                              <td style="background-color:#f7f7f7; text-align:center; font:normal 16px Arial, Helvetica, sans-serif;line-height:18px; padding-top:0px; padding-bottom:15px;" align="left" bgcolor="#ffffff" valign="top">
                                 
                              <a href="{{ url('/') }}/deeplink_verification?userid=<?=$userid?>" class="CSS3_Button" id="Theme-a" style="padding:10px 20px; background:#2C3B63; color:#fff;">Verify Email</a>
                              </td>
                           </tr>
                           <tr>
                              <td style="background-color:#2C3B63; padding:16px 0px 14px 0px;border-bottom:#2C3B63 solid 3px;text-align:center;" class="borte-footer-inner-borter" align="left"  valign="top">
                                 <table cellpadding="0" cellspacing="0" align="center" border="0" width="204">
                                    <tbody>
                                       <tr>
                                          <span style="color:#ffffff;">Copyright © 2017 Pulse</span>
                                          
                                       </tr>
                                    </tbody>
                                 </table>
                              </td>
                           </tr>
                        </tbody>
                     </table>
                  </td>
               </tr>
            </tbody>
         </table>
      </td>
   </tr>
</tbody>
</table>
</body>
</html>