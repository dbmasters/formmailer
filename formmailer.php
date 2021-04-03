<?php
////////////////////////////////////////////////////////////////////////////
// dB Masters' PHP FormM@iler, Copyright (c) 2007 dB Masters Multimedia
// http://www.dbmasters.net/
// FormMailer comes with ABSOLUTELY NO WARRANTY
// Licensed under the AGPL
// See license.txt and readme.txt for details
////////////////////////////////////////////////////////////////////////////
// General Variables
	$check_referrer="no";
	$referring_domains="http://domain.com/,http://www.domain.com/,http://subdomain.domain.com/";

// options to use if hidden field "config" has a value of 0 sean1leahy@hotmail.com info@blueskyguttering.co.uk
// recipient info
	$charset[0]="UTF-8";
	$tomail[0]="";
	$cc_tomail[0]="";
	$bcc_tomail[0]="";
// Mail contents config
	$subject[0]="";
	$reply_to_field[0]="Email";
	$reply_to_name[0]="Name";
	$required_fields[0]="Name,Comments,Phone,Email,Postcode";
	$required_email_fields[0]="Email";
	$attachment_fields[0]="";
	$return_ip[0]="no";
	$mail_intro[0]="The following enquiry came from your  site:";
	$mail_fields[0]="Name,Email,Phone,Address,Postcode,Service_Required,Comments,Website";
	$mail_type[0]="text";
	$mail_priority[0]="1";
	$allow_html[0]="no";
// Send back to sender config
	$send_copy[0]="yes";
	$copy_format[0]="vert_table";
	$copy_fields[0]="Name,Comments";
	$copy_attachment_fields[0]="";
	$copy_subject[0]="Enquiry";
	$copy_intro[0]="";
	$copy_from[0]="quote@blueskyguttering.co.uk";
	$copy_tomail_field[0]="Email";
// Result options
	$header[0]="";
	$footer[0]="";
	$error_page[0]="";
	$thanks_page[0]="";
// Default Error and Success Page Variables
	$error_page_title[0]="Errors:";
	$error_page_text[0]="Please use your browser's back button to return to the form and complete the required fields.";
	$thanks_page_title[0]="Message Sent";
	$thanks_page_text[0]="Thank you for your inquiry";
// Antispam Options
	$empty_field[0]="nospam";
	$character_scan[0]="Comments,Name";
	$time_delay[0]="2";
	$captcha_codes[0]="";
	$max_urls[0]="2";
	$max_url_fields[0]="Comments";
	$flag_spam[0]="";

// options to use if hidden field "config" has a value of 1
// recipient info
	$charset[1]="";
	$tomail[1]="";
	$cc_tomail[1]="";
	$bcc_tomail[1]="";
// Mail contents config
	$subject[1]="";
	$reply_to_field[1]="";
	$reply_to_name[1]="";
	$required_fields[1]="";
	$required_email_fields[1]="";
	$attachment_fields[1]="";
	$return_ip[1]="";
	$mail_intro[1]="";
	$mail_fields[1]="";
	$mail_type[1]="";
	$mail_priority[1]="";
	$allow_html[1]="";
// Send back to sender config
	$send_copy[1]="";
	$copy_format[1]="";
	$copy_fields[1]="";
	$copy_attachment_fields[1]="";
	$copy_subject[1]="";
	$copy_intro[1]="";
	$copy_from[1]="";
	$copy_tomail_field[1]="";
// Result options
	$header[1]="";
	$footer[1]="";
	$error_page[1]="";
	$thanks_page[1]="";
// Default Error and Success Page Variables
	$error_page_title[1]="";
	$error_page_text[1]="";
	$thanks_page_title[1]="";
	$thanks_page_text[1]="";
// Antispam Options
	$empty_field[1]="";
	$character_scan[1]="";
	$time_delay[1]="";
	$captcha_codes[1]="";
	$max_urls[1]="";
	$max_url_fields[1]="";
	$flag_spam[1]="";

/////////////////////////////////////////////////////////////////////////
// Don't muck around past this line unless you know what you are doing //
/////////////////////////////////////////////////////////////////////////

ob_start();
$config=$_POST["config"];
$debug=0;
$debug_text="";

// fix for Windows email server security
ini_set("sendmail_from",$tomail[$config]);

// email validation regular expression
$regex = "^[-a-z0-9!#$%&\'*+/=?^_`{|}~]+(\.[-a-z0-9!#$%&\'*+/=?^_`{|}~]+)*@(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+([a-z]([-a-z0-9]*[a-z0-9]+)?){2,63}$";
$header_injection_regex = "(\r|\n)";

if($header[$config]!="")
	include($header[$config]);

