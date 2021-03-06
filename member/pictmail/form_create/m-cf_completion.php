<?php

/**********************************************
		作成したフォームからの入力データを
		DBへ保存するためのファイル
*********************************************/


	//ファイルを読み込み
//	require_once("MySmarty.class.php");
	require_once("SQLData.class.php");
	require_once("MyMail.class.php");
		
	//インスタンス生成
//	$smarty = new MySmarty();
	$sql    = new SQLData();
	$myMail   = new MyMail;

	//GETでユーザーID取得
	$user_id = $_GET['u_id'];
	$f_id = $_GET['f_id'];
	
	//IDの一致確認
	$id_flag = $sql->isValidUser($user_id,$f_id);
	if(($user_id=="")||(!is_numeric($user_id))||(!$id_flag)){
		require_once("./templates/error.html");
		exit;
	}
	
	$submit = $_POST['submit'];
	if($submit!=""){
		$sub_key = key($submit);
		if($sub_key==="back"){
			require_once("./m-confirmForm.php");
		}
	}

	//POSTのデータを取得
	$data = $_POST['data'];

	//フォームから受けたデータを変数に格納
	$name_family 	  = mb_convert_encoding($data['name_family'],"EUC-JP","SJIS");
	$name_first		  = mb_convert_encoding($data['name_first'],"EUC-JP","SJIS");
	$user_mail_add    = mb_convert_encoding($data['user_mail_add'],"EUC-JP","SJIS");
	$paramAmount      = mb_convert_encoding($data['paramAmount'],"EUC-JP","SJIS");
	$del_flag		  = mb_convert_encoding($data['del_flag'],"EUC-JP","SJIS");
	$thk_url          = mb_convert_encoding($data['thk_url'],"EUC-JP","SJIS");;
	$i="1";
	while($i<=$paramAmount){
		$param[$i]  = mb_convert_encoding($data['param'.$i],"EUC-JP","SJIS");
		$p_name[$i] = mb_convert_encoding($data['param_name'.$i],"EUC-JP","SJIS");
		$i++;
	}
	
	//DBクラスへ値を格納
	$sql->setNameFamily($name_family);
	$sql->setNameFirst($name_first);
	$sql->setUserMailAdd($user_mail_add);
	$sql->setParam($param);
	$sql->setPName($p_name);
	$sql->setDeleteFlag($del_flag);
	$sql->setUserID($user_id);
		
	//DBへフォームデータを保存
	$res = $sql->saveUserData();
	if(!$res){
		echo mb_convert_encoding($sql->errorm,"SJIS","EUC-JP");
		exit;
	}
	
	//DBよりサンキューメールの設定データ取得
	$tableName="td_setting_thankmail";
	$sql_data = $sql->getTableData($tableName,$user_id);
	
	//サンキューメール送信の使用確認 使用=1 不使用=0
	$sendmail_flag = $sql_data['sendmail_flag'];
	if($sendmail_flag=="1"){

		//DBから受けたデータをそれぞれ変数に格納
		$transmit_name    = $sql_data['transmit_name'];
		$transmit_mailadd = $sql_data['transmit_mailadd'];
		$return_err       = $sql_data['return_err'];
		$subject          = $sql_data['subject'];
		$text_mess        = $sql_data['text_mess'];
		
		//テキストメッセージのユーザータグ、改行を置換
		$text_mess        = str_replace("%name%",$name_family."  ".$name_first,$text_mess);
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
		
		//メールヘッダー
		$from = mb_encode_mimeheader($transmit_name)."<".$transmit_mailadd.">";
		
		//ＰＣフラグ
		$pc_flag = 1;
		
		//携帯メッセージ、フラグの取得
		$mobail_mess = $sql_data['mobail_mess'];
		
		//携帯メッセージのユーザータグ、改行を置換
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

		//メルアドがdocomoかどうかチェック、携帯メッセージを使うかどうかをチェック
	    if(ereg( "^@docomo.ne.jp",substr($user_mail_add,-13) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}
		//メルアドがvodafoneかどうかチェックして、携帯メッセージを使うかどうかをチェック
		if(ereg( "^@[dqnchtrks]{1}.vodafone.ne.jp",substr($user_mail_add,-17) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}
		//メルアドがezwebかどうかチェックして、携帯メッセージを使うかどうかをチェック
		if(ereg( "^ezweb.ne.jp",substr($user_mail_add,-11) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}
		//メルアドがsoftbankかどうかチェックして、携帯メッセージを使うかどうかをチェック
		if(ereg( "^@[dqnchtrks]{1}.softbank.ne.jp",substr($str,-17) )){
			if($mobail_flag=="2"){
				$text_mess = $mobail_mess;
			}
			$pc_flag = 0;
		}

		//htmlメッセージ送信の使用確認　使用=1 不使用=0;
		$html_flag        = $sql_data['html_flag'];

		if(($pc_flag=="1")&&($html_flag=="1")){
			$html_mess    = $sql_data['html_mess'];
			$html_mess = ereg_replace("\n","",$html_mess);

			//HTMLメール送信
			$myMail->htmlMail($from,$user_mail_add,$subject,$text_mess,$html_mess);
			
			//管理者(itm_nikiにメール)
			$myMail->htmlMail($from,"niki@itm.ne.jp",$subject,$text_mess,$html_mess);
		
		}else{
			//通常のメール配信
			$from = "From:".$from;
			mb_send_mail($user_mail_add,$subject,$text_mess,$from);
			mb_send_mail("niki@itm.ne.jp",$subject,$text_mess,$from);
		}
	}
	
	$url = $thk_url;
	//html出力
	//$smarty->assign("url",$thk_url);
	//$smarty->display("./completion.tpl");
	require_once("./templates/m-cf_completion.html");
	exit;

?>