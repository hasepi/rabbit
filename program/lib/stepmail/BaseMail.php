<?PHP
/**
 * �᡼���ۿ��ζ��̴ؿ� �ȤäƤͤ��衡���줫��Ȥ�ͽ��
 * [ StepMail�ѤˤʤäƤ� ]
 *
 * @author fujita
 * @package defaultPackage
 */

class BaseMail {

	// Mail
	var $Mail = "";
	var $Mime = "";

	// Mail���� (SMTP����)
	var $host 		= "localhost";
	var $port 		= 25;
	var $auth 		= false;
	var $persist	= true;

	var $html_params = "";

	var $Util = "";

	/**
	* @desc ���󥹥ȥ饯��
	*/
	function BaseMail(){

		// �Ѵ����饹
		require_once _DIR_LIB_.'util/Util.php';

		// PearMail�����
		require_once '/usr/local/share/pear/PEAR.php';
		require_once '/usr/local/share/pear/DB.php';

		require_once '/usr/local/share/pear/Mail.php';
		require_once '/usr/local/share/pear/Mail/mime.php';

		// SMTP��³�Ѿ��� ( PEAR MAIL )
		$params = array(
			"host"    => $this->host,
			"port"    => $this->port,
			"auth"    => $this->auth,
			"persist" => $this->persist
		);

		// HTML MAIL�Ѥξ���
		$this->html_params = array(
			"text_encoding" => "7bit",
			"html_encoding" => "7bit",
			"text_charset"  => "ISO-2022-JP",
			"html_charset"  => "ISO-2022-JP",
		  	"head_charset"  => "ISO-2022-JP"

		);

 		// PEAR MAIL CLASS
		$this->Mail = Mail::factory("sendmail", $params);
		$this->Util = new Util();

	}

	/**
	* @return bool
	* @param  from			������
	* @param  to			������
	* @param  subject		��̾
	* @param  message		��ʸ
	* @param  error			Error������
	* @param  flag_html True=HTML�᡼��, False=TEXT�᡼��
	* @desc   �᡼�������¹� �Ѵ���
	*/
	function sendMail($from, $from_name, $to, $to_name, $subject, $message, $error, $flag_html, $html_message=""){

		$subject = $this->getSubject($subject);
		$from 	 = $this->getSender($from, $from_name);
		$to      = $this->getSender($to,   $to_name);
		$message = $this->getMessage($message);
		$html_message = $this->getMessage($html_message);

//		$to = "abce@itm-asp.com";

		// �᡼�����
		$headers = array(
			"From"			=> $from,
			"To"			=> $to,
			"Subject"		=> $subject,
			"Return-Path"	=> $error
		);

		// �᡼��������¹�
		if ( $flag_html==true ) {
			// HTML�᡼��
			$this->Mime = & new Mail_mime("\n");
			$this->Mime->setTXTBody($message);
			$this->Mime->setHTMLBody($html_message);

			$message    = $this->Mime->get($this->html_params);
			$mimeHeader = $this->Mime->headers($headers);

			$mail_flag = $this->Mail->send($to, $mimeHeader, $message);

		}else{
			// TEXT�᡼��
			$mail_flag = $this->Mail->send($to, $headers, $message);

		}

		return $mail_flag;

	}

	/**
	* @return String
	* @param  $subject ��̾
	* @desc   ��̾�βù�
	*/
	function getSubject($subject){
		$subject = $this->Util->decodeTag($subject);
//		$subject = mb_encode_mimeheader($subject);
	    $subject = mb_convert_encoding($subject ,"ISO-2022-JP","EUC-JP");
	    $subject = "=?iso-2022-jp?B?" . base64_encode($subject) . "?=";

		return $subject;
	}

	/**
	* @return String
	* @param	$email      �ᥢ��
	* @param	$email_name ̾��
	* @desc   �����ԡ�������̾�βù�
	*/
	function getSender($email, $email_name){

		if ( $email_name=="��" or $email_name=="") {
			// ̾����̵��
			$to = $email;
		}else{
			// ̾����ͭ��
			$email_name = $this->Util->decodeTag($email_name);
			$email_name = str_replace('"', '\"', $email_name);
//			$to = "\"".mb_encode_mimeheader( $email_name )."\" <".$email.">";

		    $to    = mb_convert_encoding($email_name ,"ISO-2022-JP","EUC-JP");
		    $to    = "\"=?iso-2022-jp?B?" . base64_encode($to) . "?=\" <".$email.">";

		}

		return $to;

	}