if($_POST["submit"] || $_POST["Submit"] || $_POST["submit_x"] || $_POST["Submit_x"])
{

////////////////////////////
// begin global functions //
////////////////////////////
// get visitor IP
	function getIP()
	{
		if(getenv(HTTP_X_FORWARDED_FOR))
			$user_ip=getenv("HTTP_X_FORWARDED_FOR");
		else
			$user_ip=getenv("REMOTE_ADDR");
		return $user_ip;
	}
// get value of given key
	function parseArray($key)
	{
		$array_value=$_POST[$key];
		$count=1;
		extract($array_value);
		foreach($array_value as $part_value)
		{
			if($count > 1){$value.=", ";}
			$value.=$part_value;
			$count=$count+1;
		}
		return $value;
	}
// stripslashes and autolink url's
	function parseValue($value)
	{
		$value=preg_replace("/(http:\/\/+.[^\s]+)/i",'<a href="\\1">\\1</a>', $value);
		return $value;
	}
// html header if used
	function htmlHeader()
	{
		$htmlHeader="<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01//EN\">\n<html>\n<head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=".$charset[$config]."\"></head>\n<body>\n<table cellpadding=\"2\" cellspacing=\"0\" border=\"0\" width=\"600\">\n";
		return $htmlHeader;
	}
// html footer if used
	function htmlFooter()
	{
		$htmlFooter="</table>\n</body>\n</html>\n";
		return $htmlFooter;
	}
// build verticle table format
	function buildVertTable($fields, $intro, $to, $send_ip)
	{
		$message=htmlHeader();
		if($intro != "")
			$message.="<tr>\n<td align=\"left\" valign=\"top\" colspan=\"2\">".$intro."</td>\n</tr>\n";
		$fields_check=preg_split('/,/',$fields);
		$run=sizeof($fields_check);
		for($i=0;$i<$run;$i++)
		{
			$cur_key=$fields_check[$i];
			$cur_value=$_POST[$cur_key];
			if(is_array($cur_value))
			{
				$cur_value=parseArray($cur_key);
			}
			$cur_value=parseValue($cur_value);
			if($allow_html[$config]=="no")
				$cur_value=htmlspecialchars(nl2br($cur_value));
			else
				$cur_value=nl2br($cur_value);
			$message.="<tr>\n<td align=\"left\" valign=\"top\" style=\"white-space:nowrap;\"><b>".$cur_key."</b></td>\n<td align=\"left\" valign=\"top\" width=\"100%\">".$cur_value."</td>\n</tr>\n";
		}
		if($send_ip=="yes" && $to=="recipient")
		{
			$user_ip=getIP();
			$message.="<tr>\n<td align=\"left\" valign=\"top\" style=\"white-space:nowrap;\"><b>Sender IP</b></td>\n<td align=\"left\" valign=\"top\" width=\"100%\">".$user_ip."</td>\n</tr>\n";
		}
		$message.=htmlFooter();
		return $message;
	}
// build horizontal table format
	function buildHorzTable($fields, $intro, $to, $send_ip)
	{
		$message=htmlHeader();
		$fields_check=preg_split('/,/',$fields);
		$run=sizeof($fields_check);
		if($intro != "")
			$message.="<tr>\n<td align=\"left\" valign=\"top\" colspan=\"".$run."\">".$intro."</td>\n</tr>\n";
		$message.="<tr>\n";
		for($i=0;$i<$run;$i++)
		{
			$cur_key=$fields_check[$i];
			$message.="<td align=\"left\" valign=\"top\" style=\"white-space:nowrap;\"><b>".$cur_key."</b></td>\n";
		}
		if($send_ip=="yes" && $to=="recipient")
			$message.="<td align=\"left\" valign=\"top\" style=\"white-space:nowrap;\"><b>Sender IP</b></td>\n";
		$message.="</tr>\n";
		$message.="<tr>\n";
		for($i=0;$i<$run;$i++)
		{
			$cur_key=$fields_check[$i];
			$cur_value=$_POST[$cur_key];
			if(is_array($cur_value))
			{
				$cur_value=parseArray($cur_key);
			}
			$cur_value=parseValue($cur_value);
			if($allow_html[$config]=="no")
				$cur_value=htmlspecialchars(nl2br($cur_value));
			else
				$cur_value=nl2br($cur_value);
			$message.="<td align=\"left\" valign=\"top\">".$cur_value."</td>\n";
		}
		$message.="</tr>\n";
		$message.="<tr>\n";
		if($send_ip=="yes" && $to=="recipient")
		{
			$user_ip=getIP();
			$message.="<td align=\"left\" valign=\"top\">".$user_ip."</td>\n";
		}
		$message.="</tr>\n";
		$message.=htmlFooter();
		return $message;
	}
// build plain text format
	function buildTextTable($fields, $intro, $to, $send_ip)
	{
		$message="";
		if($intro != "")
			$message.=$intro."\n\n";
		$fields_check=preg_split('/,/',$fields);
		$run=sizeof($fields_check);
		for($i=0;$i<$run;$i++)
		{
			$cur_key=$fields_check[$i];
			$cur_value=$_POST[$cur_key];
			if(is_array($cur_value))
			{
				$cur_value=parseArray($cur_key);
			}
			$cur_value=parseValue($cur_value);
			if($allow_html[$config]=="no")
				$cur_value=htmlspecialchars($cur_value);
			else
				$cur_value=$cur_value;
			$message.="".$cur_key.": ".$cur_value."\n";
		}
		if($send_ip=="yes" && $to=="recipient")
		{
			$user_ip=getIP();
			$message.="Sender IP: ".$user_ip."\n";
		}
		return $message;
	}
// get the proper build fonction
	function buildTable($format, $fields, $intro, $to, $send_ip)
	{
		if($format=="vert_table")
			$message=buildVertTable($fields, $intro, $to, $send_ip);
		else if($format=="horz_table")
			$message=buildHorzTable($fields, $intro, $to, $send_ip);
		else
			$message=buildTextTable($fields, $intro, $to, $send_ip);
		return $message;
	}
// referrer checking security option
	function checkReferer()
	{
		if($check_referrer=="yes")
		{
			$ref_check=preg_split('/,/',$referring_domains);
			$ref_run=sizeof($ref_check);
			$referer=$_SERVER['HTTP_REFERER'];
			$domain_chk="no";
			for($i=0;$i<$ref_run;$i++)
			{
				$cur_domain=$ref_check[$i];
				if(stristr($referer,$cur_domain)){$domain_chk="yes";}
			}
		}
		else
		{
			$domain_chk="yes";
		}
		return $domain_chk;
	}
// checking required fields and email fields
	function checkFields($text_fields, $email_fields, $regex)
	{
      	$error_message="";
		if($debug==1)
			$error_message.="<li>text_fields: ".$text_fields."<br />email_fields: ".$email_fields."<br />reply_to_field: ".$reply_to_field."<br />reply_to_name: ".reply_to_name."</li>";
		if($text_fields != "")
		{
			$req_check=preg_split('/,/',$text_fields);
			$req_run=sizeof($req_check);
			for($i=0;$i<$req_run;$i++)
			{
				$cur_field_name=$req_check[$i];
				$cur_field=$_POST[$cur_field_name];
				if($cur_field=="")
				{
					$error_message.="<li>You are missing the <b>".$req_check[$i]."</b> field</li>\n";
				}
			}
		}
		if($email_fields != "")
		{
			$email_check=preg_split('/,/',$email_fields);
			$email_run=sizeof($email_check);
			for($i=0;$i<$email_run;$i++)
			{
				$cur_email_name=$email_check[$i];
				$cur_email=$_POST[$cur_email_name];
				if($cur_email=="" || !eregi($regex, $cur_email))
				{
					$error_message.="<li>You are missing the <b>".$email_check[$i]."</b> field or it is not a valid email address.</li>\n";
				}
			}
		}
		return $error_message;
	}
// attachment function
	function getAttachments($attachment_fields, $message, $content_type, $border)
	{
		$att_message="This is a multi-part message in MIME format.\r\n";
		$att_message.="--{$border}\r\n";
		$att_message.=$content_type."\r\n";
		$att_message.="Content-Transfer-Encoding: 7bit\r\n\r\n";
		$att_message.=$message."\r\n\r\n";

		$att_check=preg_split('/,/',$attachment_fields);
		$att_run=sizeof($att_check);
		for($i=0;$i<$att_run;$i++)
		{
			$fileatt=$_FILES[$att_check[$i]]['tmp_name'];
			$fileatt_name=$_FILES[$att_check[$i]]['name'];
			$fileatt_type=$_FILES[$att_check[$i]]['type'];
			if (is_uploaded_file($fileatt))
			{
				$file=fopen($fileatt,'rb');
				$data=fread($file,filesize($fileatt));
				fclose($file);
				$data=chunk_split(base64_encode($data));
				$att_message.="--{$border}\n";
				$att_message.="Content-Type: {$fileatt_type}; name=\"{$fileatt_name}\"\r\n";
				$att_message.="Content-Disposition: attachment; filename=\"{$fileatt_name}\"\r\n";
				$att_message.="Content-Transfer-Encoding: base64\r\n\r\n".$data."\r\n\r\n";
			}
		}
		$att_message.="--{$border}--\n";
		return $att_message;
	}
// function to set content type
	function contentType($charset, $format)
	{
		if($format=="vert_table")
			$content_type="Content-type: text/html; charset=".$charset."\r\n";
		else if($format=="horz_table")
			$content_type="Content-type: text/html; charset=".$charset."\r\n";
		else
			$content_type="Content-type: text/plain; charset=".$charset."\r\n";
		return $content_type;
	}
//////////////////////////
// end global functions //
//////////////////////////

////////////////////////////////
// begin procedural scripting //
////////////////////////////////
	// anti-spam empty field check
	if($_POST[$empty_field[$config]] != "")
	{
		$empty_message = "<li>This submission failed and was flagged as spam.</li>\n";
	}
	// anti-spam character scan check
	if(strlen($character_scan[$config]) > 0)
	{
		$spam_message="";
		$field_check=preg_split('/,/',$character_scan[$config]);
		$field_run=sizeof($field_check);
		for($i=0;$i<$field_run;$i++)
		{
			$cur_field_name=$field_check[$i];
			$cur_field=$_POST[$cur_field_name];
			if(preg_match("/<(.|\n)+?>/", $cur_field) || preg_match("/\[(.|\n)+?\]/", $cur_field))
				$spam_message.="<li>This message contains disallowed characters.</li>\n";
		}
	}
	// anti-spam time delay check
	if((strlen($time_delay[$config]) > 0 && strlen($_POST["time"]) > 0) || (strlen($time_delay[$config]) > 0 && (strlen($_POST["time"]) == 0 || !$_POST["time"])))
	{
		if((time() - $_POST["time"]) < $time_delay[$config])
			$time_message = "<li>This has been stopped by the timer, and is likely spam.</li>\n";
	}
	// anti-spam CAPTCHA check
	if(strlen($captcha_codes[$config]) > 0)
	{
		$captcha_check=preg_split('/,/',$captcha_codes[$config]);
		if(strtolower($_POST["captcha_entry"]) != strtolower($captcha_check[$_POST["captcha_code"]]))
			$captcha_message = "<li>CAPTCHA test did not match.</li>\n";
	}
	// anti-spam max URL check
	if(strlen($max_url_fields[$config]) > 0)
	{
		$max_url_message="";
		$field_check=preg_split('/,/',$max_url_fields[$config]);
		$field_run=sizeof($field_check);
		for($i=0;$i<$field_run;$i++)
		{
			$cur_field_name=$field_check[$i];
			$cur_field=$_POST[$cur_field_name];
			preg_match_all("/http:/", $cur_field, $matches);
			if(count($matches[0]) > $max_urls[$config])
				$max_url_message.="<li>This message contains too many URL's.</li>\n";
		}
	}
	// set anti-spam flagging option
	if(strlen($empty_message.$spam_message.$time_message.$captcha_message.$max_url_message) > 0 && strlen($flag_spam[$config]) == 0)
		$set_flag = 2;
	else if(strlen($empty_message.$spam_message.$time_message.$captcha_message.$max_url_message) > 0 && strlen($flag_spam[$config]) > 0)
		$set_flag = 1;
	else
		$set_flag = 0;
	// header injection check
   	$security_filter="";
	if(strlen($_POST[$reply_to_field[$config]]) > 0)
	{
		if(eregi($header_injection_regex,$_POST[$reply_to_field[$config]]))
			$security_filter.="<li>Header injection attempt detected, mail aborted.</li>\n";
		else
			$reply_to_field_checked=$_POST[$reply_to_field[$config]];
	}
	if(strlen($_POST[$reply_to_name[$config]]) > 0)
	{
		if(eregi($header_injection_regex,$_POST[$reply_to_name[$config]]))
			$security_filter.="<li>Header injection attempt detected, mail aborted.</li>\n";
		else
			$reply_to_name_checked=$_POST[$reply_to_name[$config]];
	}
	// check domain referrer and continue
	$domain_chk=checkReferer();
	if($domain_chk=="yes")
	{
		$error_message=checkFields($required_fields[$config], $required_email_fields[$config], $regex);
		if(strlen($error_message) < 1 && strlen($security_filter) < 1 && $set_flag < 2)
		{
			// build appropriate message format for recipient
			$content_type=contentType($charset[$config], $mail_type[$config]);
			$message=buildTable($mail_type[$config], $mail_fields[$config], $mail_intro[$config], "recipient", $return_ip[$config]);
			// build header data for recipient message
			//$extra="From: ".$_POST[$reply_to_field[$config]]."\r\n";
			$extra="From: ".$reply_to_name_checked." <".$reply_to_field_checked.">\r\n";
			if($cc_tomail[$config]!="")
				$extra.="Cc: ".$cc_tomail[$config]."\r\n";
			if($bcc_tomail[$config]!="")
				$extra.="Bcc: ".$bcc_tomail[$config]."\r\n";
			if($mail_priority[$config]!="")
				$extra.="X-Priority: ".$mail_priority[$config]."\r\n";
			// get attachments if necessary
			if($attachment_fields[$config]!="")
			{
				$semi_rand=md5(time());
				$border="==Multipart_Boundary_x{$semi_rand}x";
				$extra.="MIME-Version: 1.0\r\n";
				$extra.="Content-Type: multipart/mixed; boundary=\"{$border}\"";
				$message=getAttachments($attachment_fields[$config], $message, $content_type, $border);
			}
			else
			{
				$extra.="MIME-Version: 1.0\r\n".$content_type;
			}
			// send recipient email
			if($debug==1)
			{
				if($set_flag == 1)
					$debug_text.="<p><b>Mail would have sent flagged for spam if not in debug mode.</b></p>";
				else
					$debug_text.="<p><b>Mail would have sent if not in debug mode.</b></p>";
			}
			else if($debug==0)
			{
				if($set_flag == 1)
					$subject = $flag_spam[$config]." ".$subject[$config];
				else
					$subject = $subject[$config];
				mail("".$tomail[$config]."", "".stripslashes($subject)."", "".stripslashes($message)."", "".$extra."");
			}
			// autoresponse email if necessary
			if($send_copy[$config]=="yes")
			{
				// build appropriate message format for autoresponse
				$content_type=contentType($charset[$config], $copy_format[$config]);
				$message=buildTable($copy_format[$config], $copy_fields[$config], $copy_intro[$config], "autoresponder", $return_ip[$config]);
				// build header data for autoresponse
				$copy_tomail=$_POST[$copy_tomail_field[$config]];
				$copy_extra="From: ".$copy_from[$config]."\r\n";
				// get autoresponse  attachments if necessary
				if($copy_attachment_fields[$config]!="")
				{
					$semi_rand=md5(time());
					$border="==Multipart_Boundary_x{$semi_rand}x";
					$copy_extra.="MIME-Version: 1.0\r\n";
					$copy_extra.="Content-Type: multipart/mixed; boundary=\"{$border}\"";
					$message=getAttachments($copy_attachment_fields[$config], $message, $content_type, $border);
				}
				else
				{
					$copy_extra.="MIME-Version: 1.0\r\n".$content_type;
				}
				// send autoresponse email
				if($debug==1)
				{
					if($set_flag == 1)
						$debug_text.="<p><b>Autoresponder would have sent flagged for spam if not in debug mode.</b></p>";
					else
						$debug_text.="<p><b>Autoresponder would have sent if not in debug mode.</b></p>";
				}
				else if($debug==0)
				{
					$send_copy = 1;
					if($copy_tomail=="" || !eregi($regex,$copy_tomail))
						$send_copy = 0;
					if($send_copy == 1)
					{
						if($set_flag == 1)
							$copy_subject = $flag_spam[$config]." ".$copy_subject[$config];
						else
							$copy_subject = $copy_subject[$config];
						mail("$copy_tomail", "".$copy_subject."", "$message", "$copy_extra");
					}
				}
			}
			// showing thanks pages from a successful submission
			if($thanks_page[$config]=="")
			{
				header("Location: thankyou.php");
			}
			else
			{
				header("Location: error.php");
			}
		}
		else
		{
			// entering error page options from missing required fields
			if($error_page[$config]=="")
			{
				echo "<h3>".$error_page_title[$config]."</h3>\n";
				echo "<ul>\n";
				echo $security_filter.$empty_message.$error_message.$spam_message.$time_message.$captcha_message.$max_url_message;
				echo "</ul>\n";
				echo "<p>".$error_page_text[$config]."</p>\n";
			}
			else
			{
				header("Location: ".$error_page[$config]);
			}
		}
	}
	else
	{
		echo "<h3>".$error_page_title[$config]."</h3>\n";
		// message if unauthorized domain trigger from referer checking option
		echo "<p>Sorry, mailing request came from an unauthorized domain.</p>\n";
	}
//////////////////////////////
// end procedural scripting //
//////////////////////////////
}
else
{
	echo "<h3>Error</h3>";
	echo "<p>No form data has been sent to the script</p>\n";
}
if($footer[$config]!="")
	include($footer[$config]);
ob_end_flush();
?>
