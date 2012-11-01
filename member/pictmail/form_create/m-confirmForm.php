<?php
/******************************************
	���������ե��������Ϥ��뤿���
	�ե����롡����������
******************************************/

	//�ե�������ɤ߹���
//	require_once("MySmarty.class.php");
	require_once("M_CheckValue.class.php");
	require_once("SQLData.class.php");

	//���󥹥�������
//	$smarty = new MySmarty();
	$chk = new M_CheckValue();
	$sql = new SQLData();
	
	//GET�ǥ桼����ID����
	$user_id = $_GET['u_id'];
	$f_id = $_GET['f_id'];

	$submit = $_POST['submit'];
	if($submit!=""){
		$sub_key = key($submit);
		if($sub_key==="finish"){
			header("Location:");
		}
	}

	//ID�ΰ��׳�ǧ
	$id_flag = $sql->isValidUser($user_id,$f_id);
	if(($user_id=="")||(!is_numeric($user_id))||(!$id_flag)){
		require_once("./templates/error.html");
		exit;
	}
	
	//delete�ե饰
	$del_flag="0";
	
	//�ѥ�᡼������
	$paramAmount = "5";
	
	//�ե����फ��ǡ��������
	$data = $_POST['data'];
	
	//DB����ե�����ǡ��������
	$tableName="td_form_create";
	$sql_data = $sql->getTableData($tableName,$user_id);
	
	
	if(!$sql_data==""){						//DB�˥ե�����ǡ��������ä����ν���

		//���줾���ͤ򥨥������פ����ѿ��˳�Ǽ
		$form_name   = $sql_data['form_name'];
		$form_name   = htmlspecialchars($form_name);
		
		//�����ѤΤ���ӣʣɣӤ��Ѵ�
		$form_name   = mb_convert_encoding($form_name,"SJIS","EUC-JP");
		
		$form_header = $sql_data['form_header'];
		$form_header = htmlspecialchars($form_header);
		$form_header = nl2br($form_header);
		$form_header = mb_convert_encoding($form_header,"SJIS","EUC-JP");
		$name_check  = htmlspecialchars($sql_data['name_check']);

		for($i=1;$i<=$paramAmount;$i++){
			
			$check[$i] = $sql_data['check'.$i];
	
			if($check[$i]=="1"){
				$param_name[$i] = $sql_data['param'.$i];
				$param_name[$i] = htmlspecialchars($param_name[$i]);
				$param_name[$i] = mb_convert_encoding($param_name[$i],"SJIS","EUC-JP");
			}else if($check[$i]=="2"){
				$cnt1[$i] = "on";
				$param_name[$i] = $sql_data['param'.$i];
				$param_name[$i] = htmlspecialchars($param_name[$i]);
				$param_name[$i] = mb_convert_encoding($param_name[$i],"SJIS","EUC-JP");
			}
		}
				
		$thk_url = $sql_data['thk_url'];
	}else{								//DB�˥ե�����ǡ������ʤ����ν���
		
		$mess = "�ե����ब��������Ƥ��ޤ���";
	/*	
		$smarty->assign("mess",$mess);
		$smarty->display("./notFound.tpl");
	*/
		require_once("./templates/notFound.html");
		exit;
	}

	//POST�ǡ�������������ν���
	if(!is_null($data)){
		
		//�⡼�ɤμ���
		$mode = $_POST['mode'];
		//���줾���ͤ��ѿ��˳�Ǽ�������ϥ����å�
		$name_family   = $data['name_family'];
    	$name_first   = $data['name_first'];		
		if($name_check=="2"){
    		$chk->requireCheck($name_family,"̾��(��)");
    		$chk->requireCheck($name_first,"̾��(̾)");
		}
		$user_mail_add     = $data['user_mail_add'];
		$chk->requireCheck($user_mail_add,"�᡼�륢�ɥ쥹");
		$chk->mailCheck($user_mail_add,"�᡼�륢�ɥ쥹");
		
		//HTML��ǧ�ڡ����Ѥ��ִ�
		$s_name_family   = htmlspecialchars($name_family);
		$s_name_first    = htmlspecialchars($name_first);
		$s_user_mail_add = htmlspecialchars($user_mail_add);
		
		$j=1;
		while($j<=$paramAmount){
			$param[$j]   = $data['param'.$j]; 
			$s_param[$j] = htmlspecialchars($param[$j]);
			$p_name[$j]  = $data['param_name'.$j];
			$check[$j]   = $data['check'.$j];
			
			if($check[$j]=="on"){
				$chk->requireCheck($param[$j],$p_name[$j]);
			}
			$j++;
		}
		
	/*	//HTML���ѿ�����
		$smarty->assign("form_name",$form_name);
		$smarty->assign("form_header",$form_header);
		$smarty->assign("param_name",$param_name);
		$smarty->assign("name_family",$name_family);
		$smarty->assign("s_name_family",$s_name_family);
		$smarty->assign("name_first",$name_first);
		$smarty->assign("s_name_first",$s_name_first);
		$smarty->assign("user_mail_add",$user_mail_add);
		$smarty->assign("s_user_mail_add",$s_user_mail_add);
		$smarty->assign("param",$param);
		$smarty->assign("s_param",$s_param);
		$smarty->assign("paramAmount",$paramAmount);
		$smarty->assign("del_flag",$del_flag);
		$smarty->assign("thk_url",$thk_url);
		$smarty->assign("cnt1",$cnt1);
		$smarty->assign("js_url",$js_url);
		$smarty->assign("header_url",$header_url);
		$smarty->assign("left_url",$left_url);
		$smarty->assign("hooter_url",$hooter_url);
	*/	
		//���顼��å���������
		$errorm = $chk->getError();

		$cnt = count($errorm);
		if($cnt>0){			//���ϥ��顼���ν���
		
		/*	
			$smarty->assign("errorm",$errorm);
			$smarty->display("./confirmForm.tpl");
		*/
			require_once("./templates/m-confirmForm.html");
			exit;
		}else{
			if($mode=="input"){
			//	$smarty->display("./confirmForm.tpl");
				require_once("./templates/m-confirmForm.html");
				exit;
			}else if($mode=="check"){
			//	$smarty->display("./cf_check.tpl");
				require_once("./templates/m-cf_check.html");
				exit;
			}
		}

	}
	//html���ѿ�����
/*
	$smarty->assign("form_name",$form_name);
	$smarty->assign("form_header",$form_header);
	$smarty->assign("param_name",$param_name);
	$smarty->assign("cnt1",$cnt1);
	
	//html����
	$smarty->display("./confirmForm.tpl");
*/
	require_once("./templates/m-confirmForm.html");
?>