	/**
	* @return String
	* @param $message ��ʸ
	* @desc �᡼����ʸ�βù�
	*/
	function getMessage($message){
		// ���ԥ����ɤ� \n ���ѹ�
		$message = $this->Util->nl2LF($message);
		$message = $this->Util->decodeTag($message);
		$message = mb_convert_encoding($message, "JIS", "EUC-JP");
		return $message;
	}

	/**
	* @return String
	* @param  $message ��ʸ
	* @param  $ary		 �Ѵ�����ʸ���������
	* @desc   ��ʸ��Υѥ�᡼�����Ѵ�
	*/
	function getConvertMessage($message, $ary){

		$message = str_replace("%name%",    	$ary['name'],			$message);
		$message = str_replace("%name_family%", $ary['name_family'], 	$message);
		$message = str_replace("%name_first%",  $ary['name_first'],		$message);

		$message = str_replace("%company%", $ary['company'], $message);
		$message = str_replace("%post%",  	$ary['post'],    $message);

		$message = str_replace("%param1%",  $ary['param1'],  $message);
		$message = str_replace("%param2%",  $ary['param2'],  $message);
		$message = str_replace("%param3%",  $ary['param3'],  $message);
		$message = str_replace("%param4%",  $ary['param4'],  $message);
		$message = str_replace("%param5%",  $ary['param5'],  $message);
		$message = str_replace("%param6%",  $ary['param6'],  $message);
		$message = str_replace("%param7%",  $ary['param7'],  $message);
		$message = str_replace("%param8%",  $ary['param8'],  $message);
		$message = str_replace("%param9%",  $ary['param9'],  $message);
		$message = str_replace("%param10%", $ary['param10'], $message);

		$message = str_replace("%email%",   $ary['email'], $message);

		// ����ѥ��
//		$member_id = $ary['stepmail_member_id'];
//		$email     = crypt( $ary['email'] , "hoge");
//		$url       = "https://www.itm-asp.com/member/stepmail/mail_stop/stop.php?m={$email}&num={$member_id}";
//		$message   = str_replace("%stop%", $url, $message);

		return $message;

	}


	/**
	* @return string
	* @param  $message
	* @param  $id
	* @param  $mali
	* @param  $html_flag
	* @desc	  ���Υ�å�����
	*/
	function getLeaveMessage($message, $id, $mail, $html_flag)
	{

	    // ����ѥ��
		$m    = crypt($mail,"stepmail");  // �Ź沽
		
		if ( $_SERVER['HTTP_HOST'] == "test.itm-asp.com" ) {
		    $link = "http://test.itm-asp.com";
		}else{
		    $link = "http://www.itm-asp.com";
		}
        $link .= "/stepmail/mail_stop/stop.php?m={$m}&num={$id}";

		// �ۿ���߽���

		// ����å�����
		if ( strpos($message, "%stop%")===false  )
		{
            // ����ѥ�󥯤�¸�ߤ��ʤ�
    		if ( $html_flag == 't')
    		{
    		    // HTML Mail
        		$message .= "�ۿ���ߤ�";
        		$message .= "<a href='{$link}'>������</a>";
        		$message .= "�򥯥�å�<BR>\n";
    		}
    		else
    		{
    		    // TEXT Mail
        		$message .= "�ۿ���ߤϤ�����򥯥�å�\n{$link}";
    		}

		}
		else
		{
		    // ����ѥ�󥯤�¸�ߤ���
		    $message = str_replace("%stop%", $link, $message);
		}

		return $message;

	}


	/**
	* @return void
	* @param	$to      ����
	* @param	$subject ��̾
	* @param	$body    ��ʸ
	* @param	$isError isError Object
	* @desc   ��ݡ����ѤΥ᡼������
	*/
	function sendReportMail($to, $subject, $body, $isError){

//		print_a($isError, "_ReportMail");

		// ��ʸ�� System Error �Υ�å�����
		$body .= $isError->message ."\n\n";
		$body .= $isError->userinfo."\n\n";
		// Error ���᡼������
		mb_send_mail( $to, $subject, $body );

	}

}
?>