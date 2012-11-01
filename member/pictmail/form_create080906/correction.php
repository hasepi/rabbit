<?php
/*******************************************
	�ե������������ѹ����뤿��Υե�����
	�������줿�ǡ�����DB����¸
*********************************************/

	//�ե�������ɤ߹���
//	require_once("MySmarty.class.php");
	require_once("CheckValue.class.php");
	require_once("SQLData.class.php");
	
	//SESSION����桼����ID����
	session_start();
	$user    = $_SESSION['user'];
	$user_id = $user['user_id'];
		if(($user_id=="")||(is_null($user_id))){
		require_once("./templates/loginError.html");
		exit;
	}
	
	//���󥹥�������
//	$smarty = new MySmarty();
	$chk = new CheckValue();
	$sql = new SQLData();
	
	//POST�Υǡ��������
	$data = $_POST['data'];
	//�ѥ�᡼�������λ���
	$paramAmount = "5";
		
	//Delete�ե饰������
	$del_flag = 0;
	
	//�ѥ�᡼�������͡������å��ͤν����
	for($i=1;$i<=$paramAmount;$i++){
		$param_name[$i] = "�ѥ�᡼����".$i;
		$check[$i] = "0";
	}
	
	//���󥭥塼�ڡ��������URL���������
	$thk_url = "http://www.yahoo.co.jp";
	
	//DB���ե�����ǡ����μ���
	//�ơ��֥�̾
	$tableName = "td_form_create";
	$sql_data = $sql->getTableData($tableName,$user_id);
	if($sql_data=="f"){
		echo $sql->errorm;
		exit;
	}

	//�ե�����ǡ��������ξ����н�
	if(!$sql_data==""){
	
		//�ե�����͡���μ���
		$form_name = $sql_data['form_name'];
		//�ե�����إå����μ���
		$form_header = $sql_data['form_header'];
		//�ѥ�᡼�����������å��ͤμ���
		for($l=1;$l<=$paramAmount;$l++){
		
			$param_name[$l] = $sql_data['param'.$l];
			$check[$l] = $sql_data['check'.$l];
			if($check[$l]=="1"){
				$cnt0[$l] = "on";
			}else if($check[$l]=="2"){
				$cnt0[$l] = "on";
				$cnt1[$l] = "on";
			}
			
		}	
		
		//���󥭥塼�ڡ��������URL�μ���
		$thk_url = $sql_data['thk_url'];
	
	}else{				//�ե�����ǡ������������ξ����н�
		if(is_null($data)){
		/*	//html���ѿ�����
			$smarty->assign("param_name",$param_name);
			$smarty->assign("thk_url",$thk_url);
			//html����
			$smarty->display("./correction.tpl");
			exit;
		*/
			require_once("./templates/correction.html");
			exit;
		}
	}
	
	//POST�ե饰
	$postFlag = "0";
	
	//POST���褿���ν���
	if(!is_null($data)){
	
		//POST����������ǡ����������ե������̾��
		if((!$data['form_name']=="")||(!is_null($data['form_name']))){
			$form_name = $data['form_name'];
			
		}
		
		//POST����������ǡ����������ե�����إå���
		if((!$data['form_header']=="")||(!is_null($data['form_header']))){
			$form_header = $data['form_header'];
		}
		
		//POST����������ǡ��������ѥ�᡼����
		for($j=1;$j<=$paramAmount;$j++){
			
			//checkbox���ͤ����	
			$param_ini0 = $data['param_ini'.$j][0];
			$param_ini1 = $data['param_ini'.$j][1];
			
			if($param_ini0=="t"){				//checkbox���Ѥ����ϥ����å�
				$param_name[$j] = $data['param_name'.$j];			
				$chk->requireCheck($param_name[$j],"�ѥ�᡼����$j");
				$cnt0[$j]="on";
				$check[$j]="1";	
			}else{
				$cnt0[$j]="off";
			}
			
			if($param_ini1=="t"){			//checkboxɬ�ܤ����ϥ����å�
			
				$param_name[$j] = $data['param_name'.$j];			
				$chk->requireCheck($param_name[$j],"�ѥ�᡼����$j");
				$cnt0[$j]="on";
				$cnt1[$j]="on";
				$check[$j]="2";
			}else{
				$cnt1[$j]="off";
			}
			
			if((!$param_ini0=="t")&&(!$param_ini1=="t")){ //�ɤ�������Ϥ���Ƥ��ʤ����
				$cnt0[$j]="off";
				$cnt1[$j]="off";
				$check[$j]="0";
			}
			$postFlag="1";
		}
		
		//���󥭥塼�ڡ��������URL����
		$thk_url = $data['thk_url'];
		
		//���ϥ����å�
		$chk->requireCheck($thk_url,"���󥭥塼�ڡ��������URL");
		$chk->urlCheck($thk_url);
	}
	
	//thk_url�����ΤȤ����н�
	if($thk_url==""){
		$thk_url = "http://itm-asp.com";
	}
	
	//DB���饹���ͤ��Ǽ
	$sql->setFormName($form_name);
	$sql->setFormHeader($form_header);
	$sql->setParamName($param_name);
	$sql->setParamVal($param_val);
	$sql->setCheck($check);
	$sql->setCheckVal($check_val);
	$sql->setThkUrl($thk_url);
	$sql->setDeleteFlag($del_flag);
	$sql->setUserId($user_id);
		
	//ʸ������ִ�
	$form_name   = htmlspecialchars($form_name);
	$form_header = htmlspecialchars($form_header);
	foreach($param_name as $key=>$value){
		$param_name[$key] = htmlspecialchars($value);
	}

	$thk_url = htmlspecialchars($thk_url);
				
/*	//html���ѿ�����
	$smarty->assign("postFlag",$postFlag);
	$smarty->assign("cnt0",$cnt0);
	$smarty->assign("cnt1",$cnt1);
	$smarty->assign("form_name",$form_name);
	$smarty->assign("form_header",$form_header);
	$smarty->assign("param_name",$param_name);
	$smarty->assign("thk_url",$thk_url);
*/
	//���顼��å���������
	$errorm = $chk->getError();
	$cnt = count($errorm);
	if($cnt>0){
		/*
		$smarty->assign("errorm",$errorm);
		$smarty->display("./correction.tpl");
		*/
		require_once("./templates/correction.html");
		exit;
	}else{
	
		//DB���ͤ��Ǽ
		if($sql_data==""){		//�����ξ��
			$res = $sql->insertFormData();
			if(!$res){
				echo $sql->errorm;
				exit;
			}
		}else{					//�����ξ��
			$res = $sql->updateFormData();
			if(!$res){
				echo $sql->errorm;
				exit;
			}
		}
	}
	
	
	if(is_null($data)){
		//������������
		require_once("./templates/correction.html");
	}else{	//��Ͽ��λ��������
		$url = "./index.php";
		/*$smarty->assign("url","./index.php");
		$smarty->display("./completion.tpl");
		*/
		
		//�ե�����ID����
		$f_id = $sql->getFormId($user_id);
		if($f_id=="f"){
			echo $sql->errorm;
			exit;
		}

		$url_mess = "https://www.itm-asp.com/member/pictmail/form_create/confirmForm.php?u_id=$user_id&f_id=$f_id";
		$url_mess2 ="http://www.itm-asp.com/member/pictmail/form_create/m-confirmForm.php?u_id=$user_id&f_id=$f_id";
		require_once("./templates/completion.html");
	}


?>