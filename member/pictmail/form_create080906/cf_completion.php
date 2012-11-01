<?php

/**********************************************
		���������ե����फ������ϥǡ�����
		DB����¸���뤿��Υե�����
*********************************************/


	//�ե�������ɤ߹���
//	require_once("MySmarty.class.php");
	require_once("SQLData.class.php");
	require_once("MyMail.class.php");
		
	//���󥹥�������
//	$smarty = new MySmarty();
	$sql    = new SQLData();
	$myMail   = new MyMail;

	//GET�ǥ桼����ID����
	$user_id = $_GET['u_id'];
	$f_id = $_GET['f_id'];
	//ID�ΰ��׳�ǧ
	$id_flag = $sql->isValidUser($user_id,$f_id);
	if(($user_id=="")||(!is_numeric($user_id))||(!$id_flag)){
		require_once("./templates/error.html");
		exit;
	}
	
	//POST�Υǡ��������
	$data = $_POST['data'];

	//�ե����फ��������ǡ������ѿ��˳�Ǽ
	$name_family 	  = $data['name_family'];
	$name_first		  = $data['name_first'];
	$user_mail_add    = $data['user_mail_add'];
	$paramAmount      = $data['paramAmount'];
	$del_flag		  = $data['del_flag'];
	$thk_url          = $data['thk_url'];
	$i="1";
	while($i<=$paramAmount){
		$param[$i]  = $data['param'.$i];
		$p_name[$i] = $data['param_name'.$i];
		$i++;
	}
	
	//DB���饹���ͤ��Ǽ
	$sql->setNameFamily($name_family);
	$sql->setNameFirst($name_first);
	$sql->setUserMailAdd($user_mail_add);
	$sql->setParam($param);
	$sql->setPName($p_name);
	$sql->setDeleteFlag($del_flag);
	$sql->setUserID($user_id);
		
	//DB�إե�����ǡ�������¸
	$res = $sql->saveUserData();
	if(!$res){
		echo $sql->errorm;
		exit;
	}
	
	//DB��ꥵ�󥭥塼�᡼�������ǡ�������
	$tableName="td_setting_thankmail";
	$sql_data = $sql->getTableData($tableName,$user_id);
	
	//���󥭥塼�᡼�������λ��ѳ�ǧ ����=1 �Ի���=0
	$sendmail_flag = $sql_data['sendmail_flag'];
	if($sendmail_flag=="1"){

		//DB����������ǡ����򤽤줾���ѿ��˳�Ǽ
		$transmit_name    = $sql_data['transmit_name'];
		$transmit_mailadd = $sql_data['transmit_mailadd'];
		$return_err       = $sql_data['return_err'];
		$subject          = $sql_data['subject'];
		$text_mess        = $sql_data['text_mess'];
		
		//�ƥ����ȥ�å������Υ桼�������������Ԥ��ִ�
		$text_mess        = str_replace("%name%",$name_family."��".$name_first,$text_mess);
		$text_mess        = str_replace("%name_family%",$name_family,$text_mess);
		$text_mess        = str_replace("%name_first%",$name_first,$text_mess);
		$text_mess        = str_replace("%name_family%",$name_family,$text_mess);
		$text_mess        = str_replace("%email%",$user_mail_add,$text_mess);
		$text_mess        = ereg_replace("\n","",$text_mess);
		
		$j="1";		
		while($j<=$paramAmount){
			$text_mess        = str_replace("%param".$j."%",$param[$j],$text_mess);
			$j++;
		}
		//�᡼��إå���
		$from = mb_encode_mimeheader($transmit_name)."<".$transmit_mailadd.">";

		//�Уåե饰
		$pc_flag = 1;
		
		//���ӥ�å��������ե饰�μ���
		$mobail_mess = $sql_data['mobail_mess'];
		
		//���ӥ�å��������ե饰�μ���
		$mobail_mess        = str_replace("%name%",$name_family."  ".$name_first,$mobail_mess);
		$mobail_mess        = str_replace("%name_family%",$name_family,$mobail_mess);
		$mobail_mess        = str_replace("%name_first%",$name_first,$mobail_mess);
		$mobail_mess        = str_replace("%name_family%",$name_family,$mobail_mess);
		$mobail_mess        = str_replace("%email%",$user_mail_add,$mobail_mess);
		$mobail_mess        = ereg_replace("\n","",$mobail_mess);

		$k="1";		
		while($k<=$paramAmount){
			$mobail_mess        = str_replace("%param".$k."%",$param[$k],$mobail_mess);
			$k++;
		}
		$text_mess   = mb_convert_kana($text_mess,"K");
		$mobail_mess = mb_convert_kana($mobail_mess,"K");
		$mobail_flag = $sql_data['mobail_flag'];

		//��륢�ɤ�docomo���ɤ��������å������ӥ�å�������Ȥ����ɤ���������å�
	    if(ereg( "^@docomo.ne.jp",substr($user_mail_add,-13) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}
		//��륢�ɤ�vodafone���ɤ��������å����ơ����ӥ�å�������Ȥ����ɤ���������å�
		if(ereg( "^@[dqnchtrks]{1}.vodafone.ne.jp",substr($user_mail_add,-17) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}
		//��륢�ɤ�ezweb���ɤ��������å����ơ����ӥ�å�������Ȥ����ɤ���������å�
		if(ereg( "^ezweb.ne.jp",substr($str,-11) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
				$pc_flag = 0;
			}
			$pc_flag = 0;
		}
		//��륢�ɤ�softbank���ɤ��������å����ơ����ӥ�å�������Ȥ����ɤ���������å�
		if( ereg( "^@[dqnchtrks]{1}.softbank.ne.jp",substr($user_mail_add,-17) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}

		//html��å����������λ��ѳ�ǧ������=1 �Ի���=0;
		$html_flag        = $sql_data['html_flag'];

			if(($pc_flag=="1")&&($html_flag=="1")){
				$html_mess    = $sql_data['html_mess'];
				$html_mess = ereg_replace("\n","",$html_mess);
			
				//HTML�᡼������
				$myMail->htmlMail($from,$user_mail_add,$subject,$text_mess,$html_mess);
	
				//������(itm_niki�˥᡼��)
				$myMail->htmlMail($from,"niki@itm.ne.jp",$subject,$text_mess,$html_mess);
			}else{
		
		
			//�̾�Υ᡼���ۿ�
			//mb_send_mail($user_mail_add,$subject,$text_mess,$from);
			//mb_send_mail("niki@itm.ne.jp",$subject,$text_mess,$from);
			$myMail->normalMb_send_mail($from,$user_mail_add,$subject,$text_mess,$return_err);
			$myMail->normalMb_send_mail($from,"niki@itm.ne.jp",$subject,$text_mess,$return_err);
			}
	}
	
	$url = $thk_url;
	//html����
	//$smarty->assign("url",$thk_url);
	//$smarty->display("./completion.tpl");
	require_once("./templates/cf_completion.html");
	exit;

?>