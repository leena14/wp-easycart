<?php
/*
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//All Code and Design is copyrighted by Level Four Development, llc
//
//Level Four Development, LLC provides this code "as is" without warranty of any kind, either express or implied,     
//including but not limited to the implied warranties of merchantability and/or fitness for a particular purpose.         
//
//Only licnesed users may use this code and storfront for live purposes. All other use is prohibited and may be 
//subject to copyright violation laws. If you have any questions regarding proper use of this code, please
//contact Level Four Development, llc and EasyCart prior to use.
//
//All use of this storefront is subject to our terms of agreement found on Level Four Development, llc's  website.
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
*/


class newsletter
	{		
	
		function newsletter() {
			//load our connection settings
			require_once('../../../connection/ec_conn.php');
		
			//set our connection variables
			$dbhost = HOSTNAME;
			$dbname = DATABASE;
			$dbuser = USERNAME;
			$dbpass = PASSWORD;	

			//make a connection to our database
			$this->conn = mysql_connect($dbhost, $dbuser, $dbpass);
			mysql_select_db ($dbname);		
			mysql_query("SET CHARACTER SET utf8", $this->conn); 
			mysql_query("SET NAMES 'utf8'", $this->conn); 

		}	
			
		
		//HELPER - used to escape out SQL calls
		function escape($sql) 
		{ 
			  $args = func_get_args(); 
				foreach($args as $key => $val) 
				{ 
					$args[$key] = mysql_real_escape_string($val); 
				} 
				 
				$args[0] = $sql; 
				return call_user_func_array('sprintf', $args); 
		} 
		


		//mail functions functions
		function mailnewsletter($sendtype, $fromemail, $subject, $themessage, $smtphost, $smptport, $smtpusername, $smtppassword) {
			//Create SQL Query
			$subquery = mysql_query("SELECT ec_subscriber.* FROM ec_subscriber");
			$sentnum = 0;
			$mailresult = "error";
			$error = "";
			while($subscribers = mysql_fetch_array($subquery)){
				
				//build the message here
				$text = "This message is in HTML and requires an html viewer, please switch to that view.";
				$phpmailmessage = "--==MIME_BOUNDRY_alt_main_message\n";
				$phpmailmessage .= "Content-Type: text/plain; charset=ISO-8859-1\n";
				$phpmailmessage .= "Content-Transfer-Encoding: 7bit\n\n";
				$phpmailmessage .= $text . "\n\n";
				$phpmailmessage .= "--==MIME_BOUNDRY_alt_main_message\n";
				$phpmailmessage .= "Content-Type: text/html; charset=ISO-8859-1\n";
				$phpmailmessage .= "Content-Transfer-Encoding: 7bit\n\n";
				//add the main message the user types in
				$phpmailmessage .= $themessage;
				$phpmailunsubscribelink = plugins_url() . "/wp-easycart/inc/amfphp/administration/unsubscribe.php?email=$subscribers[email]";
				//now add the unsubscribe portion
				$unsubscribemessage = "<br><br><br><br><center style='font-family: Arial, Helvetica, sans-serif; font-size: 9px;'>----------------------------------------------------------------------<br>Please click on the link below and you will be removed from this list.<br><a href='".$phpmailunsubscribelink."' style='color: #000; font-weight: bold; font-size: 10px;'>UNSUBSCRIBE</a><br>----------------------------------------------------------------------</center>";
				
				$message = $phpmailmessage . $unsubscribemessage;
				
				if ($sendtype == 'phpmail') {
					//headers
					$headers = "From: $fromemail\r\n";
					$headers .= "Reply-To: $fromemail\r\n";
					$headers .= "X-Mailer: PHP4\n";
					$headers .= "X-Priority: 3\n";
					$headers .= "MIME-Version: 1.0\n";
					$headers .= "Return-Path: $fromemail\r\n"; 
					$headers .= "Content-Type: multipart/alternative; boundary=\"==MIME_BOUNDRY_alt_main_message\"\n\n";
					//mail individual newsletter
					$mailresult = mail($subscribers[email], $subject, $message, $headers);
					if ($mailresult === true) {
						//do nothing
					}
					else {
						$error =  "There was a problem sending your newsletter.  PHP Mail may not be allowed from your server, so please check with your host.";
					}
					//send mail using php mail
				} else if ($sendtype == 'smtpmail') {
					
					//headers
					$headers["From"] = $fromemail;
					$headers["To"] = $subscribers[email];
					$headers["Subject"] = $subject;
					//mime email settings
					$crlf = "\n"; 
					$mime = new Mail_mime($crlf); 
					$mime->setTXTBody($text); 
					$mime->setHTMLBody($themessage . $unsubscribemessage); 

					$mimemessage = $mime->get(); 
					$headers = $mime->headers($headers); 

					//smtp information
					$smtpinfo["host"] = $smtphost;
					$smtpinfo["port"] = $smtpport;
					$smtpinfo["auth"] = true;
					$smtpinfo["username"] = $smtpusername;
					$smtpinfo["password"] = $smtppassword;
					//create mail object
					$mail_object =& Mail::factory("smtp", $smtpinfo);
					//mail individual newsletter
				
					$mailresult = $mail_object->send($subscribers[email], $headers, $mimemessage);	
					if ($mailresult === true) {
						//do nothing
					}
					else {
						preg_match('/(\d+)/', $mailresult->getMessage(), $match);
						$error =  "There was a problem sending your newsletter. \n\nError Code: $match[0]\n" .
							"Message: {$mailresult->getMessage()}\n";
					}

				}
				$sentnum += 1;
			}
			if ($mailresult == 1 && $error == "") {
				return "success";
			} else {
				if ($error == "") {
					return "There was a general problem sending your newsletter.  Please try an alternative method for sending your newsletter and/or check with your host for settings and whether they allow email newsletter blasts to be sent from your hosting environment.";
				} else {
					return $error;
				}
			}
		}


	}//close class
?>