<?php
/******************************************
	�쐬�����t�H�[�����o�͂��邽�߂�
	�t�@�C��
******************************************/

	//�t�@�C���̓ǂݍ���
//	require_once("MySmarty.class.php");
	require_once("CheckValue.class.php");
	require_once("SQLData.class.php");

	//�C���X�^���X����
//	$smarty = new MySmarty();
	$chk = new CheckValue();
	$sql = new SQLData();
	
	//GET�Ń��[�U�[ID�擾
	$user_id = $_GET['u_id'];
	$f_id = $_GET['f_id'];

	//ID�̈�v�m�F
	$id_flag = $sql->isValidUser($user_id,$f_id);
	if(($user_id=="")||(!is_numeric($user_id))||(!$id_flag)){
		require_once("./templates/error.html");
		exit;
	}
	
	//delete�t���O
	$del_flag="0";
	
	//�p�����[�^�[��
	$paramAmount = "5";
	
	//�t�H�[������f�[�^���擾
	$data = $_POST['data'];
	
	//DB����t�H�[���f�[�^���擾
	$tableName="td_form_create2";
	$sql_data = $sql->getTableData($tableName,$user_id);
	
	
	if(!$sql_data==""){						//DB�Ƀt�H�[���f�[�^���������ꍇ�̏���

		//���ꂼ��l���G�X�P�[�v���ĕϐ��Ɋi�[
		$form_name   = $sql_data['form_name'];
		$form_name  = htmlspecialchars($form_name);
		$form_header = $sql_data['form_header'];
		$form_header = htmlspecialchars($form_header);
		$form_header = nl2br($form_header);
		
		for($i=1;$i<=$paramAmount;$i++){
			
			$check[$i] = $sql_data['check'.$i];
	
			if($check[$i]=="1"){
				$param_name[$i] = $sql_data['param'.$i];
				$param_name[$i] = htmlspecialchars($param_name[$i]);
			}else if($check[$i]=="2"){
				$cnt1[$i] = "on";
				$param_name[$i] = $sql_data['param'.$i];
				$param_name[$i] = htmlspecialchars($param_name[$i]);
			}
		}
				
		$thk_url = $sql_data['thk_url'];
	}else{								//DB�Ƀt�H�[���f�[�^���Ȃ��ꍇ�̏���
		
		$mess = "�t�H�[�����쐬����Ă��܂���";
	/*	
		$smarty->assign("mess",$mess);
		$smarty->display("./notFound.tpl");
	*/
		require_once("./templates/notFound.html");
		exit;
	}

	//POST�f�[�^���󂯂����̏���
	if(!is_null($data)){
		
		//���[�h�̎擾
		$mode = $_POST['mode'];
		
		//���ꂼ��l��ϐ��Ɋi�[���A���̓`�F�b�N
		$name_family = $data['name_family'];
		$chk->requireCheck($name_family,"���O(��)");
		$name_first   = $data['name_first'];
		$chk->requireCheck($name_first,"���O(��)");
		$user_mail_add     = $data['user_mail_add'];
		$chk->requireCheck($user_mail_add,"���[���A�h���X");
		$chk->mailCheck($user_mail_add,"���[���A�h���X");
		
		//HTML�m�F�y�[�W�p�ɒu��
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
		
	/*	//HTML�֕ϐ����
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
		//�G���[���b�Z�[�W�擾
		$errorm = $chk->getError();
		$cnt = count($errorm);
		if($cnt>0){			//���̓G���[���̏���
		
		/*	
			$smarty->assign("errorm",$errorm);
			$smarty->display("./confirmForm.tpl");
		*/
			require_once("./templates/confirmForm.html");
			exit;
		}else{
			if($mode=="input"){
			//	$smarty->display("./confirmForm.tpl");
				require_once("./templates/confirmForm.html");
				exit;
			}else if($mode=="check"){
			//	$smarty->display("./cf_check.tpl");
				require_once("./templates/cf_check.html");
				exit;
			}
		}

	}
	//html�֕ϐ����
/*
	$smarty->assign("form_name",$form_name);
	$smarty->assign("form_header",$form_header);
	$smarty->assign("param_name",$param_name);
	$smarty->assign("cnt1",$cnt1);
	
	//html�o��
	$smarty->display("./confirmForm.tpl");
*/
	require_once("./templates/m-confirmForm.html");
?>